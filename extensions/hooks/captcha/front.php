<?php
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
        $session_value = $_SESSION['captcha'];
        return (strlen($session_value) > 0 && strtolower($session_value) == strtolower($postdata)) ? true : false;
    }

    public function show()
    {
        global $constant;
        return $constant->url . '/resource/ccaptcha/captcha.php';
    }
}

?>