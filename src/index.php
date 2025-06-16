<?php
require 'functions.php';

$email = '';
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_email'])) {
        $email = trim($_POST['email']);
        $code = generateVerificationCode();
        saveCode($email, $code);
        if (sendVerificationEmail($email, $code)) {
            $successMessage = "Verification code sent to $email";
        } else {
            $errorMessage = "Failed to send verification email.";
        }
    }

    if (isset($_POST['submit_verification'])) {
        $email = trim($_POST['email']);
        $code = trim($_POST['verification_code']);
        if ($email && verifyCode($email, $code)) {
            registerEmail($email);
            $successMessage = "Email verified and registered!";
        } else {
            $errorMessage = "Invalid verification code or email.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>XKCD Signup</title>
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
            background-color: #007BFF;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #0056b3;
        }
        p {
            font-size: 14px;
            margin: 10px 0;
            text-align: center;
        }
        p[style*="green"] {
            color: green;
        }
        p[style*="red"] {
            color: red;
        }
    </style>
</head>
<body>
    <h2>Subscribe to XKCD Comics</h2>

    <?php if ($successMessage): ?>
        <p style="color: green;"><?= $successMessage ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p style="color: red;"><?= $errorMessage ?></p>
    <?php endif; ?>

    <!-- Email Submission Form -->
    <form method="post">
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        <button type="submit" name="submit_email">Send Verification Code</button>
    </form>

    <!-- Code Verification Form -->
    <form method="post">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="text" name="verification_code" maxlength="6" required placeholder="Enter verification code">
        <button type="submit" name="submit_verification">Verify and Subscribe</button>
    </form>
</body>
</html>
