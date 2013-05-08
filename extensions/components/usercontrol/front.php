<?php
// регистрируем компонент
if(!extension::registerPathWay(array('login', 'register', 'recovery', 'logout', 'aprove', 'user', 'message', 'settings'), 'usercontrol')) {
	exit("Component usercontrol cannot be registered!");
}
page::setNoCache('login');
page::setNoCache('register');
page::setNoCache('recovery');
page::setNoCache('logout');
page::setNoCache('aprove');
page::setNoCache('user');
page::setNoCache('message');
page::setNoCache('settings');

class com_usercontrol_front
{
	public $hook_item_menu;
	public $hook_item_url;
	public $hook_item_settings;
	
	public function load()
	{
		global $page,$template,$rule,$extension,$hook;
		$way = $page->getPathway();
		$hook->before();
		$rule->getInstance()->add('com.usercontrol.login_captcha', $extension->getConfig('login_captcha', 'usercontrol', 'components', 'boolean'));
		$rule->getInstance()->add('com.usercontrol.register_captcha', $extension->getConfig('register_captcha', 'usercontrol', 'components', 'boolean'));
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
			case "message":
				$this->userPersonalMessage();
				break;
			case "settings":
				$this->userPersonalSettings();
				break;
			default:
				break;
		}
	}
	
	private function userPersonalSettings()
	{
		global $page,$template,$user,$rule,$system,$file,$language,$database,$constant;
		$userid = $user->get('id');
		if($userid < 1)
		{
			$page->setContentPosition('body', $template->compile404());
			return;
		}
		$rule->getInstance()->add('com.usercontrol.self_profile', true);
		$rule->getInstance()->add('com.usercontrol.in_friends', true);
		$rule->getInstance()->add('com.usercontrol.in_friends_request', false);
		$way = $page->getPathway();
		$compiled_body = null;
		if($way[1] == "avatar")
		{
			$notify = null;
			if($system->post('loadavatar'))
			{
				$image_upload = $_FILES['avatarupload'];
				if($image_upload['size'] > 0)
				{
					$upload_result = $file->useravatarupload($image_upload);
					if($upload_result)
					{
						$notify = $template->stringNotify('success', $language->get('usercontrol_profile_photochange_success'));
					}
				}
				if($notify == null)
				{
					$notify = $template->stringNotify('error', $language->get('usercontrol_profile_photochange_fail'));
				}
			}
			$photo_theme = $template->tplget('profile_settings_photo', 'components/usercontrol/');
			$compiled_body = $template->assign('notify_message', $notify, $photo_theme);
		}
		elseif($way[1] == "status")
		{
			if($system->post('updatestatus'))
			{
				$new_status = $system->nohtml($system->post('newstatus'));
				$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user_custom SET status = ? WHERE id = ?");
				$stmt->bindParam(1, $new_status, PDO::PARAM_STR);
				$stmt->bindParam(2, $userid, PDO::PARAM_INT);
				$stmt->execute();
				$user->customoverload($userid);
			}
			$theme_status = $template->tplget('profile_settings_status', 'components/usercontrol/');
			$compiled_body = $template->assign('user_status', $user->customget('status'), $theme_status);
		}
		else
		{
			$notify = null;
			if($system->post('saveprofile'))
			{
				$birthday_array = $system->post('bitrhday');
				// Y-m-d
				$birthday_string = "0000-00-00";
				$nick = $system->nohtml($system->post('nickname'));
				$phone = $system->post('phone');
				$sex = $system->post('sex');
				$webpage = $system->post('website');
				// old, new, repeat new
				$password_array = array($system->post('oldpwd'), $system->post('newpwd'), $system->post('renewpwd'));
				$password = $user->get('pass');
				// анализируем то, что запостил пользователь на корректность данных
				if($birthday_array['year'] >= 1920 && $birthday_array['year'] <= date('Y') && checkdate($birthday_array['month'], $birthday_array['day'], $birthday_array['year']))
				{
					$birthday_string = $birthday_array['year']."-".$birthday_array['month']."-".$birthday_array['day'];
				}
				if(strlen($nick) < 1)
				{
					
					$nick = $user->get('nick');
				}
				if(!$system->validPhone($phone))
				{
					$phone = $user->customget('phone');
				}
				if(!$system->isInt($sex) || $sex < 0 || $sex > 2)
				{
					$sex = $user->customget('sex');
				}
				if(!filter_var($webpage, FILTER_VALIDATE_URL))
				{
					$webpage = $user->customget('webpage');
				}
				// новый пароль был назначен, новые пароли совпали а так же старый пароль введен верно
				if($system->validPasswordLength($password_array) && $system->doublemd5($password_array[0]) == $password && $password_array[1] == $password_array[2] && $password_array[0] != $password_array[1])
				{
					$password = $system->doublemd5($password_array[1]);
					$notify .= $template->stringNotify('error', $language->get('usercontrol_profile_settings_notify_passchange'));
				}
				$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user a INNER JOIN {$constant->db['prefix']}_user_custom b USING(id) SET a.nick = ?, a.pass = ?, b.birthday = ?, b.sex = ?, b.phone = ?, b.webpage = ? WHERE a.id = ?");
				$stmt->bindParam(1, $nick, PDO::PARAM_STR);
				$stmt->bindParam(2, $password, PDO::PARAM_STR, 32);
				$stmt->bindParam(3, $birthday_string, PDO::PARAM_STR);
				$stmt->bindParam(4, $sex, PDO::PARAM_INT);
				$stmt->bindParam(5, $phone, PDO::PARAM_STR);
				$stmt->bindParam(6, $webpage, PDO::PARAM_STR);
				$stmt->bindParam(7, $userid, PDO::PARAM_INT);
				$stmt->execute();
				$user->fulluseroverload($userid);
				$notify .= $template->stringNotify('success', $language->get('usercontrol_profile_settings_notify_updated'));
			}
			$theme_option_inactive = $template->tplget('form_select_option_inactive', 'components/usercontrol/');
			$theme_option_active = $template->tplget('form_select_option_active', 'components/usercontrol/');
			list($birth_year, $birth_month, $birth_day) = explode("-", $user->customget('birthday'));
			$day_range = $system->generateIntRangeArray(1, 31);
			$month_range = $system->generateIntRangeArray(1, 12);
			$year_range = $system->generateIntRangeArray(1920, date('Y'));
			$day_option_list = null;
			$month_option_list = null;
			$year_option_list = null;
			$sex_list = null;
			
			$sex_int = $user->customget('sex');
			// генерируем список для даты рождения
			foreach($day_range as $s_day)
			{
				if($s_day == $birth_day)
				{
					$day_option_list .= $template->assign(array('option_value', 'option_name'), array($s_day, $s_day), $theme_option_active);
				}
				else
				{
					$day_option_list .= $template->assign(array('option_value', 'option_name'), array($s_day, $s_day), $theme_option_inactive);
				}
			}
			foreach($month_range as $s_month)
			{
				if($s_month == $birth_month)
				{
					$month_option_list .= $template->assign(array('option_value', 'option_name'), array($s_month, $s_month), $theme_option_active);
				}
				else
				{
					$month_option_list .= $template->assign(array('option_value', 'option_name'), array($s_month, $s_month), $theme_option_inactive);
				}
			}
			foreach($year_range as $s_year)
			{
				if($s_year == $birth_year)
				{
					$year_option_list .= $template->assign(array('option_value', 'option_name'), array($s_year, $s_year), $theme_option_active);
				}
				else
				{
					$year_option_list .= $template->assign(array('option_value', 'option_name'), array($s_year, $s_year), $theme_option_inactive);
				}
			}
			for($i=0;$i<=2;$i++)
			{
				if($i == $sex_int)
				{
					$sex_list .= $template->assign(array('option_value', 'option_name'), array($i, $this->sexLang($i)), $theme_option_active);
				}
				else
				{
					$sex_list .= $template->assign(array('option_value', 'option_name'), array($i, $this->sexLang($i)), $theme_option_inactive);
				}
			}
			$compiled_body = $template->assign(array('option_day', 'option_month', 'option_year', 'user_nickname', 'option_sex', 'user_phone', 'notify_messages', 'user_website'),
					array($day_option_list, $month_option_list, $year_option_list, $user->get('nick'), $sex_list, $user->customget('phone'), $notify, $user->customget('webpage')),
					$template->tplget('profile_settings_main', 'components/usercontrol/'));
		}
		$compiled_theme = $template->assign(array('user_photo_control', 'user_header', 'user_menu', 'user_main_block'),
				array($this->userProfilePhotoSettings($userid), $this->userProfileHeaders($userid), $this->showUserMenu($userid) , $compiled_body),
				$template->tplget('profile_main', 'components/usercontrol/'));
		$page->setContentPosition('body', $compiled_theme);
	}
	
	private function userPersonalMessage()
	{
		global $user,$page,$template,$rule,$database,$constant,$system,$language,$extension;
		$userid = $user->get('id');
		$way = $page->getPathway();
		if($userid < 1)
		{
			$page->setContentPosition('body', $template->compile404());
			return;
		}
		if($user->get('content_view', $userid) < 1)
		{
			$page->setContentPosition('body', $template->compileBan());
			return;
		}
		$rule->getInstance()->add('com.usercontrol.self_profile', true);
		$rule->getInstance()->add('com.usercontrol.in_friends', true);
		$rule->getInstance()->add('com.usercontrol.in_friends_request', false);
		$compiled_messages = null;
		if($way[1] == "write")
		{
			if($system->post('sendmessage'))
			{
				$to_user_id = $system->post('accepterid');
				$message_text = $system->nohtml($system->post('message'));
				if($system->isInt($to_user_id) && $this->inFriendsWith($to_user_id) && strlen($message_text) > 0)
				{
					$time = time();
					$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user_messages(`from`, `to`, `message`, `timeupdate`) VALUES(?, ?, ?, ?)");
					$stmt->bindParam(1, $userid, PDO::PARAM_INT);
					$stmt->bindParam(2, $to_user_id, PDO::PARAM_INT);
					$stmt->bindParam(3, $message_text, PDO::PARAM_STR);
					$stmt->bindParam(4, $time, PDO::PARAM_INT);
					$stmt->execute();
					$system->redirect('/message');
				}
			}
			
			$toid = $system->toInt($way[2]);
			$theme_main = $template->tplget('profile_message_write', 'components/usercontrol/');
			$theme_option_inactive = $template->tplget('form_select_option_inactive', 'components/usercontrol/');
			$theme_option_active = $template->tplget('form_select_option_active', 'components/usercontrol/');
			$result_option_select = null;
			
			$friendlist = $user->customget('friend_list');
			$friendarray = $system->altexplode(',', $friendlist);
			// Это сообщение с известным адресатом и данный адрессат есть в списке друзей и это не сам отправитель
			if($toid > 0 && $this->inFriendsWith($toid) && $toid != $userid)
			{
				$friendarray = $system->valueUnsetInArray($toid, $friendarray);
				$result_option_select .= $template->assign(array('option_value', 'option_name'), array($toid, $user->get('nick', $toid)), $theme_option_active);
			}
			// мультизагрузка, далее нужен $user->get('nick')
			$user->listload($friendlist);
			foreach($friendarray as $item)
			{
				$result_option_select .= $template->assign(array('option_value', 'option_name'), array($item, $user->get('nick', $item)), $theme_option_inactive);
			}
			
			$compiled_messages = $template->assign('option_names', $result_option_select, $theme_main);
		}
		elseif($way[1] == null || $way[1] == "all" || $way[1] == "in" || $way[1] == "out")
		{
			if($way[1] == null)
				$way[1] = "all";
			$page_id = (int)$way[2];
			$pm_on_page = $extension->getConfig('pm_count', 'usercontrol', 'components', 'int');
			$current_marker = $page_id * $pm_on_page;
			$total_pm_count = $this->getMessageTotalRows($userid, $way[1]);
			if($page_id > 0)
			{
				$rule->getInstance()->add('com.usercontrol.have_previous', true);
			}
			if($current_marker+$pm_on_page < $total_pm_count)
			{
				$rule->getInstance()->add('com.usercontrol.have_next', true);
			}
			$theme_body = $template->tplget('profile_message_body', 'components/usercontrol/');
			$theme_head = $template->tplget('profile_message_head', 'components/usercontrol/');
			if($way[1] == "in")
			{
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_messages WHERE `to` = ? ORDER BY timeupdate DESC LIMIT ?, ?");
				$stmt->bindParam(1, $userid, PDO::PARAM_INT);
				$stmt->bindParam(2, $current_marker, PDO::PARAM_INT);
				$stmt->bindParam(3, $pm_on_page, PDO::PARAM_INT);
				$stmt->execute();
			}
			elseif($way[1] == "out")
			{
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_messages WHERE `from` = ? ORDER BY timeupdate DESC LIMIT ?, ?");
				$stmt->bindParam(1, $userid, PDO::PARAM_INT);
				$stmt->bindParam(2, $current_marker, PDO::PARAM_INT);
				$stmt->bindParam(3, $pm_on_page, PDO::PARAM_INT);
				$stmt->execute();
			}
			else
			{
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_messages WHERE `to` = ? OR `from` = ? ORDER BY timeupdate DESC LIMIT ?, ?");
				$stmt->bindParam(1, $userid, PDO::PARAM_INT);
				$stmt->bindParam(2, $userid, PDO::PARAM_INT);
				$stmt->bindParam(3, $current_marker, PDO::PARAM_INT);
				$stmt->bindParam(4, $pm_on_page, PDO::PARAM_INT);
				$stmt->execute();
			}
			$resultAssoc = $stmt->fetchAll(PDO::FETCH_ASSOC);
			// подготавливаем пользовательские данные к загрузке в 1 запрос
			$user_to_dataload = $system->extractFromMultyArray('from', $resultAssoc);
			$user->listload($user_to_dataload);
			foreach($resultAssoc as $result)
			{
				$compiled_messages .= $template->assign(array('message_from_id', 'from_nick', 'user_avatar', 'user_message', 'message_topic_id'),
						array($result['from'], $user->get('nick', $result['from']), $user->buildAvatar('small', $result['from']), $result['message'], $result['id']),
						$theme_body);
			}
			$compiled_messages = $template->assign(array('message_body', 'message_type', 'message_prev', 'message_next'), 
					array($compiled_messages, $way[1], $page_id-1, $page_id+1), 
					$theme_head);
		}
		// отображаем всю ветку переписки
		elseif($way[1] == "topic" && $system->isInt($way[2]))
		{
			$topicId = $system->toInt($way[2]);
			if($system->post('newanswer'))
			{
				$message_new = $system->nohtml($system->post('topicanswer'));
				// Добавление сообщения в базу и обновление таймера апдейта
				if(strlen($message_new) > 0)
				{
					// является ли постер участником личной переписки? 
					$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user_messages WHERE id = ? AND (`from` = ? OR `to` = ?)");
					$stmt->bindParam(1, $topicId, PDO::PARAM_INT);
					$stmt->bindParam(2, $userid, PDO::PARAM_INT);
					$stmt->bindParam(3, $userid, PDO::PARAM_INT);
					$stmt->execute();
					$res = $stmt->fetch();
					$stmt = null;
					// постер или адресат или отправитель, вносим данные
					if($res[0] == "1")
					{
						$time = time();
						$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user_messages_answer(`topic`, `from`, `message`, `time`) VALUES(?, ?, ?, ?)");
						$stmt->bindParam(1, $topicId, PDO::PARAM_INT);
						$stmt->bindParam(2, $userid, PDO::PARAM_INT);
						$stmt->bindParam(3, $message_new, PDO::PARAM_STR);
						$stmt->bindParam(4, $time, PDO::PARAM_INT);
						$stmt->execute();
						$stmt = null;
						$stmt = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user_messages SET timeupdate = ? WHERE id = ?");
						$stmt->bindParam(1, $time, PDO::PARAM_INT);
						$stmt->bindParam(2, $topicId, PDO::PARAM_INT);
						$stmt->execute();
					}
				}
			}
			$theme_head = $template->tplget('profile_topic_head', 'components/usercontrol/');
			$theme_body = $template->tplget('profile_topic_body', 'components/usercontrol/');
			$topics_first = null;
			$topics_body = null;
			// выбираем первое сообщение
			$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_messages WHERE id = ? AND (`from` = ? or `to` = ?)");
			$stmt->bindParam(1, $topicId, PDO::PARAM_INT);
			$stmt->bindParam(2, $userid, PDO::PARAM_INT);
			$stmt->bindParam(3, $userid, PDO::PARAM_INT);
			$stmt->execute();
			// корневой топик он 1, если нет - топика или нет или реквестер не участвовал в переписке
			if($stmt->rowCount() == 1)
			{
				$result = $stmt->fetch();
				$topics_first = $template->assign(array('message_from_id', 'from_nick', 'user_avatar', 'user_message'), array($result['from'], $user->get('nick', $result['from']), $user->buildAvatar('small', $result['from']), $result['message']), $theme_body);
				$stmt = null;
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_messages_answer where topic = ? ORDER BY id DESC");
				$stmt->bindParam(1, $topicId, PDO::PARAM_INT);
				$stmt->execute();
				while($single_msg = $stmt->fetch())
				{
					$topics_body .= $template->assign(array('message_from_id', 'from_nick', 'user_avatar', 'user_message', 'answer_date'), array($single_msg['from'], $user->get('nick', $single_msg['from']), $user->buildAvatar('small', $single_msg['from']), $single_msg['message'], $system->toDate($single_msg['time'], 'h')), $theme_body);
				}
				$compiled_messages = $template->assign(array('topic_main_message', 'topic_answers'), array($topics_first, $topics_body), $theme_head);
			}
			else
			{
				$compiled_messages = $language->get('usercontrol_profile_view_null_info');
			}
		}
		$compiled_theme = $template->assign(array('user_photo_control', 'user_header', 'user_menu', 'user_main_block'),
				array($this->userProfilePhotoSettings($userid), $this->userProfileHeaders($userid), $this->showUserMenu($userid) , $compiled_messages),
				$template->tplget('profile_main', 'components/usercontrol/'));
		$page->setContentPosition('body', $compiled_theme);
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
			if($way[1] == null || $system->isInt($way[1]))
			{
				$content = $this->showUserList();;
			}
			elseif(substr($way[1], 0, 2) == "id")
			{
				if($system->isInt($userid) && $userid > 0 && $user->exists($userid))
				{
					if($user->get('content_view', $userid) < 1)
					{
						$content = $template->compileBan();
					}
					else
					{
						$this->dynamicRequests($userid);
						$user->get('id') == $userid ? $rule->getInstance()->add('com.usercontrol.self_profile', true) : $rule->getInstance()->add('com.usercontrol.self_profile', false);
						$this->inFriendsWith($userid) ? $rule->getInstance()->add('com.usercontrol.in_friends', true) : $rule->getInstance()->add('com.usercontrol.in_friends', false);
						$this->inFriendRequestWith($userid) ? $rule->getInstance()->add('com.usercontrol.in_friends_request', true) : $rule->getInstance()->add('com.usercontrol.in_friends_request', false);
						
						switch($way[2])
						{
							case "marks":
								$content = $this->showBookmarks($userid);
								break;
							case "friends":
								$content = $this->showFriends($userid);
								break;
							default:
								if($this->hook_item_url[$way[2]] != null)
								{
									$content = $this->hook_item_url[$way[2]];
								}
								else
								{
									$content = $this->showProfileUser($userid);
								}
								break;
						}
					}
				}
			}
		}
		if($content == null)
			$content = $template->compile404();
		$page->setContentPosition('body', $content);
		// можно добавить и обработчик по login / etc данным
	}

	// обработка сквозных запросов для профиля: инвайты в друзья, возможна дальнейшая доработка для хуков обновления через pop-up диалоги статуса, фото и прочее
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
		global $database,$constant,$template,$user,$system,$page,$extension;
		$usercount_on_page = $extension->getConfig('userlist_count', 'usercontrol', 'components', 'int');
		$way = $page->getPathway();
		$theme_head = $template->tplget('userlist_head', 'components/usercontrol/');
		$theme_body = $template->tplget('userlist_body', 'components/usercontrol/');
		$compiled_body = null;
		$currentOnline = null;
		$current_user_id = $user->get('id');
		if($current_user_id == null)
			$current_user_id = 0;
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user a, {$constant->db['prefix']}_user_custom b WHERE a.aprove = 0 AND a.id = b.id");
		$stmt->execute();
		$rowRegisteredFetch = $stmt->fetch();
		$allRegisteredCount = $rowRegisteredFetch[0];
		$stmt = null;
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user a, {$constant->db['prefix']}_user_custom b WHERE a.aprove = 0 AND a.id = b.id AND sex = 1");
		$stmt->execute();
		$rowMaleFetch = $stmt->fetch();
		$maleRegisteredCount = $rowMaleFetch[0];
		$stmt = null;
		$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user a, {$constant->db['prefix']}_user_custom b WHERE a.aprove = 0 AND a.id = b.id AND sex = 2");
		$stmt->execute();
		$rowFemaleFetch = $stmt->fetch();
		$femaleRegisteredCount = $rowFemaleFetch[0];
		$stmt = null;
		$time_difference = time() - 15 * 60;
		$stmt = $database->con()->prepare("SELECT a.reg_id, a.cookie, b.* FROM {$constant->db['prefix']}_statistic a, {$constant->db['prefix']}_user b WHERE a.`time` >= $time_difference AND a.reg_id > 0 AND a.reg_id = b.id GROUP BY a.reg_id, a.cookie");
		$stmt->execute();
		$rowOnlineUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach($rowOnlineUser as $onlineUser)
		{
			$currentOnline .= "<a href=\"{$constant->url}/user/id{$onlineUser['id']}\">{$onlineUser['nick']}</a> ";
		}
		$stmt = null;
		$limit_start = $way[1] * $usercount_on_page;
		$stmt = $database->con()->prepare("SELECT a.id, a.nick, b.regdate FROM {$constant->db['prefix']}_user a, {$constant->db['prefix']}_user_custom b WHERE a.id != ? AND a.id = b.id AND a.aprove = 0 ORDER BY a.id DESC LIMIT ?, ?");
		$stmt->bindParam(1, $current_user_id, PDO::PARAM_INT);
		$stmt->bindParam(2, $limit_start, PDO::PARAM_INT);
		$stmt->bindParam(3, $usercount_on_page, PDO::PARAM_INT);
		$stmt->execute();
		while($result = $stmt->fetch())
		{
			$compiled_body .= $template->assign(array('target_user_id', 'target_user_name', 'target_user_avatar', 'target_reg_date'), array($result['id'], $result['nick'], $user->buildAvatar('small', $result['id']), $system->toDate($result['regdate'], 'd')), $theme_body);
		}
		//echo $way[1];
		$pagination_tpl = $template->drowNumericPagination($way[1], $usercount_on_page, $allRegisteredCount, 'user/');
		return $template->assign(array('userlist', 'reg_all_count', 'reg_male_count', 'reg_female_count', 'reg_unknown_count', 'user_online_list', 'userlist_pagination'), array($compiled_body, $allRegisteredCount, $maleRegisteredCount, $femaleRegisteredCount, $allRegisteredCount-($maleRegisteredCount+$femaleRegisteredCount), $currentOnline, $pagination_tpl), $theme_head);
	}

	private function showFriends($userid)
	{
		global $user,$template,$page,$system,$rule,$database,$constant,$language,$extension;
		$body_compiled = null;
		$theme_head = $template->tplget('profile_friendlist_head', 'components/usercontrol/');
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
					$rule->getInstance()->add('com.usercontrol.profile_friend_request', true);
					$request_list = $user->customget('friend_request');
					if(strlen($request_list) > 0)
					{
						$theme_body = $template->tplget('profile_friendrequest_body', 'components/usercontrol/');
						// загружаем данные о пользователях 1 запросом
						$user->listload($request_list);
						$request_array = explode(",", $request_list);
						foreach($request_array as $requester_id)
						{
							$user_nick = $user->get('nick', $requester_id);
							$user_avatar = $user->buildAvatar('small', $requester_id);
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
					$theme_body = $template->tplget('profile_friendlist_body', 'components/usercontrol/');
					$friend_current_page = array_slice($friend_array, $page_start, $page_start+$rows_count);
					if(sizeof($friend_current_page) > 0)
					{
						$user->listload(implode(",", $friend_current_page));
						foreach($friend_current_page as $friend_id)
						{
							$user_nick = $user->get('nick', $friend_id);
							$user_avatar = $user->buildAvatar('small', $friend_id);
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
			$rule->getInstance()->add('com.usercontrol.have_previous', true);
		}
		else
		{
			$rule->getInstance()->add('com.usercontrol.have_previous', false);
		}
		if($total_marks_row > $marks_index+$marks_config_rows)
		{
			$rule->getInstance()->add('com.usercontrol.have_next', true);
		}
		else
		{
			$rule->getInstance()->add('com.usercontrol.have_next', false);
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
		if($way[0] == "message")
		{
			$rule->getInstance()->add('com.usercontrol.menu_message', true);
		}
		elseif($way[0] == "settings")
		{
			if($way[1] == "avatar")
			{
				$rule->getInstance()->add('com.usercontrol.menu_avatar', true);
			}
			else
			{
				$rule->getInstance()->add('com.usercontrol.menu_settings', true);
			}
		}
		else
		{
			switch($way[2])
			{
				case "marks":
					$rule->getInstance()->add('com.usercontrol.menu_mark', true);
					break;
				case "wall":
				case "":
					$rule->getInstance()->add('com.usercontrol.menu_wall', true);
					break;
				case "friends":
					$rule->getInstance()->add('com.usercontrol.menu_friends', true);
					break;
				default:
					$rule->getInstance()->add('com.usercontrol.menu_dropdown', true);
					break;
			}
		}
		if($this->hook_item_menu != null)
		{
			$rule->getInstance()->add('com.usercontrol.menu_dropdown_notempty', true);
		}
		return $template->assign(array('target_user_id', 'additional_hook_list'), array($userid, $this->hook_item_menu), $template->tplget('profile_block_menu', 'components/usercontrol/'));
	}

	private function showProfileUser($userid)
	{
		global $page,$user,$rule,$template,$language,$system,$database,$constant,$extension;
		$wall_post_limit = false;
		if($system->post('wall_post'))
		{
			$caster = $user->get('id');
			$time = time();
			$message = $system->nohtml($system->post('wall_text'));
			if($system->length($message) > 1 && $caster > 0 && $this->inFriendsWith($userid))
			{
				$stmt = $database->con()->prepare("SELECT time FROM {$constant->db['prefix']}_user_wall WHERE caster = ? AND target = ? ORDER BY id DESC LIMIT 1");
				$stmt->bindParam(1, $caster, PDO::PARAM_INT);
				$stmt->bindParam(2, $userid, PDO::PARAM_INT);
				$stmt->execute();
				$res = $stmt->fetch();
				$time_last_message = $res['time'];
				$stmt = null;
				
				if(($time - $time_last_message) >= $extension->getConfig('wall_post_delay', 'usercontrol', 'components', 'int'))
				{
					$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user_wall (target, caster, message, time) VALUES (?, ?, ?, ?)");
					$stmt->bindParam(1, $userid, PDO::PARAM_INT);
					$stmt->bindParam(2, $caster, PDO::PARAM_INT);
					$stmt->bindParam(3, $message, PDO::PARAM_STR);
					$stmt->bindParam(4, $time, PDO::PARAM_INT);
					$stmt->execute();
				}
				else
				{
					$wall_post_limit = true;
				}
			}
		}
		$way = $page->getPathway();
		$wall_marker = (int)$way[3];
		$profile_theme = $template->tplget('profile_main', 'components/usercontrol/');
		$profile_data_theme = $template->tplget('profile_block_data', 'components/usercontrol/');
		// пользовательские данные
		$regdate = $system->toDate($user->customget('regdate', $userid), 'd');
		$birthday = $system->toDate($user->customget('birthday', $userid), 'd');
		$sex_int = $user->customget('sex', $userid);
		$website = $user->customget('webpage', $userid);
		if(strlen($website) > 0)
		{
			$rule->getInstance()->add('com.usercontrol.have_webpage', true);
		}
		$sex = $this->sexLang($sex_int);
		$phone = $user->customget('phone', $userid);
		if(strlen($phone) > 0)
		{
			$rule->getInstance()->add('com.usercontrol.have_phone', true);
		}
		$user_compiled_menu = $this->showUserMenu($userid);
		$profile_compiled_data = $template->assign(array('user_regdate', 'user_birthday', 'user_sex', 'user_phone', 'target_user_id', 'user_wall', 'wall_prev', 'wall_next', 'user_website'),
				array($regdate, $birthday, $sex, $phone, $userid, $this->loadUserWall($userid, $wall_marker, $wall_post_limit), $wall_marker-1, $wall_marker+1, $website),
				$profile_data_theme);
		$compiled_theme = $template->assign(array('user_photo_control', 'user_header', 'user_menu', 'user_main_block'),
				array($this->userProfilePhotoSettings($userid), $this->userProfileHeaders($userid), $user_compiled_menu , $profile_compiled_data),
				$profile_theme);
		return $compiled_theme;
	}
	
	private function sexLang($int)
	{
		global $language;
		if($int == 1)
		{
			return $language->get('usercontrol_profile_sex_man');
		}
		elseif($int == 2)
		{
			return $language->get('usercontrol_profile_sex_woman');
		}
		else
		{
			return $language->get('usercontrol_profile_sex_unknown');
		}
	}

	private function loadUserWall($userid, $marker, $limit = false)
	{
		global $template,$database,$constant,$user,$language,$extension,$rule,$system;
		$theme = $template->tplget('profile_wall', 'components/usercontrol/');
		$output = null;
		if($limit)
		{
			$output .= $template->stringNotify('error', $language->get('usercontrol_profile_wall_answer_spamdetect'));
		}
		$wall_rows = $extension->getConfig('wall_post_count', 'usercontrol', 'components', 'int');
		$marker_index = $marker * $wall_rows;
		$wall_total_rows = $this->getWallTotalRows($userid);
		if($marker > 0)
		{
			$rule->getInstance()->add('com.usercontrol.have_previous', true);
		}
		else
		{
			$rule->getInstance()->add('com.usercontrol.have_previous', false);
		}
		if($wall_total_rows > $marker_index+$wall_rows)
		{
			$rule->getInstance()->add('com.usercontrol.have_next', true);
		}
		else
		{
			$rule->getInstance()->add('com.usercontrol.have_next', false);
		}
		$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_wall WHERE target = ? ORDER by time DESC LIMIT ?, ?");
		$stmt->bindParam(1, $userid, PDO::PARAM_INT);
		$stmt->bindParam(2, $marker_index, PDO::PARAM_INT);
		$stmt->bindParam(3, $wall_rows, PDO::PARAM_INT);
		$stmt->execute();
		$resultAssoc = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$user->listload($system->extractFromMultyArray('caster', $resultAssoc));
		foreach($resultAssoc as $result)
		{
			$from_name = $user->get('nick', $result['caster']);
			if(strlen($from_name) < 1)
			{
				$from_name = $language->get('usercontrol_profile_name_unknown');
			}
			$message = $result['message'];
			$output .= $template->assign(array('wall_from', 'wall_message', 'wall_from_id', 'wall_message_id', 'user_avatar'), array($from_name, $message, $result['caster'], $result['id'], $user->buildAvatar('small', $result['caster'])), $theme);
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
	
	private function getMessageTotalRows($userid, $type)
	{
		global $database,$constant;
		if($type == "in")
		{
			$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user_messages WHERE `to` = ?");
			$stmt->bindParam(1, $userid, PDO::PARAM_INT);
		}
		elseif($type == "out")
		{
			$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user_messages WHERE `from` = ?");
			$stmt->bindParam(1, $userid, PDO::PARAM_INT);
		}
		else
		{
			$stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user_messages WHERE `to` = ? OR `from` = ?");
			$stmt->bindParam(1, $userid, PDO::PARAM_INT);
			$stmt->bindParam(2, $userid, PDO::PARAM_INT);
		}
		$stmt->execute();
		$res = $stmt->fetch();
		return $res[0];
	}

	private function userProfilePhotoSettings($userid)
	{
		global $template,$user;
		$parsed = $template->assign(array('user_avatar', 'target_user_id'),
				array($user->buildAvatar('big', $userid), $userid),
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
			if($extension->getConfig('login_captcha', 'usercontrol', 'components', 'boolean') && !$hook->get('captcha')->validate($system->post('captcha')))
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
				$md5pwd = $system->doublemd5($system->post('password'));
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
		$captcha = $hook->get('captcha')->show();
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
			$md5pwd = $system->doublemd5($pass);
			if($extension->getConfig('register_captcha', 'usercontrol', 'components', 'boolean') && !$hook->get('captcha')->validate($system->post('captcha')))
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_captcha_form_error'));
			}
			if(!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_invalid_email_error'));
			}
			if(!$system->validPasswordLength($pass))
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
				$user_obtained_id = $database->con()->lastInsertId();
				$stmt = null;
				$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user_custom (`id`) VALUES (?)");
				$stmt->bindParam(1, $user_obtained_id, PDO::PARAM_INT);
				$stmt->execute();
				$stmt = null;
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
		$captcha = $hook->get('captcha')->show();
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
				if(strlen($system->post('captcha')) < 1 || !$hook->get('captcha')->validate($system->post('captcha')))
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
			$captcha = $hook->get('captcha')->show();
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