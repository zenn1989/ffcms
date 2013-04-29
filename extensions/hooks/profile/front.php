<?php 
class hook_profile_front implements hook_front
{
	public function load()
	{
		return $this;
	}
	
	public function before()
	{
		if(class_exists('com_usercontrol_front'))
		{
			$class_to_hook = 'com_usercontrol_front';
			$call = new $class_to_hook;
			$call->hook_item_menu = "<li>Item</li>";
		}
	}
}


?>