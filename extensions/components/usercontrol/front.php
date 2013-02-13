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
				return $this->loginComponent();
				break;
			case "register":
				return $this->regComponent();
				break;
			case "recovery":
				return $this->recoveryComponent();
				break;
			default:
				return $template->compile404();
				break;
		}
	}
	
	private function loginComponent()
	{
		global $page,$template,$hook,$language;
		$notify = null;
		if($_POST['submit'])
		{
			if(strtolower($_POST['captcha']) != $_SESSION['captcha'])
			{
				$notify .= $language->get('captcha_header_error');
			}
		}
		$theme = $template->tplget('com_usercontrol_login', 'components/');
		$captcha = $hook->get('captcha');
		$theme = $template->assign('captcha', $captcha, $theme);
		$page->setContentPosition('body', $theme);
	}
	
	private function regComponent()
	{
		
	}
	
	private function recoveryComponent()
	{
	
	}

}

?>