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
	
	}
	
	private function regComponent()
	{
	
	}
	
	private function recoveryComponent()
	{
	
	}

}

?>