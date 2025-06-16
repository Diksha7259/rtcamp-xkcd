<?php
// src/functions.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';
 // Load PHPMailer and Dotenv

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function generateVerificationCode(): int {
    return rand(100000, 999999);
}

function registerEmail(string $email): void {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (!in_array($email, $emails)) {
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND);
    }
}

function unsubscribeEmail(string $email): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updatedEmails = array_filter($emails, fn($e) => trim($e) !== trim($email));
    file_put_contents($file, implode(PHP_EOL, $updatedEmails) . PHP_EOL);
}

function sendVerificationEmail(string $email, int $code): bool {
    $subject = 'Your Verification Code';
    $body = "<p>Your verification code is: <strong>$code</strong></p>";
    return sendEmail($email, $subject, $body);
}

function verifyCode(string $email, string $code): bool {
    $file = __DIR__ . '/codes.json';
    if (!file_exists($file)) return false;

    $data = json_decode(file_get_contents($file), true);
    return isset($data[$email]) && $data[$email] === $code;
}

function saveCode(string $email, string $code): void {
    $file = __DIR__ . '/codes.json';
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $data[$email] = $code;
    file_put_contents($file, json_encode($data));
}

function fetchAndFormatXKCDData(): string {
    $latest = json_decode(file_get_contents('https://xkcd.com/info.0.json'), true);
    $max = $latest['num'];
    $random = rand(1, $max);

    $data = json_decode(file_get_contents("https://xkcd.com/$random/info.0.json"), true);
    $img = $data['img'];

    return <<<HTML
        <h2>XKCD Comic</h2>
        <img src="$img" alt="XKCD Comic">
        <p><a href="http://localhost/rtcamp-xkcd/src/unsubscribe.php" id="unsubscribe-button">Unsubscribe</a></p>
    HTML;
}

function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $content = fetchAndFormatXKCDData();

    foreach ($emails as $email) {
        sendEmail($email, 'Your XKCD Comic', $content);
    }
}

function sendEmail(string $to, string $subject, string $body): bool {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($_ENV['MAIL_USERNAME'], 'XKCD Bot');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
