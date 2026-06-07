<?php
function smtp_read_response($socket) {
    $response = '';

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    return $response;
}

function smtp_send_command($socket, $command, $expected_codes) {
    fwrite($socket, $command . "\r\n");
    $response = smtp_read_response($socket);
    $code = (int)substr($response, 0, 3);

    return in_array($code, (array)$expected_codes, true);
}

function smtp_format_address($email, $name = '') {
    $email = trim((string)$email);
    $name = trim((string)$name);

    if ($name === '') {
        return $email;
    }

    return '"' . str_replace('"', '\"', $name) . '" <' . $email . '>';
}

function smtp_send_mail($to_email, $subject, $body, $config) {
    $host = $config['smtp_host'] ?? '';
    $port = (int)($config['smtp_port'] ?? 587);
    $username = $config['smtp_username'] ?? '';
    $password = $config['smtp_password'] ?? '';
    $from_email = $config['from_email'] ?? $username;
    $from_name = $config['from_name'] ?? 'SYS Property Holdings';

    if ($host === '' || $username === '' || $password === '' || $password === 'YOUR_GOOGLE_APP_PASSWORD') {
        return false;
    }

    $socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 20);
    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 20);

    if ((int)substr(smtp_read_response($socket), 0, 3) !== 220) {
        fclose($socket);
        return false;
    }

    $server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';
    if (!smtp_send_command($socket, "EHLO {$server_name}", 250)) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_command($socket, 'STARTTLS', 220)) {
        fclose($socket);
        return false;
    }

    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_command($socket, "EHLO {$server_name}", 250)) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_command($socket, 'AUTH LOGIN', 334)
        || !smtp_send_command($socket, base64_encode($username), 334)
        || !smtp_send_command($socket, base64_encode($password), 235)) {
        fclose($socket);
        return false;
    }

    if (!smtp_send_command($socket, 'MAIL FROM:<' . $from_email . '>', 250)
        || !smtp_send_command($socket, 'RCPT TO:<' . $to_email . '>', [250, 251])
        || !smtp_send_command($socket, 'DATA', 354)) {
        fclose($socket);
        return false;
    }

    $headers = [
        'From: ' . smtp_format_address($from_email, $from_name),
        'To: ' . smtp_format_address($to_email),
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $message = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n", "\r\n", str_replace("\r\n", "\n", $body));
    $message = str_replace("\r\n.", "\r\n..", $message);
    fwrite($socket, $message . "\r\n.\r\n");

    $sent = ((int)substr(smtp_read_response($socket), 0, 3)) === 250;
    smtp_send_command($socket, 'QUIT', 221);
    fclose($socket);

    return $sent;
}

function send_registration_otp($to_email, $full_name, $otp) {
    $config_path = __DIR__ . '/mail_config.php';
    if (!file_exists($config_path)) {
        return false;
    }

    $config = require $config_path;
    $subject = 'SYS Property Holdings Email Verification OTP';
    $safe_name = trim($full_name) !== '' ? trim($full_name) : 'Customer';
    $message = "Hi {$safe_name},\n\n";
    $message .= "Your SYS Property Holdings registration verification code is: {$otp}\n\n";
    $message .= "This code will expire in 10 minutes. If you did not request this registration, please ignore this email.\n\n";
    $message .= "Regards,\nSYS Property Holdings";

    return smtp_send_mail($to_email, $subject, $message, $config);
}
?>
