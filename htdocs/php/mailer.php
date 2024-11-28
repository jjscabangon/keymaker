<!--for forgot password func-->
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require __DIR__ . "/../vendor/autoload.php";

$mail = new PHPMailer(true);

$mail->SMTPDebug = SMTP::DEBUG_SERVER;

$mail->isSMTP();
$mail->SMTPAuth = true;

$mail->Host = "smtp.gmail.com";
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port = 465;
$mail->Username = "bellpip.88@gmail.com";
$mail->Password = "qmtv lccg bqxb yrgy";

$mail->isHtml(true);

return $mail;