<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class api
{
    public function load()
    {
        global $engine;
        $apiresult = null;
        switch ($engine->system->get('action')) {
            case "readwall":
                $apiresult = $this->loadUserWall();
                break;
            case "postwall":
                $apiresult = $this->doPostWall();
                break;
            case "adminfiles":
                return $engine->file->elfinderForAdmin();
                break;
            case "ckeditorload":
                return $engine->file->ckeditorLoad();
                break;
            case "ckeditorbrowse":
                return $this->ckeditorBrowser();
                break;
            case "commentupload":
                return $engine->file->commentUserUpload();
                break;
            case "redirect":
                $apiresult = $this->userLeaveRedirect();
                break;
            case "encodedredirect":
                $apiresult = $this->userEncodedLeaveRedirect();
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
            case "commenteditform":
                $apiresult = $this->editComment();
                break;
            case "commenteditpost":
                return $this->editPostComment();
                break;
            case "commentdelete":
                return $this->deleteComment();
                break;
            case "addbookmark":
                return $this->addBookMark();
                break;
            case "apicallback":
                return $this->apiCallBack();
                break;
            case "lang":
                return $this->changeLanguage();
                break;
            default:
                return $engine->system->redirect();
                break;
        }
        $apiresult = $engine->template->ruleCheck($apiresult);
        return $engine->language->set($apiresult);
    }

    private function ckeditorBrowser()
    {
        global $engine;
        if($engine->user->get('access_to_admin') < 1)
            return;
        if(file_exists($engine->constant->root . "/resource/ckeditor/browser.php")) {
            require_once($engine->constant->root . "/resource/ckeditor/browser.php");
        }
    }

    private function apiCallBack()
    {
        global $engine;
        $name = $engine->system->get('object');
        $file = $engine->constant->root . "/extensions/apicallback/" . $name . "/front.php";
        if(file_exists($file))
        {
            require_once($file);
            $class_name = "api_{$name}_front";
            if(class_exists($class_name)) {
                $init = new $class_name;
                if(method_exists($init, 'load')) {
                    return $init->load();
                }
            }
        }
        return;
    }

    private function changeLanguage()
    {
        global $engine;
        $lang = $engine->system->get('lang');
        if($lang != null && in_array($lang, $engine->language->getAvailable())) {
            setcookie('ffcms_lang', $lang);
        }
        $engine->system->redirect();
    }

    private function addBookMark()
    {
        global $engine;
        if($engine->user->get('id') < 1) {
            return;
        }
        $title = $engine->system->nohtml($engine->system->post('title'));
        $url = $engine->system->nohtml($engine->system->post('url'));
        $userid = $engine->user->get('id');
        if($engine->system->prefixEquals($url, $engine->constant->url)) {
            $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_user_bookmarks WHERE target = ? AND href = ?");
            $stmt->bindParam(1, $userid, PDO::PARAM_INT);
            $stmt->bindParam(2, $url, PDO::PARAM_STR);
            $stmt->execute();
            $res = $stmt->fetch();
            if($res[0] == 0) {
                $stmt = null;
                $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user_bookmarks (`target`, `title`, `href`) VALUES (?, ?, ?)");
                $stmt->bindParam(1, $userid, PDO::PARAM_INT);
                $stmt->bindParam(2, $title, PDO::PARAM_STR);
                $stmt->bindParam(3, $url, PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }

    private function showRequestJs()
    {
        global $engine;
        header('Content-Type: text/javascript');
        $dir = $engine->system->get('dir');
        $file = $engine->system->get('name');
        if (file_exists($engine->constant->root . $engine->constant->ds . $engine->constant->tpl_dir . $engine->constant->ds . $engine->constant->tpl_name . $engine->constant->ds . $dir . $engine->constant->ds . $file . ".tpl")) {
            return $engine->template->get($file, $dir . $engine->constant->ds);
        }
    }

    private function deleteComment()
    {
        global $engine;
        if($engine->database->isDown())
            return;
        if ($engine->user->get('id') > 0 && $engine->user->get('mod_comment_delete') > 0) {
            $comment_id = (int)$engine->system->get('id');
            $stmt = $engine->database->con()->prepare("DELETE FROM {$engine->constant->db['prefix']}_mod_comments WHERE id = ?");
            $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            $stmt = null;
        }
        return;
    }

    private function editPostComment()
    {
        global $engine;
        if($engine->database->isDown())
            return;
        $comment_id = (int)$engine->system->post('comment_id');
        if ($engine->user->get('id') > 0 && ($engine->user->get('mod_comment_edit') > 0 || $this->commentEditCondition($comment_id))) {
            $comment_text = $engine->system->nohtml($engine->system->post('comment_text'));
            if ($comment_id > 0 && strlen($comment_text) > 0) {
                $stmt = $engine->database->con()->prepare("UPDATE {$engine->constant->db['prefix']}_mod_comments set comment = ? where id = ?");
                $stmt->bindParam(1, $comment_text, PDO::PARAM_STR);
                $stmt->bindParam(2, $comment_id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
            }
        }
        return;
    }

    private function commentEditCondition($id)
    {
        global $engine;
        if($engine->database->isDown())
            return;
        if ($id > 0) {
            $stmt = $engine->database->con()->prepare("SELECT author,time FROM {$engine->constant->db['prefix']}_mod_comments WHERE id = ?");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($result = $stmt->fetch()) {
                $editconfig = $engine->extension->getConfig('edit_time', 'comments', 'modules', 'int');
                if ($result['author'] == $engine->user->get('id') && (time() - $result['time']) <= $editconfig) {
                    return true;
                }
            }
        }
        return false;
    }

    private function editComment()
    {
        global $engine;
        if($engine->database->isDown())
            return;
        $comment_id = (int)$engine->system->get('id');
        $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_mod_comments WHERE id = ?");
        $stmt->bindParam(1, $comment_id, PDO::PARAM_INT);
        $stmt->execute();
        $content = null;
        if ($result = $stmt->fetch()) {
            $content = $engine->template->assign(array('comment_id', 'comment_text'), array($comment_id, $engine->system->nohtml($result['comment'])), $engine->template->get('comment_api_edit', 'modules/mod_comments/'));
        } else {
            $content = $engine->template->stringNotify('error', $engine->language->get('comment_api_edit_nocomment'));
        }
        $stmt = null;
        return $content;
    }

    private function postComment()
    {
        global $engine;
        if($engine->database->isDown())
            return;
        $text = $engine->system->nohtml($engine->system->post('comment_message'));
        $object = $engine->system->post('object');
        $id = $engine->system->post('id');
        $hash = $engine->system->post('hash');
        if ($text != null && $object != null && $id != null && $engine->system->isInt($id) && $hash != null && strlen($hash) == 32) {
            $notify = null;
            if ($engine->user->get('id') > 0 && $engine->user->get('content_post') > 0 && $engine->user->get('mod_comment_add') > 0) {
                $time = time();
                $userid = $engine->user->get('id');
                // узнаем время последнего комментария
                $stmt = $engine->database->con()->prepare("SELECT `time` FROM {$engine->constant->db['prefix']}_mod_comments WHERE author = ? ORDER BY `time` DESC LIMIT 1");
                $stmt->bindParam(1, $userid, PDO::PARAM_INT);
                $stmt->execute();
                if ($result = $stmt->fetch()) {
                    $lastposttime = $result['time'];
                    if (($time - $lastposttime) < $engine->extension->getConfig('time_delay', 'comments', 'modules', 'int')) {
                        $notify .= $engine->template->stringNotify('error', $engine->language->get('comments_api_delay_exception'));
                    }
                }
                if ($engine->system->length($text) < $engine->extension->getConfig('min_length', 'comments', 'modules', 'int') || $engine->system->length($text) > $engine->extension->getConfig('max_length', 'comments', 'modules', 'int')) {
                    $notify .= $engine->template->stringNotify('error', $engine->language->get('comments_api_incorrent_length'));
                }
                if ($notify == null) {
                    $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_mod_comments (target_hash, object_name, object_id, comment, author, time)
                    VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bindParam(1, $hash, PDO::PARAM_STR, 32);
                    $stmt->bindParam(2, $object, PDO::PARAM_STR);
                    $stmt->bindParam(3, $id, PDO::PARAM_STR);
                    $stmt->bindParam(4, $text, PDO::PARAM_STR);
                    $stmt->bindParam(5, $userid, PDO::PARAM_INT);
                    $stmt->bindParam(6, $time, PDO::PARAM_INT);
                    $stmt->execute();
                    $notify .= $engine->template->stringNotify('success', $engine->language->get('comments_api_add_success'));
                }
            } else {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('comments_api_add_fail'));
            }
            return $this->viewComment($notify);
        }
        return;
    }

    public function viewComment($notify = null)
    {
        global $engine;
        if($engine->database->isDown())
            return;
        $object = $engine->system->post('object');
        $id = $engine->system->post('id');
        $hash = $engine->system->post('hash');
        $position = $engine->system->post('comment_position');
        if ($object != null && $id != null && $engine->system->isInt($id) && $hash != null && strlen($hash) == 32 && $engine->system->isInt($position)) {
            $userid = $engine->user->get('id');
            $config_on_page = $engine->extension->getConfig('comments_count', 'comments', 'modules', 'int');
            $end_point = $position == 0 ? $config_on_page : $position * $config_on_page + $config_on_page;
            $theme_list = $engine->template->get('comment_list', 'modules/mod_comments/');
            $content = null;
            $content .= $notify;
            $stmt = $engine->database->con()->prepare("SELECT COUNT(*) FROM {$engine->constant->db['prefix']}_mod_comments WHERE target_hash = ? AND object_name = ? AND object_id = ?");
            $stmt->bindParam(1, $hash, PDO::PARAM_STR, 32);
            $stmt->bindParam(2, $object, PDO::PARAM_STR);
            $stmt->bindParam(3, $id, PDO::PARAM_STR);
            $stmt->execute();
            $rowRes = $stmt->fetch();
            $commentCount = $rowRes[0];
            $stmt = null;
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_mod_comments WHERE target_hash = ? AND object_name = ? AND object_id = ? ORDER BY id DESC LIMIT 0,?");
            $stmt->bindParam(1, $hash, PDO::PARAM_STR, 32);
            $stmt->bindParam(2, $object, PDO::PARAM_STR);
            $stmt->bindParam(3, $id, PDO::PARAM_INT);
            $stmt->bindParam(4, $end_point, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = null;
            $engine->user->listload($engine->system->extractFromMultyArray('author', $result));
            foreach ($result as $item) {
                $edit_link = null;
                $delete_link = null;
                $poster_id = $item['author'];
                $editconfig = $engine->extension->getConfig('edit_time', 'comments', 'modules', 'int');
                if ($userid > 0) {
                    if (($poster_id == $userid && (time() - $item['time']) <= $editconfig) || $engine->user->get('mod_comment_edit') > 0) {
                        $edit_link = $engine->template->assign('comment_id', $item['id'], $engine->template->get('comment_link_edit', 'modules/mod_comments/'));
                    }
                    if ($engine->user->get('mod_comment_delete') > 0) {
                        $delete_link = $engine->template->assign('comment_id', $item['id'], $engine->template->get('comment_link_delete', 'modules/mod_comments/'));
                    }
                }
                $content .= $engine->template->assign(array('poster_id', 'poster_nick', 'poster_avatar', 'comment_text', 'comment_date', 'comment_id', 'comment_link_edit', 'comment_link_delete'),
                    array($poster_id, $engine->user->get('nick', $poster_id), $engine->user->buildAvatar('small', $poster_id), $engine->hook->get('bbtohtml')->bbcode2html($item['comment']), $engine->system->toDate($item['time'], 'h'), $item['id'], $edit_link, $delete_link),
                    $theme_list);
            }
            if ($end_point > $commentCount) {
                $content .= '<script>$(\'#loader_comment\').remove();</script>';
            }
            return $content;
        }
    }

    private function userLeaveRedirect()
    {
        global $engine;
        return $engine->template->assign('target_url', $engine->system->get('url'), $engine->template->get('redirect'));
    }

    private function userEncodedLeaveRedirect()
    {
        global $engine;
        $url = base64_decode($engine->system->get('url'));
        return $engine->template->assign('target_url', $url, $engine->template->get('redirect'));
    }

    public function doPostWall()
    {
        global $engine;
        if($engine->database->isDown())
            return;
        $root_post_id = $engine->system->get('id');
        $writer_id = $engine->user->get('id');
        $message = $engine->system->nohtml($engine->system->post('message'));
        $time = time();
        $limit = false;
        if ($engine->system->isInt($root_post_id) && strlen($engine->system->post('message')) > 0 && $writer_id > 0) {
            $time_between_posts = $engine->extension->getConfig('wall_post_delay', 'usercontrol', 'components', 'int');
            $stmt = $engine->database->con()->prepare("SELECT time FROM {$engine->constant->db['prefix']}_user_wall_answer WHERE poster = ? ORDER BY id DESC LIMIT 1");
            $stmt->bindParam(1, $writer_id, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetch();
            $last_post_time = $res['time'];
            $stmt = null;
            $current_time = time();
            if (($current_time - $last_post_time) >= $time_between_posts) {
                $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_user_wall_answer (wall_post_id, poster, message, time) VALUES(?, ?, ?, ?)");
                $stmt->bindParam(1, $root_post_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $writer_id, PDO::PARAM_INT);
                $stmt->bindParam(3, $message, PDO::PARAM_STR);
                $stmt->bindParam(4, $time, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $limit = true;
            }
        }
        return $this->loadUserWall($limit);
    }

    public function loadUserWall($limit = false)
    {
        global $engine;
        if($engine->database->isDown())
            return;
        $root_post_id = $engine->system->get('id');
        if ($engine->system->isInt($root_post_id)) {
            $theme = $engine->template->get('api_wallanswer', 'components/usercontrol/');
            $compiled = null;
            if ($limit) {
                $compiled .= $engine->template->stringNotify('error', $engine->language->get('usercontrol_profile_wall_answer_spamdetect'));
            }
            $stmt = $engine->database->con()->prepare("SELECT * FROM {$engine->constant->db['prefix']}_user_wall_answer WHERE wall_post_id = ? ORDER BY id DESC");
            $stmt->bindParam(1, $root_post_id, PDO::PARAM_INT);
            $stmt->execute();
            while ($result = $stmt->fetch()) {
                $from_id = $result['poster'];
                $compiled .= $engine->template->assign(array('wall_from_id', 'wall_from', 'user_avatar', 'wall_message'),
                    array($from_id, $engine->user->get('nick', $from_id), $engine->user->buildAvatar('small', $from_id), $result['message']),
                    $theme);
            }
            if ($compiled == null) {
                $compiled = $engine->language->get('usercontrol_profile_wall_noanswer');
            }
            return $compiled;
        }
    }
}

?>