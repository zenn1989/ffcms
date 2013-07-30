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
        global $page, $template, $system, $user, $meta, $database, $constant, $hook, $language;
        $notify = null;
        if($system->post('dofeedback')) {
            $poster_name = $system->nohtml($system->post('topic_name'));
            $topic_title = $system->nohtml($system->post('topic_title'));
            $topic_text = $system->nohtml($system->post('topic_body'));
            $poster_email = $user->get('id') > 0 ? $user->get('email') : $system->post('topic_email');
            $captcha = $system->post('captcha');
            $date = time();
            if(!filter_var($poster_email, FILTER_VALIDATE_EMAIL)) {
                $notify .= $template->stringNotify('error', $language->get('feedback_error_email'));
            }
            if($system->length($topic_title) < 3 || $system->length($topic_title) > 70) {
                $notify .= $template->stringNotify('error', $language->get('feedback_error_title'));
            }
            if($system->length($poster_name) < 3 || $system->length($poster_name) > 50) {
                $notify .= $template->stringNotify('error', $language->get('feedback_error_name'));
            }
            if($system->length($topic_text) < 10) {
                $notify .= $template->stringNotify('error', $language->get('feedback_error_text'));
            }
            if(!$hook->get('captcha')->validate($captcha)) {
                $notify .= $template->stringNotify('error', $language->get('feedback_error_captcha'));
            }

            if($notify == null) {
                $stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_com_feedback (`from_name`, `from_email`, `title`, `text`, `time`) VALUES (?, ?, ?, ?, ?)");
                $stmt->bindParam(1, $poster_name, PDO::PARAM_STR);
                $stmt->bindParam(2, $poster_email, PDO::PARAM_STR);
                $stmt->bindParam(3, $topic_title, PDO::PARAM_STR);
                $stmt->bindParam(4, $topic_text, PDO::PARAM_STR);
                $stmt->bindParam(5, $date, PDO::PARAM_INT);
                $stmt->execute();
                $notify = $template->stringNotify('success', $language->get('feedback_success_send'));
            }
        }
        $theme = $template->get('form', 'components/feedback/');
        $page->setContentPosition('body', $template->assign(array('notify', 'captcha'), array($notify, $hook->get('captcha')->show()), $theme));
        $meta->add('title', 'Обратная связь');
    }

}


?>