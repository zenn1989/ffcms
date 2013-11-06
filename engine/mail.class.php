<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

/**
 * Класс работы с доставкой почты
 */

class mail
{
    /**
     * Отправка почты, 3 обязательных параметра.
     */
    public function send($to, $title, $text, $ownername = "unknown")
    {
        global $engine;
        require_once($engine->constant->root . '/resource/phpmailer/class.phpmailer.php');
        $mail = new PHPMailer(true);
        if ($engine->constant->mail['smtp_enabled']) {
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->SMTPDebug = 0; // enables SMTP debug information (for testing)

            $mail->SMTPAuth = $engine->constant->mail['smtp_auth']; // enable SMTP authentication
            $mail->Host = $engine->constant->mail['smtp_host']; // sets the SMTP server
            $mail->Port = $engine->constant->mail['smtp_port']; // set the SMTP port for the GMAIL server
            $mail->Username = $engine->constant->mail['smtp_user']; // SMTP account username
            $mail->Password = $engine->constant->mail['smtp_password']; // SMTP account password

            $mail->SetFrom($engine->constant->mail['from_email'], $engine->constant->mail['ownername']);
            $mail->AddReplyTo($engine->constant->mail['from_email'], $engine->constant->mail['ownername']);
            $mail->Subject = $title;
            $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
            $mail->MsgHTML($text);
            $mail->AddAddress($to, $ownername);
            $mail->IsHTML(true);

            return $mail->Send();
        } else {
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
            $headers .= 'From: '.$engine->constant->mail['from_email'] . "\r\n";
            return mail($to, $title, $text, $headers);
        }
    }
}


?>