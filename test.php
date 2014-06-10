<?php
require 'vendor/autoload.php';


$msg = Swift_Message::newInstance();
    $msg->setSubject('Power report-')
            ->setFrom('aafetsrom@htghana.com')
            ->setTo('aafetsrom@htghana.com')
            ->setBody('Please find attached your requested report');
    $transport = Swift_SmtpTransport::newInstance('mail.htghana.com', '25');
    $transport->setUsername('aafetsrom@htghana.com')
            ->setPassword('!!AFtony19833');
    
    $mail = new Swift_Mailer($transport);
    $mail->send($msg);
    
?>