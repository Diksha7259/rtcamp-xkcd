<?php
require 'functions.php';

$email = '';
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_unsubscribe'])) {
        $email = trim($_POST['unsubscribe_email']);
        $code = generateVerificationCode();
        saveCode($email, $code);
        $subject = 'Confirm Unsubscription';
        $body = "<p>To confirm unsubscription, use this code: <strong>$code</strong></p>";

        if (sendEmail($email, $subject, $body)) {
            $successMessage = "Confirmation code sent to $email";
        } else {
            $errorMessage = "Failed to send confirmation email.";
        }
    }

    if (isset($_POST['submit_verification'])) {
        $email = trim($_POST['unsubscribe_email']);
        $code = trim($_POST['verification_code']);
        if ($email && verifyCode($email, $code)) {
            unsubscribeEmail($email);
            $successMessage = "Successfully unsubscribed.";
        } else {
            $errorMessage = "Invalid code or email.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f6fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 50px;
        }
        h2 {
            color: #333;
        }
        form {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 15px;
            width: 300px;
        }
        input[type="email"],
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #dc3545;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #c82333;
        }
        p {
            font-size: 14px;
            margin: 10px 0;
            text-align: center;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h2>Unsubscribe from XKCD Comics</h2>

    <?php if ($successMessage): ?>
        <p class="success"><?= $successMessage ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p class="error"><?= $errorMessage ?></p>
    <?php endif; ?>

    <!-- Email Form -->
    <form method="post">
        <label for="unsubscribe_email">Email Address</label>
        <input type="email" name="unsubscribe_email" value="<?= htmlspecialchars($email) ?>" required>
        <button type="submit" name="submit_unsubscribe">Send Unsubscribe Code</button>
    </form>

    <!-- Verification Code Form -->
    <form method="post">
        <input type="hidden" name="unsubscribe_email" value="<?= htmlspecialchars($email) ?>">
        <label for="verification_code">Verification Code</label>
        <input type="text" name="verification_code" maxlength="6" required placeholder="Enter verification code">
        <button type="submit" name="submit_verification">Verify and Unsubscribe</button>
    </form>
</body>
</html>
