<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\user;
use engine\system;
use engine\extension;
use engine\database;
use engine\property;
use engine\meta;
use engine\language;
use engine\template;

class components_feedback_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $params = array();
        $params['captcha_full'] = extension::getInstance()->getConfig('captcha_type', 'captcha', 'hooks') == "recaptcha" ? true : false;
        $params['captcha'] = extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->show();
        if(system::getInstance()->post('dofeedback')) {
            $poster_name = system::getInstance()->nohtml(system::getInstance()->post('topic_name'));
            $topic_title = system::getInstance()->nohtml(system::getInstance()->post('topic_title'));
            $topic_text = system::getInstance()->nohtml(system::getInstance()->post('topic_body'));
            $poster_email = user::getInstance()->get('id') > 0 ? user::getInstance()->get('email') : system::getInstance()->post('topic_email');
            $captcha = system::getInstance()->post('captcha');
            $date = time();
            if(!filter_var($poster_email, FILTER_VALIDATE_EMAIL)) {
                $params['notify']['wrong_email'] = true;
            }
            if(system::getInstance()->length($topic_title) < 3 || system::getInstance()->length($topic_title) > 70) {
                $params['notify']['wrong_title'] = true;
            }
            if(system::getInstance()->length($poster_name) < 3 || system::getInstance()->length($poster_name) > 50) {
                $params['notify']['wrong_name'] = true;
            }
            if(system::getInstance()->length($topic_text) < 10) {
                $params['notify']['wrong_text'] = true;
            }
            if(!extension::getInstance()->call(extension::TYPE_HOOK, 'captcha')->validate($captcha)) {
                $params['notify']['wrong_captcha'] = true;
            }

            if(sizeof($params['notify']) == 0) {
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_feedback (`from_name`, `from_email`, `title`, `text`, `time`) VALUES (?, ?, ?, ?, ?)");
                $stmt->bindParam(1, $poster_name, PDO::PARAM_STR);
                $stmt->bindParam(2, $poster_email, PDO::PARAM_STR);
                $stmt->bindParam(3, $topic_title, PDO::PARAM_STR);
                $stmt->bindParam(4, $topic_text, PDO::PARAM_STR);
                $stmt->bindParam(5, $date, PDO::PARAM_INT);
                $stmt->execute();
                $params['notify']['success'] = true;
            }
        }
        meta::getInstance()->add('title', language::getInstance()->get('feedback_form_title'));
        $render = template::getInstance()->twigRender('components/feedback/form.tpl', array('local' => $params));
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $render);
    }
}