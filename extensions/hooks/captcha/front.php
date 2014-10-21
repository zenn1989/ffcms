<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\extension;
use engine\property;

class hooks_captcha_front extends \engine\singleton {

    public function validate($postdata)
    {
        $captcha_type = extension::getInstance()->getConfig('captcha_type', 'captcha', 'hooks');
        if($captcha_type == "recaptcha") {
            require_once(root."/resource/recaptcha/recaptchalib.php");
            $resp = recaptcha_check_answer (extension::getInstance()->getConfig('captcha_privatekey', 'captcha', 'hooks'), $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
            return $resp->is_valid;
        }
        $session_value = $_SESSION['captcha'];
        return (strlen($session_value) > 0 && strtolower($session_value) == strtolower($postdata)) ? true : false;
    }

    public function show()
    {
        $captcha_type = extension::getInstance()->getConfig('captcha_type', 'captcha', 'hooks');
        if($captcha_type == "recaptcha") {
            require_once(root . "/resource/recaptcha/recaptchalib.php");
            return recaptcha_get_html(extension::getInstance()->getConfig('captcha_publickey', 'captcha', 'hooks'));
        }
        return property::getInstance()->get('script_url') . '/resource/ccaptcha/captcha.php';
    }
}

?>