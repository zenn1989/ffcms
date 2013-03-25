<?php
// регистрируем компонент
if(!extension::registerPathWay(array('login', 'register', 'recovery', 'logout', 'aprove'), 'usercontrol')) { exit("Component usercontrol cannot be registered!"); }
page::setNoCache('login');
page::setNoCache('register');
page::setNoCache('recovery');
page::setNoCache('logout');

class com_usercontrol_front
{
	public function load()
	{
		global $page,$template;
		$way = $page->getPathway();
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
			default:
				break;
		}
	}
	
	private function loginComponent()
	{
		global $page,$template,$hook,$language,$database,$system,$constant,$user;
		if($user->get('id') != NULL)
		{
			$page->setContentPosition('body', $template->compile404());
			return;
		}
		$notify = null;
		if($system->post('submit'))
		{
			$loginoremail = $system->post('email');
			if(strlen($system->post('captcha')) < 1 || strtolower($system->post('captcha')) != $_SESSION['captcha'])
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
		$theme = $template->tplget('com_usercontrol_login', 'components/');
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
			$page->setContentPosition('body', $template->tplget('com_usercontrol_aprove', 'components/'));
		}		
	}
	
	private function regComponent()
	{
		global $user,$template,$hook,$page,$language,$database,$constant,$system,$mail;
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
			if(strlen($system->post('captcha')) < 1 || strtolower($system->post('captcha')) != $_SESSION['captcha'])
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
				$validate = $system->randomWithUnique($email);
				$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user (`login`, `email`, `nick`, `pass`, `aprove`) VALUES (?,?,?,?,?)");
				$stmt->bindParam(1, $login, PDO::PARAM_STR, 64);
				$stmt->bindParam(2, $email, PDO::PARAM_STR);
				$stmt->bindParam(3, $nickname, PDO::PARAM_STR);
				$stmt->bindParam(4, $md5pwd, PDO::PARAM_STR, 32);
				$stmt->bindParam(5, $validate, PDO::PARAM_STR, 32);
				$stmt->execute();
				$notify .= $template->stringNotify('success', $language->get('usercontrol_register_success'));
				$mail_body = $template->tplget('mail');
				$link = '<a href="'.$constant->url.'/aprove/'.$validate.'">'.$language->get('usercontrol_reg_mail_aprove_link_text').' - '.$constant->url.'</a>';
				$mail_body = $template->assign(array('title', 'description', 'text', 'footer'), array($language->get('usercontrol_reg_mail_title'), $language->get('usercontrol_reg_mail_description'), $link, $language->get('usercontrol_reg_mail_footer')), $mail_body);
				$mail->send($email, $language->get('usercontrol_reg_mail_title'), $mail_body, $nickname);
			}
		}
		$theme = $template->tplget('com_usercontrol_reg', 'components/');
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
			$theme = $template->tplget('com_usercontrol_recovery', 'components/');
			$captcha = $hook->get('captcha');
			$theme = $template->assign(array('captcha', 'notify'), array($captcha, $notify), $theme);
			$page->setContentPosition('body', $theme);
		}
	}
	
	private function doLogOut()
	{
		global $system,$user;
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