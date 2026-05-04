<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';

// MAGIC BULLET: Changed $attachment_path to $attachment_string
function sendBloodCareEmail($to_email, $to_name, $subject, $html_body, $attachment_string = null, $attachment_name = '') {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bloodcaredonation@gmail.com'; // CHECK THIS IS YOUR REAL GMAIL
        $mail->Password   = 'jjnd kinf lego eeez';       // CHECK THIS IS YOUR APP PASSWORD
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('your.email@gmail.com', 'BloodCare System');
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->AltBody = strip_tags($html_body); 

        // MAGIC BULLET: Attach directly from memory! No files saved to the hard drive!
        if ($attachment_string !== null) {
            $mail->addStringAttachment($attachment_string, $attachment_name, 'base64', 'application/pdf');
        }

        $mail->send();
        return true; 
        
    } catch (Exception $e) {
        // If it fails, write the exact error to a text file so we can see it!
        file_put_contents(__DIR__ . '/mail_error_log.txt', date('[Y-m-d H:i:s] ') . $mail->ErrorInfo . PHP_EOL, FILE_APPEND);
        return false; 
    }
}
?>