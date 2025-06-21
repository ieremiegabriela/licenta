<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require("{$_SERVER['DOCUMENT_ROOT']}vendor/autoload.php");

// BEGIN - INITIAL SECURITY SCREEN ------------------

if (!defined('helper_functions.php')):

    die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endif;

// END - INITIAL SECURITY SCREEN --------------------


// BEGIN - ADDITIONAL SESSION CONFIG ----------------

$_SESSION['LOCATION_ORIGIN'] = getLocationOrigin();
$_SESSION['DOCKER_ORIGIN'] = getDockerOrigin();

// END - ADDITIONAL SESSION CONFIG ------------------


function getLocationOrigin() {

    return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]";
}

function getDockerOrigin() {
    // Use in internal CURL requests
    return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://host.docker.internal";
}

function createAuthToken() {

    return sha1(md5(mt_rand(0, mt_getrandmax())));
}

function sendEmail(
    $recipient,
    $emailSubject,
    $htmlBodyPath,
    $replacements = []
) {
    $mailObj = new PHPMailer(true);

    try {
        switch (true):
            case (!$recipient):
            case (!$emailSubject):
            case (!$htmlBodyPath):
                throw new Exception("Missing mandatory parameter/s");
                break;
            case (!$mailObj instanceof PHPMailer):
                throw new Exception("Invalid argument type | instanceof PHPMailer required");
                break;
        endswitch;

        $mailObj->CharSet = "UTF-8";
        // Send using SMTP
        $mailObj->isSMTP();
        // Set the SMTP server to send through
        $mailObj->Host = 'smtp.mailersend.net';
        // Enable SMTP authentication
        $mailObj->SMTPAuth = true;
        // SMTP username
        $mailObj->Username = 'MS_f6H2gB@test-69oxl5evj0kl785k.mlsender.net';
        // SMTP password
        $mailObj->Password = "mssp.zVelb4I.z86org8zvxegew13.Q7I5crc";
        // Enable implicit TLS encryption
        $mailObj->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailObj->Port = 587;

        // Recipients
        $mailObj->setFrom('MS_f6H2gB@test-69oxl5evj0kl785k.mlsender.net', 'Marked as Safe');
        // Add a recipient
        $mailObj->addAddress($recipient);
        $mailObj->addReplyTo('MS_f6H2gB@test-69oxl5evj0kl785k.mlsender.net', 'Marked as Safe');

        // Content
        // Set email format to HTML
        $mailObj->isHTML(true);
        $mailObj->Subject = $emailSubject;
        $mailObj->Body = file_get_contents($htmlBodyPath);

        foreach ($replacements as $key => $value):
            $mailObj->Body = str_replace($key, $value, $mailObj->Body);
        endforeach;

        $mailObj->send();

        $output = [
            'success' => 1,
            'message' => 'Success!',
            'data' => null
        ];
    } catch (Exception $e) {

        $message = $e->getMessage() ? $e->getMessage() : $mailObj->ErrorInfo;

        $output = [
            'success' => 0,
            'message' => "Message could not be sent. Mailer Error: $message",
            'data' => null
        ];
    }

    return $output;
}
