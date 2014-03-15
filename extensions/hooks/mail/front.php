<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\property;

class hooks_mail_front {
    protected static $instance = null;
    protected $mailer = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function send($to, $title, $text, $ownername = null) {
        if(is_null($this->mailer)) {
            require_once(root . '/resource/phpmailer/class.phpmailer.php');
            $this->mailer = new PHPMailer(true);
        }
        if (property::getInstance()->get('mail_smtp_use') != 0) {
            $this->mailer->IsSMTP(); // telling the class to use SMTP
            $this->mailer->SMTPDebug = 0; // enables SMTP debug information (for testing)

            $this->mailer->SMTPAuth = property::getInstance()->get('mail_smtp_auth'); // enable SMTP authentication
            $this->mailer->Host = property::getInstance()->get('mail_smtp_host'); // sets the SMTP server
            $this->mailer->Port = property::getInstance()->get('mail_smtp_port'); // set the SMTP port for the GMAIL server
            $this->mailer->Username = property::getInstance()->get('mail_smtp_login'); // SMTP account username
            $this->mailer->Password = property::getInstance()->get('mail_smtp_password'); // SMTP account password

            $this->mailer->SetFrom(property::getInstance()->get('mail_from'), property::getInstance()->get('mail_ownername'));
            $this->mailer->AddReplyTo(property::getInstance()->get('mail_from'), property::getInstance()->get('mail_ownername'));
            $this->mailer->Subject = $title;
            $this->mailer->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
            $this->mailer->MsgHTML($text);
            $this->mailer->AddAddress($to, $ownername);
            $this->mailer->IsHTML(true);

            return $this->mailer->Send();
        } else {
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
            $headers .= 'From: '.property::getInstance()->get('mail_from') . "\r\n";
            return mail($to, $title, $text, $headers);
        }
    }
}