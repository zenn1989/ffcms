<?php
// регистрируем компонент
if(!page::registerPathWay(array('login', 'register', 'recovery', 'logout', 'aprove'), 'usercontrol')) { exit("Component usercontrol cannot be registered!"); }
// запрещаем кеширование компонента
page::setNoCache('login');
page::setNoCache('register');
page::setNoCache('recovery');
page::setNoCache('logout');


class com_usercontrol
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
		if($_POST['submit'])
		{
			if(strlen($_POST['captcha']) < 1 || strtolower($_POST['captcha']) != $_SESSION['captcha'])
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_captcha_form_error'));
			}
			if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_invalid_email_error'));
			}
			if(strlen($_POST['password']) < 4 || strlen($_POST['password']) > 32)
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_incorrent_password_error'));
			}
			// все хорошо, ошибок нет, можно идти к SQL запросу
			if($notify == null)
			{
				$md5pwd = md5($_POST['password']);
				$stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user WHERE email = ? AND pass = ?");
				$stmt->bindParam(1, $_POST['email'], PDO::PARAM_STR);
				$stmt->bindParam(2, $md5pwd, PDO::PARAM_STR, 32);
				$stmt->execute();
				if($stmt->rowCount() == 1)
				{
					$md5token = $system->md5random();
					$nixtime = time();
					$stmt2 = $database->con()->prepare("UPDATE {$constant->db['prefix']}_user SET token = ?, token_start = ? WHERE email = ? AND pass = ?");
					$stmt2->bindParam(1, $md5token);
					$stmt2->bindParam(2, $nixtime);
					$stmt2->bindParam(3, $_POST['email']);
					$stmt2->bindParam(4, $md5pwd);
					$stmt2->execute();
					
					setcookie('email', $_POST['email'], 0, '/');
					setcookie('token', $md5token, 0, '/');
					$system->redirect();
					exit();
				}
				else
				{
					// видимо пароль не верный
					
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
		if($_POST['submit'])
		{
			$nickname = $system->nohtml($_POST['nick']);
			$md5pwd = md5($_POST['password']);
			if(strlen($_POST['captcha']) < 1 || strtolower($_POST['captcha']) != $_SESSION['captcha'])
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_captcha_form_error'));
			}
			if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_invalid_email_error'));
			}
			if(strlen($_POST['password']) < 4 || strlen($_POST['password']) > 32)
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_incorrent_password_error'));
			}
			if($this->mailExists($_POST['email']))
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_mail_exist'));
			}
			if(strlen($nickname) < 3 || strlen($nickname) > 64)
			{
				$notify .= $template->stringNotify('error', $language->get('usercontrol_nick_incorrent'));
			}
			if($notify == null)
			{
				$validate = $system->randomWithUnique($_POST['email']);
				$stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user (`email`, `nick`, `pass`, `aprove`) VALUES (?,?,?,?)");
				$stmt->bindParam(1, $_POST['email'], PDO::PARAM_STR);
				$stmt->bindParam(2, $nickname, PDO::PARAM_STR);
				$stmt->bindParam(3, $md5pwd, PDO::PARAM_STR, 32);
				$stmt->bindParam(4, $validate, PDO::PARAM_STR, 32);
				$stmt->execute();
				$notify .= $template->stringNotify('success', $language->get('usercontrol_register_success'));
				$mail_body = $template->tplget('mail');
				$link = '<a href="'.$constant->url.'/aprove/'.$validate.'">Подтвердить регистрацию - '.$constant->url.'</a>';
				$mail_body = $template->assign(array('title', 'description', 'text', 'footer'), array($language->get('usercontrol_reg_mail_title'), $language->get('usercontrol_reg_mail_description'), $link, $language->get('usercontrol_reg_mail_footer')), $mail_body);
				$mail->send($_POST['email'], $language->get('usercontrol_reg_mail_title'), $mail_body, $nickname);
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
			if($_POST['submit'])
			{
				if(strlen($_POST['captcha']) < 1 || strtolower($_POST['captcha']) != $_SESSION['captcha'])
				{
					$notify .= $template->stringNotify('error', $language->get('usercontrol_captcha_form_error'));
				}
				if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
				{
					$notify .= $template->stringNotify('error', $language->get('usercontrol_invalid_email_error'));
				}
				if($notify == null)
				{
					$new_password = $system->randomString(rand(8,12));
					$hash = $system->md5random();
					$stmt = $database->con()->prepare("SELECT id FROM {$constant->db['prefix']}_user WHERE email = ?");
					$stmt->bindParam(1, $_POST['email']);
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
						$mail->send($_POST['email'], $language->get('usercontrol_reg_mail_title'), $mail_body, $nickname);
						//echo $recovery_link;
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
		setcookie('email', '', 0, '/');
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

}

?>