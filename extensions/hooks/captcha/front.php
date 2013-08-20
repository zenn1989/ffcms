<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 * Хук отвечающий за капчу
 * В будующем доработать конфигурации для разных капч (recaptcha, kcaptcha, etc)
 */

class hook_captcha_front implements hook_front
{

    // возвращение себя самого
    public function load()
    {
        return $this;
    }

    public function validate($postdata)
    {
        global $extension, $constant;
        $captcha_type = $extension->getConfig('captcha_type', 'captcha', 'hooks');
        if($captcha_type == "recaptcha") {
            require_once($constant->root."/resource/recaptcha/recaptchalib.php");
            $resp = recaptcha_check_answer ($extension->getConfig('captcha_privatekey', 'captcha', 'hooks'), $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
            return $resp->is_valid;
        }
        $session_value = $_SESSION['captcha'];
        return (strlen($session_value) > 0 && strtolower($session_value) == strtolower($postdata)) ? true : false;
    }

    public function show()
    {
        global $extension, $constant;
        $captcha_type = $extension->getConfig('captcha_type', 'captcha', 'hooks');
        if($captcha_type == "recaptcha") {
            require_once($constant->root."/resource/recaptcha/recaptchalib.php");
            return recaptcha_get_html($extension->getConfig('captcha_publickey', 'captcha', 'hooks'));
        }
        return $constant->url . '/resource/ccaptcha/captcha.php';
    }
}

?>