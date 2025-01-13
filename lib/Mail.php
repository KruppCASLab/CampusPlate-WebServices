<?php

require_once(__DIR__ . "/Config.php");

require_once(__DIR__ . "/Exception.php");
require_once(__DIR__ . "/PHPMailer/PHPMailer.php");
require_once(__DIR__ . "/PHPMailer/SMTP.php");


use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    static function sendPinEmail($email, $pin) {
        $config = new Config();

        $emailText = "
        <html>
          <head>
          </head>
          <body>
            <h2>Campus Plate - Thank You For Registering</h2>
            <p>
                A new account was registered for the following email: $email
                
                <br /><br />
                
               Please use the following PIN to confirm your account: <pre>$pin</pre>

            </p>
          </body>
        </html>
        ";

        $mail = new PHPMailer(); // create a new object
        $mail->IsSMTP(); // enable SMTP
        //$mail->SMTPDebug = 1 ; // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth = true; // authentication enabled
        $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 587; // 465 or 587
        $mail->IsHTML(true);
        $mail->Username = $config->getConfigValue("email", "username");
        $mail->Password = $config->getConfigValue("email", "password");
        $mail->SetFrom($config->getConfigValue("email", "username"), "Campus Plate");
        $mail->Subject = "Campus Plate : Account Registered";
        $mail->Body = $emailText;
        $mail->AddAddress($email);

        $mail->Send();
        
        return $error;

    }

}