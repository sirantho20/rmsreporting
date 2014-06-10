<?php
require 'vendor/autoload.php';

echo 'you will receive soon';
fastcgi_finish_request();

$msg = Swift_Message::newInstance();
    $msg->setSubject('Power report-')
            ->setFrom('sirantho20@gmail.com')
            ->setTo('aafetsrom@htghana.com')
            ->setBody('Please find attached your requested report');
    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl");
    $transport->setUsername('sirantho20@gmail.com')
            ->setPassword('afTONY19833');
    
    $mail = new Swift_Mailer($transport);
    $mail->send($msg);
    
?>