<?php
// регистрируем компонент
if(!extension::registerPathWay(array('login', 'register', 'recovery', 'logout', 'aprove', 'user'), 'usercontrol')) { exit("Component usercontrol cannot be registered!"); }
page::setNoCache('login');
page::setNoCache('register');
page::setNoCache('recovery');
page::setNoCache('logout');
page::setNoCache('aprove');
page::setNoCache('user');

class com_usercontrol_front
{
	public function load()
	{
		global $page,$template,$rule,$extension;
		$way = $page->getPathway();
		$rule->add('com.usercontrol.login_captcha', $extension->getConfig('login_captcha', 'usercontrol', 'components', 'boolean'));
		$rule->add('com.usercontrol.register_captcha', $extension->getConfig('register_captcha', 'usercontrol', 'components', 'boolean'));
		switch($way[0])
		{
			case "login":
				$this->loginComponent();
				break;
			case "register":
				$this->regComponent();
				break;
			case "recovery":
				$this->recoveryComponent();
				break;
			case "logout":
				$this->doLogOut();
				break;
			case "aprove":
				$this->doRegisterAprove();
				break;
			case "user":
				$this->profileComponent();
				break;
			default:
				break;
		}
	}
	
	private function profileComponent()
	{
		global $page,$system,$template,$page,$user,$extension,$rule;
		$way = $page->getPathway();
		$userid = substr($way[1], 2);
		$content = null;
		
		if(!$extension->getConfig('profile_view', 'usercontrol', 'components', 'boolean') && $user->get('id') < 1)
		{
			$content = $template->tplget('guest_message', 'components/usercontrol/');
		}
		else
		{
			if($way[1] == null)
			{
				$content = $this->showUserList();;
			}
			elseif(substr($way[1], 0, 2) == "id")
			{
				if($system->isInt($userid) && $userid > 0 && $user->exists($userid))
				{
					$this->dynamicRequests($userid);
					$user->get('id') == $userid ? $rule->add('com.usercontrol.self_profile', true) : $rule->add('com.usercontrol.self_profile', false);
					$this->inFriendsWith($userid) ? $rule->add('com.usercontrol.in_friends', true) : $rule->add('com.usercontrol.in_friends', false);
					$this->inFriendRequestWith($userid) ? $rule->add('com.usercontrol.in_friends_request', true) : $rule->add('com.usercontrol.in_friends_request', false);
					switch($way[2])
					{
						case "marks":
							$content = $this->showBookmarks($userid);
							break;
						case "friends":
							$content = $this->showFriends($userid);
							break;
						default:
							$content = $this->showProfileUser($userid);
							break;
					}
				}
			}
		}
		if($content == null)
			$content = $template->compile404();
		$page->setContentPosition('body', $content);
		// можно добавить и обработчик по login / etc данным
	}
	
	// обработка сквозных запросов для профиля: инвайты в друзья, обновление аваров, статуса и прочее
	private function dynamicRequests($userid)
	{
		global $system,$user,$database,$constant;
		if($system->post('requestfriend'))
		{
			// еще не было запроса, вдруг пост-фейкинг 
			if(!$this->inFriendRequestWith($userid))
			{
				$current_friendrequest_list = $user->customget('friend_request', $userid);
				if(strlen($current_friendrequest_list) < 1)
				{
					$current_friendrequest_list .= $user->get('id');
				}
				else
				{
					$current_friendrequest_list .= ",".$user->get('id');
				}
				$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user_custom SET friend_request = ? WHERE id = ?");
				$stmt->bindParam(1, $current_friendrequest_list, PDO::PARAM_STR);
				$stmt->bindParam(2, $userid, PDO::PARAM_INT);
				$stmt->execute();
				$user->customoverload($userid);
			}
		}
	}
	
	private function showUserList()
	{
		global $database,$constant,$template,$user;
		$theme_head = $template->tplget('userlist_head', 'components/usercontrol/');
		$theme_body = $template->tplget('userlist_body', 'components/usercontrol/');
		$compiled_body = null;
		$current_user_id = $user->get('id');
		if($current_user_id == null)
			$current_user_id = 0;
		$stmt = $database->con()->prepare("SELECT a.id, a.nick, b.avatar, b.regdate FROM {$constant->db['prefix']}_user a, {$constant->db['prefix']}_user_custom b WHERE a.id != ? AND a.id = b.id AND a.aprove = 0 ORDER BY a.id DESC");
		$stmt->bindParam(1, $current_user_id, PDO::PARAM_INT);
		$stmt->execute();
		while($result = $stmt->fetch())
		{
			$compiled_body .= $template->assign(array('target_user_id', 'target_user_name', 'target_user_avatar', 'target_reg_date'), array($result['id'], $result['nick'], $result['avatar'], $result['regdate']), $theme_body);
		}
		return $template->assign('userlist', $compiled_body, $theme_head);
	}
	
	private function showFriends($userid)
	{
		global $user,$template,$page,$system,$rule,$database,$constant,$language,$extension;
		//$theme_body = $template->tplget('friendlist_body', 'components/usercontrol/');
		$body_compiled = null;
		$theme_head = $template->tplget('friendlist_head', 'components/usercontrol/');
		$way = $page->getPathway();
		
		switch($way[3])
		{
			case "request":
				if($user->get('id') == $userid)
				{
					if($system->post('acceptfriend'))
					{
						$this->acceptFriend($system->post('target_id'));
					}
					elseif($system->post('cancelfriend'))
					{
						$this->rejectFriend($system->post('target_id'));
					}
					$rule->add('com.usercontrol.profile_friend_request', true);
					$request_list = $user->customget('friend_request');
					if(strlen($request_list) > 0)
					{
						$theme_body = $template->tplget('friendrequest_body', 'components/usercontrol/');
						// загружаем данные о пользователях 1 запросом
						$user->listload($request_list);
						$request_array = explode(",", $request_list);
						foreach($request_array as $requester_id)
						{
							$user_nick = $user->get('nick', $requester_id);
							$user_avatar = $user->customget('avatar', $requester_id);
							$body_compiled .= $template->assign(array('nick', 'avatar', 'target_user_id'), array($user_nick, $user_avatar, $requester_id), $theme_body);
						}
					}
				}
				break;
			default:
				$friend_list = $user->customget('friend_list', $userid);
				if(strlen($friend_list) > 0)
				{
					$friend_array = explode(",", $friend_list);
					$page_index = 0;
					if($system->isInt($way[3]))
					{
						$page_index = $way[3];
					}
					$rows_count = $extension->getConfig('friend_page_count', 'usercontrol', 'components', 'int');
					$page_start = $page_index * $rows_count;
					$theme_body = $template->tplget('friendlist_body', 'components/usercontrol/');
					$friend_current_page = array_slice($friend_array, $page_start, $page_start+$rows_count);
					if(sizeof($friend_current_page) > 0)
					{
						$user->listload(implode(",", $friend_current_page));
						foreach($friend_current_page as $friend_id)
						{
							$user_nick = $user->get('nick', $friend_id);
							$user_avatar = $user->customget('avatar', $friend_id);
							$body_compiled .= $template->assign(array('nick', 'avatar', 'target_user_id'), array($user_nick, $user_avatar, $friend_id), $theme_body);
						}
					}
				}
				break;
		}

		if($body_compiled == null)
		{
			$body_compiled = $language->get('usercontrol_profile_view_null_info');
		}
		
		$container_compiled = $template->assign(array('target_user_id', 'friend_body'), array($userid, $body_compiled), $theme_head);
		
		$compiled_theme = $template->assign(array('user_photo_control', 'user_header', 'user_menu', 'user_main_block'),
				array($this->userProfilePhotoSettings($userid), $this->userProfileHeaders($userid), $this->showUserMenu($userid) , $container_compiled),
				$template->tplget('profile_main', 'components/usercontrol/'));
		return $compiled_theme;
	}
	
	private function acceptFriend($id)
	{
		global $user,$system,$database,$constant;
		$request_array = explode(",", $user->customget('friend_request'));
		$friend_array = explode(",", $user->customget('friend_list'));
		if(in_array($id, $request_array))
		{
			$ownerid = $user->get('id');
			// этот пользователь еще не в списках
			if(!in_array($id, $friend_array))
			{
				// вносим в списки друзей и удаляем из списков запросов
				$new_request_array = $system->valueUnsetInArray($id, $request_array);
				$new_request_list = $system->altimplode(",", $new_request_array);
				$new_friend_array = $system->arrayAdd($id, $friend_array);
				$new_friend_list = $system->altimplode(",", $new_friend_array);
				echo $new_friend_list;
				$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user_custom SET friend_list = ?, friend_request = ? WHERE id = ?");
				$stmt->bindParam(1, $new_friend_list, PDO::PARAM_STR);
				$stmt->bindParam(2, $new_request_list, PDO::PARAM_STR);
				$stmt->bindParam(3, $ownerid, PDO::PARAM_INT);
				$stmt->execute();
				$stmt = null;
				// так же при принятии заносим пользователя в список друзей тому, кто подал запрос
				$requester_friendarray = explode(",", $user->customget('friend_list', $id));
				$requester_friendarray = $system->arrayAdd($ownerid, $requester_friendarray);
				$requester_new_friendlist = $system->altimplode(",", $requester_friendarray);
				$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user_custom SET friend_list = ? WHERE id = ?");
				$stmt->bindParam(1, $requester_new_friendlist, PDO::PARAM_STR);
				$stmt->bindParam(2, $id, PDO::PARAM_INT);
				$stmt->execute();
			}
			// как такое произошло? уже в друзьях и еще запрос прислал, чистим
			else
			{
				$new_request_array = $system->valueUnsetInArray($id, $request_array);
				$new_request_list = $system->altimplode(",", $new_request_array);
				$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user_custom SET friend_request = ? WHERE id = ?");
				$stmt->bindParam(1, $new_request_list, PDO::PARAM_STR);
				$stmt->bindParam(2, $ownerid, PDO::PARAM_INT);
				$stmt->execute();
			}
			$user->customoverload($ownerid);
		}
		
	}
	
	private function rejectFriend($id)
	{
		global $user,$system,$database,$constant;
		$request_array = explode(",", $user->customget('friend_request'));
		if(in_array($id, $request_array))
		{
			$ownerid = $user->get('id');
			$new_request_list = $system->altimplode(",", $system->valueUnsetInArray($id, $request_array));
			$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user_custom SET friend_request = ? WHERE id = ?");
			$stmt->bindParam(1, $new_request_list, PDO::PARAM_STR);
			$stmt->bindParam(2, $ownerid, PDO::PARAM_INT);
			$stmt->execute();
			$user->customoverload($ownerid);
		}
	}
	
	private function showBookmarks($userid)
	{
		global $user,$rule,$page,$template,$database,$constant,$extension;
		$way = $page->getPathway();
		$marks_marker = (int)$way[3];
		$total_marks_row = $this->getMarkTotalRows($userid);
		$marks_config_rows = $extension->getConfig('marks_post_count', 'usercontrol', 'components');
		$marks_index = $marks_marker * $marks_config_rows;
		$main_theme = $template->tplget('profile_main', 'components/usercontrol/');
		$user_compiled_menu = $this->showUserMenu($userid);
		$user_marks_header = $template->tplget('profile_marks_head', 'components/usercontrol/');
		$user_marks_body = $template->tplget('profile_marks_body', 'components/usercontrol/');
		$user_marks_list = null;
		$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_bookmarks WHERE target = ? ORDER BY id DESC LIMIT ?, ?");
		$stmt->bindParam(1, $userid, PDO::PARAM_INT);
		$stmt->bindParam(2, $marks_index, PDO::PARAM_INT);
		$stmt->bindParam(3, $marks_config_rows, PDO::PARAM_INT);
		$stmt->execute();
		while($result = $stmt->fetch())
		{
			$user_marks_list .= $template->assign(array('mark_title', 'mark_link'), 
					array($result['title'], $constant->url.$result['href']), 
					$user_marks_body);
		}
		$user_marks = $template->assign(array('marks_body', 'target_user_id', 'mark_prev', 'mark_next'), array($user_marks_list, $userid, $marks_marker-1, $marks_marker+1), $user_marks_header);
		// позиция коретки > 0 дает понять о наличии предидущих элементов
		if($marks_marker > 0)
		{
			$rule->add('com.usercontrol.have_previous', true);
		}
		else
		{
			$rule->add('com.usercontrol.have_previous', false);
		}
		if($total_marks_row > $marks_index+$marks_config_rows)
		{
			$rule->add('com.usercontrol.have_next', true);
		}
		else
		{
			$rule->add('com.usercontrol.have_next', false);
		}
		$compiled_theme = $template->assign(array('user_photo_control', 'user_header', 'user_menu', 'user_main_block'),
				array($this->userProfilePhotoSettings($userid), $this->userProfileHeaders($userid), $user_compiled_menu , $user_marks),
				$main_theme);
		return $compiled_theme;
	}

	private function showUserMenu($userid)
	{
		global $template,$rule,$page;
		$way = $page->getPathway();
		switch($way[2])
		{
			case "marks":
				$rule->add('com.usercontrol.menu_mark', true);
				break;
			case "wall":
			case "":
				$rule->add('com.usercontrol.menu_wall', true);
				break;
			case "friends":
				$rule->add('com.usercontrol.menu_friends', true);
				break;
			default:
				$rule->add('com.usercontrol.menu_dropdown', true);
				break;
		}
		return $template->assign('target_user_id', $userid, $template->tplget('profile_block_menu', 'components/usercontrol/'));
	}
	
	private function showProfileUser($userid)
	{
		global $page,$user,$rule,$template,$language,$system,$database,$constant;
		if($system->post('wall_post'))
		{
			$caster = $user->get('id');
			$time = time();
			$message = $system->nohtml($system->post('wall_text'));
			if($system->length($message) > 1 && $caster > 0 && $this->inFriendsWith($userid))
			{
				$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user_wall (target, caster, message, time) VALUES (?, ?, ?, ?)");
				$stmt->bindParam(1, $userid, PDO::PARAM_INT);
				$stmt->bindParam(2, $caster, PDO::PARAM_INT);
				$stmt->bindParam(3, $message, PDO::PARAM_STR);
				$stmt->bindParam(4, $time, PDO::PARAM_INT);
				$stmt->execute();
			}
		}
		$way = $page->getPathway();
		$wall_marker = (int)$way[3];
		$profile_theme = $template->tplget('profile_main', 'components/usercontrol/');
		$profile_data_theme = $template->tplget('profile_block_data', 'components/usercontrol/');
		// пользовательские данные
		$regdate = new DateTime($user->customget('regdate', $userid));
		$regdate = $regdate->format('Y.m.d');
		$birthday = $user->customget('birthday', $userid);
		$sex_int = $user->customget('sex', $userid);
		if($sex_int == "1")
		{
			$sex = $language->get('usercontrol_profile_sex_man');
		}
		elseif($sex_int == "2")
		{
			$sex = $language->get('usercontrol_profile_sex_woman');
		}
		else
		{
			$sex = $language->get('usercontrol_profile_sex_unknown');
		}
		$phone = $user->customget('phone', $userid);
		if(strlen($phone) < 4)
		{
			$phone = $language->get('usercontrol_profile_phone_unknown');
		}
		$user_compiled_menu = $this->showUserMenu($userid);
		$profile_compiled_data = $template->assign(array('user_regdate', 'user_birthday', 'user_sex', 'user_phone', 'target_user_id', 'user_wall', 'wall_prev', 'wall_next'), 
				array($regdate, $birthday, $sex, $phone, $userid, $this->loadUserWall($userid, $wall_marker), $wall_marker-1, $wall_marker+1),
				 $profile_data_theme);
		$compiled_theme = $template->assign(array('user_photo_control', 'user_header', 'user_menu', 'user_main_block'), 
				array($this->userProfilePhotoSettings($userid), $this->userProfileHeaders($userid), $user_compiled_menu , $profile_compiled_data), 
				$profile_theme);
		return $compiled_theme;
	}
	
	private function loadUserWall($userid, $marker)
	{
		global $template,$database,$constant,$user,$language,$extension,$rule;
		$theme = $template->tplget('profile_wall', 'components/usercontrol/');
		$output = null;
		$wall_rows = $extension->getConfig('wall_post_count', 'usercontrol', 'components', 'int');
		$marker_index = $marker * $wall_rows;
		$wall_total_rows = $this->getWallTotalRows($userid);
		if($marker > 0)
		{
			$rule->add('com.usercontrol.have_previous', true);
		}
		else
		{
			$rule->add('com.usercontrol.have_previous', false);
		}
		if($wall_total_rows > $marker_index+$wall_rows)
		{
			$rule->add('com.usercontrol.have_next', true);
		}
		else
		{
			$rule->add('com.usercontrol.have_next', false);
		}
		$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_wall WHERE target = ? ORDER by time DESC LIMIT ?, ?");
		$stmt->bindParam(1, $userid, PDO::PARAM_INT);
		$stmt->bindParam(2, $marker_index, PDO::PARAM_INT);
		$stmt->bindParam(3, $wall_rows, PDO::PARAM_INT);
		$stmt->execute();
		while($result = $stmt->fetch())
		{
			$from_name = $user->get('nick', $result['caster']);
			if(strlen($from_name) < 1)
			{
				$from_name = $language->get('usercontrol_profile_name_unknown');
			}
			$message = $result['message'];
			$output .= $template->assign(array('wall_from', 'wall_message', 'wall_from_id', 'wall_message_id'), array($from_name, $message, $result['caster'], $result['id']), $theme);
		}
		return $output;
	}
	
	private function inFriendsWith($userid)
	{
		global $user;
		$friend_list = $user->customget('friend_list', $userid);
		$friend_array = explode(",", $friend_list);
		if(in_array($user->get('id'), $friend_array) || $user->get('id') == $userid)
		{
			return true;
		}
		return false;
	}
	
	private function inFriendRequestWith($userid)
	{
		global $user;
		$friend_request_list = $user->customget('friend_request', $userid);
		$friend_request_array = explode(",", $friend_request_list);
		if(in_array($user->get('id'), $friend_request_array))
		{
			return true;
		}
		return false;
	}
	
	private function getWallTotalRows($userid)
	{
		global $database,$constant;
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user_wall WHERE target = ?");
		$stmt->bindParam(1, $userid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		return $result[0];
	}
	
	private function getMarkTotalRows($userid)
	{
		global $database,$constant;
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user_bookmarks WHERE target = ?");
		$stmt->bindParam(1, $userid, PDO::PARAM_INT);
		$stmt->execute();
		$res = $stmt->fetch();
		return $res[0];
	}
	
	private function userProfilePhotoSettings($userid)
	{
		global $template,$user;
		$parsed = $template->assign(array('user_avatar', 'target_user_id'), 
				array($user->customget('avatar', $userid), $userid), 
				$template->tplget('profile_photo', 'components/usercontrol/'));
		return $parsed;
	}
	
	private function userProfileHeaders($userid)
	{
		global $user,$language,$template;
		$nickname = $user->get('nick', $userid);
		if(strlen($nickname) < 1)
		{
			$nickname = $language->get('usercontrol_profile_name_unknown');
		}
		$status = $user->customget('status', $userid);
		if(strlen($status) < 1)
		{
			$status = $language->get('usercontrol_profile_status_unknown');
		}
		return $template->assign(array('user_name', 'user_status'), array($nickname, $status), $template->tplget('profile_header', 'components/usercontrol/'));
	}
	
	private function loginComponent()
	{
		global $page,$template,$hook,$language,$database,$system,$constant,$user,$extension;
		if($user->get('id') != NULL)
		{
			$page->setContentPosition('body', $template->compile404());
			return;
		}
		$notify = null;
		if($system->post('submit'))
		{
			$loginoremail = $system->post('email');
			if($extension->getConfig('login_captcha', 'usercontrol', 'components', 'boolean') && (strlen($system->post('captcha')) < 1 || strtolower($system->post('captcha')) != $_SESSION['captcha']))
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_captcha_form_error'));
			}
			if(!filter_var($loginoremail, FILTER_VALIDATE_EMAIL) && (strlen($loginoremail) < 3 || !$system->isLatinOrNumeric($loginoremail)))
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_invalid_emailorlogin_error'));
			}
			if(strlen($system->post('password')) < 4 || strlen($system->post('password')) > 32)
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_incorrent_password_error'));
			}
			// все хорошо, ошибок нет, можно идти к SQL запросу
			if($notify == null)
			{
				$md5pwd = md5($system->post('password'));
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user WHERE (email = ? OR login = ?) AND pass = ?");
				$stmt->bindParam(1, $loginoremail, PDO::PARAM_STR);
				$stmt->bindParam(2, $loginoremail, PDO::PARAM_STR);
				$stmt->bindParam(3, $md5pwd, PDO::PARAM_STR, 32);
				$stmt->execute();
				if($stmt->rowCount() == 1)
				{
					$md5token = $system->md5random();
					$nixtime = time();
					$stmt2 = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user SET token = ?, token_start = ? WHERE (email = ? OR login = ?) AND pass = ?");
					$stmt2->bindParam(1, $md5token);
					$stmt2->bindParam(2, $nixtime);
					$stmt2->bindParam(3, $loginoremail);
					$stmt2->bindParam(4, $loginoremail);
					$stmt2->bindParam(5, $md5pwd);
					$stmt2->execute();
					
					setcookie('person', $loginoremail, 0, '/');
					setcookie('token', $md5token, 0, '/');
					$system->redirect();
					exit();
				}
				else
				{
					$notify .= $template->stringNotify('error', $language->get('usercontrol_incorrent_password_query'));					
				}
			}
		}
		$theme = $template->tplget('login', 'components/usercontrol/');
		$captcha = $hook->get('captcha');
		$theme = $template->assign(array('captcha', 'notify'), array($captcha, $notify), $theme);
		$page->setContentPosition('body', $theme);
	}
	
	private function doRegisterAprove()
	{
		global $page,$database,$constant,$template;
		$pathway = $page->getPathway();
		$hash = $pathway[1];
		$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user SET aprove = 0 WHERE aprove = ?");
		$stmt->bindParam(1, $hash, PDO::PARAM_STR, 32);
		$stmt->execute();
		if($stmt->rowCount() == 0)
		{
			$page->setContentPosition('body', $template->compile404());
		}
		else
		{
			$page->setContentPosition('body', $template->tplget('aprove', 'components/usercontrol/'));
		}		
	}
	
	private function regComponent()
	{
		global $user,$template,$hook,$page,$language,$database,$constant,$system,$mail,$extension;
		if($user->get('id') != NULL)
		{
			$page->setContentPosition('body', $template->compile404());
			return;
		}
		if($system->post('submit'))
		{
			$nickname = $system->nohtml($system->post('nick'));
			$email = $system->post('email');
			$login = $system->post('login');
			$pass = $system->post('password');
			$md5pwd = md5($pass);
			if($extension->getConfig('register_captcha', 'usercontrol', 'components', 'boolean') && (strlen($system->post('captcha')) < 1 || strtolower($system->post('captcha')) != $_SESSION['captcha']))
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_captcha_form_error'));
			}
			if(!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_invalid_email_error'));
			}
			if(strlen($pass) < 4 || strlen($pass) > 32)
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_incorrent_password_error'));
			}
			if($this->mailExists($email))
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_mail_exist'));
			}
			if($this->loginIsIncorrent($login))
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_login_exist'));
			}
			if(strlen($nickname) < 3 || strlen($nickname) > 64)
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_nick_incorrent'));
			}
			if($notify == null)
			{
				$aprove_reg_from_email = $extension->getConfig('register_aprove', 'usercontrol', 'components', 'boolean');
				$validate = 0;
				if($aprove_reg_from_email)
				{
					$validate = $system->randomWithUnique($email);
				}
				$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user (`login`, `email`, `nick`, `pass`, `aprove`) VALUES (?,?,?,?,?)");
				$stmt->bindParam(1, $login, PDO::PARAM_STR, 64);
				$stmt->bindParam(2, $email, PDO::PARAM_STR);
				$stmt->bindParam(3, $nickname, PDO::PARAM_STR);
				$stmt->bindParam(4, $md5pwd, PDO::PARAM_STR, 32);
				$stmt->bindParam(5, $validate, PDO::PARAM_STR, 32);
				$stmt->execute();
				if($aprove_reg_from_email)
				{
					$notify .= $template->stringNotify('success', $language->get('usercontrol_register_success_aprove'));
					$mail_body = $template->tplget('mail');
					$link = '<a href="'.$constant->url.'/aprove/'.$validate.'">'.$language->get('usercontrol_reg_mail_aprove_link_text').' - '.$constant->url.'</a>';
					$mail_body = $template->assign(array('title', 'description', 'text', 'footer'), array($language->get('usercontrol_reg_mail_title'), $language->get('usercontrol_reg_mail_description'), $link, $language->get('usercontrol_reg_mail_footer')), $mail_body);
					$mail->send($email, $language->get('usercontrol_reg_mail_title'), $mail_body, $nickname);
				}
				else
				{
					$notify = $notify .= $template->stringNotify('success', $language->get('usercontrol_register_success_noaprove'));
				}
			}
		}
		$theme = $template->tplget('register', 'components/usercontrol/');
		$captcha = $hook->get('captcha');
		$theme = $template->assign(array('captcha', 'notify'), array($captcha, $notify), $theme);
		$page->setContentPosition('body', $theme);
		
	}
	
	private function recoveryComponent()
	{
		global $template,$hook,$page,$system,$database,$constant,$language;
		$pathway = $page->getPathway();
		if($pathway[1] != null && $pathway[2] != null)
		{
			// todo
		}
		else
		{
			$notify = null;
			if($system->post('submit'))
			{
				$email = $system->post('email');
				if(strlen($system->post('captcha')) < 1 || strtolower($system->post('captcha')) != $_SESSION['captcha'])
				{
					$notify .= $template->stringNotify('error', $language->get('usercontrol_captcha_form_error'));
				}
				if(!filter_var($email, FILTER_VALIDATE_EMAIL))
				{
					$notify .= $template->stringNotify('error', $language->get('usercontrol_invalid_email_error'));
				}
				if($notify == null)
				{
					$new_password = $system->randomString(rand(8,12));
					$hash = $system->md5random();
					$stmt = $database->con()->prepare("SELECT id FROM {$constant->db['prefix']}_user WHERE email = ?");
					$stmt->bindParam(1, $email);
					$stmt->execute();
					if($stmt->rowCount() != 1)
					{
						$notify .= $template->stringNotify('error', $language->get('usercontrol_recovery_mail_unknown'));
					}
					else
					{
						// Учетка есть, делаем запись в бд для восстановления
						$res_stmt = $stmt->fetch();
						$userid = $res_stmt['id'];
						$stmt2 = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user_recovery (`password`, `hash`, `userid`) VALUES (?, ?, ?)");
						$stmt2->bindParam(1, $new_password);
						$stmt2->bindParam(2, $hash);
						$stmt2->bindParam(3, $userid);
						$stmt2->execute();
						$request_id = $database->con()->lastInsertId();
						$recovery_link = $template->assign('recovery_url', $constant->url.'/recovery/'.$request_id.'/'.$hash, $language->get("usercontrol_mail_link_text"));
						
						$mail_body = $template->tplget('mail');
						$mail_body = $template->assign(array('title', 'description', 'text', 'footer'), array($language->get('usercontrol_reg_mail_title'), $language->get('usercontrol_reg_mail_description'), $link, $language->get('usercontrol_reg_mail_footer')), $mail_body);
						$mail->send($email, $language->get('usercontrol_reg_mail_title'), $mail_body, $nickname);
					}
				}
			}
			$theme = $template->tplget('recovery', 'components/usercontrol/');
			$captcha = $hook->get('captcha');
			$theme = $template->assign(array('captcha', 'notify'), array($captcha, $notify), $theme);
			$page->setContentPosition('body', $theme);
		}
	}
	
	private function doLogOut()
	{
		global $system,$user,$page;
		if($user->get('id') == NULL)
		{
			$page->setContentPosition('body', $template->compile404());
			return;
		}
		setcookie('person', '', 0, '/');
		setcookie('token', '', 0, '/');
		$system->redirect();
	}
	
	private function mailExists($mail)
	{
		global $database,$constant;
		$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user WHERE email = ?");
		$stmt->bindParam(1, $mail, PDO::PARAM_STR);
		$stmt->execute();
		if($stmt->rowCount() == 0)
		{
			return false;
		}
	return true;
	}
	
	private function loginIsIncorrent($login)
	{
		global $database,$constant,$system;
		if(strlen($login) < 3 || strlen($login) > 64)
		{
			return true;
		}
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user WHERE login = ?");
		$stmt->bindParam(1, $login, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		return $result[0] == 0 ? false : true;
	}

}

?>