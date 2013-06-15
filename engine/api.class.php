<?php
class api
{
	public function load()
	{
		global $system,$file,$language,$template;
        $apiresult = null;
		switch($system->get('action'))
		{
			case "readwall":
				$apiresult = $this->loadUserWall();
				break;
			case "postwall":
                $apiresult = $this->doPostWall();
				break;
			case "elfinder":
				$file->elfinder();
				break;
			case "redirect":
                $apiresult = $this->userLeaveRedirect();
				break;
			case "js":
                $apiresult = $this->showRequestJs();
				break;
            case "postcomment":
                $apiresult = $this->postComment();
                break;
            case "viewcomment":
                $apiresult = $this->viewComment();
                break;
			default:
				break;
		}
        $apiresult = $template->ruleCheck($apiresult);
        return $language->set($apiresult);
	}
	
	private function showRequestJs()
	{
		global $system,$constant,$template;
		header('Content-Type: text/javascript');
		$dir = $system->get('dir');
		$file = $system->get('name');
		if(file_exists($constant->root.$constant->ds.$constant->tpl_dir.$constant->ds.$constant->tpl_name.$constant->ds.$dir.$constant->ds.$file.".tpl"))
		{
			return $template->tplget($file, $dir.$constant->ds);
		}
	}

    private function postComment()
    {
        global $system,$constant,$database,$user;
        $text = $system->nohtml($system->post('comment_message'));
        $object = $system->post('object');
        $id = $system->post('id');
        $hash = $system->post('hash');
        if($text != null && $object != null && $id != null && $system->isInt($id) && $hash != null && strlen($hash) == 32)
        {
            if($user->get('id') > 0 && $user->get('content_post') > 0)
            {
                $time = time();
                $userid = $user->get('id');
                $stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_mod_comments (target_hash, object_name, object_id, comment, author, time)
                VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bindParam(1, $hash, PDO::PARAM_STR, 32);
                $stmt->bindParam(2, $object, PDO::PARAM_STR);
                $stmt->bindParam(3, $id, PDO::PARAM_STR);
                $stmt->bindParam(4, $text, PDO::PARAM_STR);
                $stmt->bindParam(5, $userid, PDO::PARAM_INT);
                $stmt->bindParam(6, $time, PDO::PARAM_INT);
                $stmt->execute();
            }
            return $this->viewComment();
        }
        return;
    }

    public function viewComment()
    {
        global $system,$database,$constant,$user,$template,$extension,$hook;
        $object = $system->post('object');
        $id = $system->post('id');
        $hash = $system->post('hash');
        $position = $system->post('comment_position');
        if($object != null && $id != null && $system->isInt($id) && $hash != null && strlen($hash) == 32 && $system->isInt($position))
        {
            $config_on_page = $extension->getConfig('comments_count', 'comments', 'modules', 'int');
            $end_point = $position == 0 ? $config_on_page : $position * $config_on_page + $config_on_page;
            $theme_list = $template->tplget('comment_list', 'modules/mod_comments/');
            $content = null;
            $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_mod_comments WHERE target_hash = ? AND object_name = ? AND object_id = ?");
            $stmt->bindParam(1, $hash, PDO::PARAM_STR, 32);
            $stmt->bindParam(2, $object, PDO::PARAM_STR);
            $stmt->bindParam(3, $id, PDO::PARAM_STR);
            $stmt->execute();
            $rowRes = $stmt->fetch();
            $commentCount = $rowRes[0];
            $stmt = null;
            $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_mod_comments WHERE target_hash = ? AND object_name = ? AND object_id = ? ORDER BY id DESC LIMIT 0,?");
            $stmt->bindParam(1, $hash, PDO::PARAM_STR, 32);
            $stmt->bindParam(2, $object, PDO::PARAM_STR);
            $stmt->bindParam(3, $id, PDO::PARAM_INT);
            $stmt->bindParam(4, $end_point, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;
            $user->listload($system->extractFromMultyArray('author', $result));
            foreach($result as $item)
            {
                $poster_id = $item['author'];
                $content .= $template->assign(array('poster_id', 'poster_nick', 'poster_avatar', 'comment_text', 'comment_date'),
                    array($poster_id, $user->get('nick', $poster_id), $user->buildAvatar('small', $poster_id), $hook->get('bbtohtml')->bbcode2html($item['comment']), $system->toDate($item['time'], 'h')),
                    $theme_list);
            }
            if($end_point > $commentCount)
            {
                $content .= '<script>$(\'#loader_comment\').remove();</script>';
            }
            return $content;
        }
        return;
    }
	
	private function userLeaveRedirect()
	{
		global $system,$template;
		return $template->assign('target_url', $system->get('url'), $template->tplget('redirect'));
	}
	
	public function doPostWall()
	{
		global $system,$user,$database,$constant,$extension;
		$root_post_id = $system->get('id');
		$writer_id = $user->get('id');
		$message = $system->nohtml($system->post('message'));
		$time = time();
		$limit = false;
		if($system->isInt($root_post_id) && strlen($system->post('message')) > 0 && $writer_id > 0)
		{
			$time_between_posts = $extension->getConfig('wall_post_delay', 'usercontrol', 'components', 'int');
			$stmt = $database->con()->prepare("SELECT time FROM {$constant->db['prefix']}_user_wall_answer WHERE poster = ? ORDER BY id DESC LIMIT 1");
			$stmt->bindParam(1, $writer_id, PDO::PARAM_INT);
			$stmt->execute();
			$res = $stmt->fetch();
			$last_post_time = $res['time'];
			$stmt = null;
			$current_time = time();
			if(($current_time - $last_post_time) >= $time_between_posts)
			{
				$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user_wall_answer (wall_post_id, poster, message, time) VALUES(?, ?, ?, ?)");
				$stmt->bindParam(1, $root_post_id, PDO::PARAM_INT);
				$stmt->bindParam(2, $writer_id, PDO::PARAM_INT);
				$stmt->bindParam(3, $message, PDO::PARAM_STR);
				$stmt->bindParam(4, $time, PDO::PARAM_INT);
				$stmt->execute();
			}
			else
			{
				$limit = true;
			}
		}
		return $this->loadUserWall($limit);
	}
	
	public function loadUserWall($limit = false)
	{
		global $system,$database,$constant,$user,$language,$extension,$template;
		$root_post_id = $system->get('id');
		if($system->isInt($root_post_id))
		{
			$theme = $template->tplget('api_wallanswer', 'components/usercontrol/');
			$compiled = null;
			if($limit)
			{
				$compiled .= $template->stringNotify('error', $language->get('usercontrol_profile_wall_answer_spamdetect'));
			}
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_wall_answer WHERE wall_post_id = ? ORDER BY id DESC");
			$stmt->bindParam(1, $root_post_id, PDO::PARAM_INT);
			$stmt->execute();
			while($result = $stmt->fetch())
			{
				$from_id = $result['poster'];
				$compiled .= $template->assign(array('wall_from_id', 'wall_from', 'user_avatar', 'wall_message'), 
						array($from_id, $user->get('nick', $from_id), $user->buildAvatar('small', $from_id), $result['message']), 
						$theme);
			}
			if($compiled == null)
			{
				$compiled = $language->get('usercontrol_profile_wall_noanswer');
			}
			return $compiled;
		}
	}
}
?>