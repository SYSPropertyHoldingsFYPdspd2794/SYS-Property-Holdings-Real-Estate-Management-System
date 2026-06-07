<?php
function send_registration_otp($to_email, $full_name, $otp) {
    $subject = 'SYS Property Holdings Email Verification OTP';
    $safe_name = trim($full_name) !== '' ? trim($full_name) : 'Customer';
    $message = "Hi {$safe_name},\n\n";
    $message .= "Your SYS Property Holdings registration verification code is: {$otp}\n\n";
    $message .= "This code will expire in 10 minutes. If you did not request this registration, please ignore this email.\n\n";
    $message .= "Regards,\nSYS Property Holdings";

    $headers = [
        'From: SYS Property Holdings <no-reply@sysproperty.local>',
        'Reply-To: no-reply@sysproperty.local',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    return mail($to_email, $subject, $message, implode("\r\n", $headers));
}
?>
