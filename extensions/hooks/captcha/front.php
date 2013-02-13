<?php
/**
* Хук отвечающий за капчу
*/

class hook_captcha
{

	// обязательно возвращение полного URL
	public function load()
	{
		global $constant;
		return $constant->url.'/public/ccaptcha/captcha.php';
	}

}

?>