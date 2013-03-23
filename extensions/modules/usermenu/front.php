<?php

class mod_usermenu_front implements mod_front
{
	public function after()
	{
		global $template,$user;
		if($user->get('id') == NULL)
		{
			$usermenu = $template->tplget('unregistered_menu', 'modules/mod_usermenu/');
		}
		else
		{
			$usermenu = $template->tplget('registered_menu', 'modules/mod_usermenu/');
			$usermenu = $template->assign('username', $user->get('nick'), $usermenu);
		}
		$template->set('mod_usermenu', $usermenu);
	
	}
	
	public function before() { }

}


?>