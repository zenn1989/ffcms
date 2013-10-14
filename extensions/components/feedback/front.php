<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//
if (!extension::registerPathWay('feedback', 'feedback')) {
    exit("Component feedback cannot be registered!");
}
page::setNoCache('feedback');


class com_feedback_front implements com_front
{
    public function load()
    {
        global $engine;
        $notify = null;
        $engine->rule->add('com.feedback.captcha_full', $engine->extension->getConfig('captcha_type', 'captcha', 'hooks') == "recaptcha" ? true : false);
        if($engine->system->post('dofeedback')) {
            $poster_name = $engine->system->nohtml($engine->system->post('topic_name'));
            $topic_title = $engine->system->nohtml($engine->system->post('topic_title'));
            $topic_text = $engine->system->nohtml($engine->system->post('topic_body'));
            $poster_email = $engine->user->get('id') > 0 ? $engine->user->get('email') : $engine->system->post('topic_email');
            $captcha = $engine->system->post('captcha');
            $date = time();
            if(!filter_var($poster_email, FILTER_VALIDATE_EMAIL)) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('feedback_error_email'));
            }
            if($engine->system->length($topic_title) < 3 || $engine->system->length($topic_title) > 70) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('feedback_error_title'));
            }
            if($engine->system->length($poster_name) < 3 || $engine->system->length($poster_name) > 50) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('feedback_error_name'));
            }
            if($engine->system->length($topic_text) < 10) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('feedback_error_text'));
            }
            if(!$engine->hook->get('captcha')->validate($captcha)) {
                $notify .= $engine->template->stringNotify('error', $engine->language->get('feedback_error_captcha'));
            }

            if($notify == null) {
                $stmt = $engine->database->con()->prepare("INSERT INTO {$engine->constant->db['prefix']}_com_feedback (`from_name`, `from_email`, `title`, `text`, `time`) VALUES (?, ?, ?, ?, ?)");
                $stmt->bindParam(1, $poster_name, PDO::PARAM_STR);
                $stmt->bindParam(2, $poster_email, PDO::PARAM_STR);
                $stmt->bindParam(3, $topic_title, PDO::PARAM_STR);
                $stmt->bindParam(4, $topic_text, PDO::PARAM_STR);
                $stmt->bindParam(5, $date, PDO::PARAM_INT);
                $stmt->execute();
                $notify = $engine->template->stringNotify('success', $engine->language->get('feedback_success_send'));
            }
        }
        $theme = $engine->template->get('form', 'components/feedback/');
        $engine->page->setContentPosition('body', $engine->template->assign(array('notify', 'captcha'), array($notify, $engine->hook->get('captcha')->show()), $theme));
        $engine->meta->add('title', 'Обратная связь');
    }

}


?>