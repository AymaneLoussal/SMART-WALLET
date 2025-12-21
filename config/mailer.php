<?php
use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/../mailer/src/PHPMailer.php';
require __DIR__ . '/../mailer/src/SMTP.php';
require __DIR__ . '/../mailer/src/Exception.php';

function sendOTP($toEmail, $otp) {

    $mail = new PHPMailer();
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aymanloussal552@gmail.com';
        $mail->Password = 'yijbfnjkptwusshx';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';

        $mail->setFrom('aymanloussal552@gmail.com', 'FinanceApp');
        $mail->addAddress($toEmail);

        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Your OTP code is: $otp";

        $mail->send();

            return true;

    } catch (Exception $e) {
        return false;
    }
}
?>