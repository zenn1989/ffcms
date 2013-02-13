<?php
// регистрируем компонент
if(!page::registerPathWay(array('login', 'register', 'recovery'), 'usercontrol')) { exit("Component usercontrol cannot be registered!"); }
// запрещаем кеширование компонента
page::setNoCache('login');
page::setNoCache('register');
page::setNoCache('recovery');


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
			default:
				$template->compile404();
				break;
		}
	}
	
	private function loginComponent()
	{
		global $page,$template,$hook,$language,$database,$system,$constant;
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
	
	private function regComponent()
	{
		
	}
	
	private function recoveryComponent()
	{
	
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