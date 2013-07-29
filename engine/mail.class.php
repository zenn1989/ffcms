<?php
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
        global $constant;
        require_once($constant->root . '/engine/phpmailer/class.phpmailer.php');
        $mail = new PHPMailer(true);
        if ($constant->mail['smtp_enabled']) {
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->SMTPDebug = 0; // enables SMTP debug information (for testing)

            $mail->SMTPAuth = $constant->mail['smtp_auth']; // enable SMTP authentication
            $mail->Host = $constant->mail['smtp_host']; // sets the SMTP server
            $mail->Port = $constant->mail['smtp_port']; // set the SMTP port for the GMAIL server
            $mail->Username = $constant->mail['smtp_user']; // SMTP account username
            $mail->Password = $constant->mail['smtp_password']; // SMTP account password

            $mail->SetFrom($constant->mail['from_email'], $constant->mail['ownername']);
            $mail->AddReplyTo($constant->mail['from_email'], $constant->mail['ownername']);
            $mail->Subject = $title;
            $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
            $mail->MsgHTML($text);
            $mail->AddAddress($to, $ownername);
            $mail->IsHTML(true);

            return $mail->Send();
        } else {
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
            $headers .= 'From: '.$constant->mail['from_email'] . "\r\n";
            return mail($to, $title, $text, $headers);
        }
    }
}


?>