<?php

class mod_usermenu
{
	public function after()
	{
		global $template,$user;
		if($user->get('id') == NULL)
		{
			echo "PWNZ?".$user->get('id');
			$usermenu = $template->tplget('unregistered_menu', 'modules/mod_usermenu/');
		}
		else
		{
			$usermenu = $template->tplget('registered_menu', 'modules/mod_usermenu/');
			$usermenu = $template->assign('username', $user->get('nick'), $usermenu);
		}
		$template->set('mod_usermenu', $usermenu);
	
	}

}


?>