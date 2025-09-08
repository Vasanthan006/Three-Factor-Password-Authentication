<?php
session_start();
include 'db.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';

    // Fetch user's ID and email
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo "<script>alert('User not found!'); window.location.href = 'forgotpw.php';</script>";
        exit();
    }

    $user_id = $user['id'];
    $email = $user['email'];
    $otp = rand(100000, 999999); // Generate 6-digit OTP
    $expiry = date("Y-m-d H:i:s", time() + 300); // OTP valid for 5 minutes

    // Store OTP in the database
    $stmt = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE id = ?");
    $stmt->bind_param("ssi", $otp, $expiry, $user_id);
    $stmt->execute();
    $stmt->close();

    // Send OTP email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'prakatheeshsubbaya@gmail.com';
        $mail->Password = 'esta bakx avrj bwjb';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your-email@gmail.com', 'Password Reset');
        $mail->addAddress($email);
        $mail->Subject = "Your Password Reset OTP";
        $mail->isHTML(true);
        $mail->Body = "<p>Your OTP for password reset is: <strong>$otp</strong>. This OTP is valid for 5 minutes.</p>";
        $mail->send();

        // Redirect to verify OTP page with user_id
        echo "<script>alert('OTP sent to your email!'); window.location.href = 'verify_otp.php?user_id=$user_id';</script>";
        exit();
    } catch (Exception $e) {
        echo "<script>alert('Failed to send OTP: {$mail->ErrorInfo}');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            width: 350px;
            padding: 20px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        input {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 18px;
            text-align: center;
        }
        button {
            padding: 10px 20px;
            background: rgb(0, 68, 255);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }

        input:focus {
            border-color:rgb(4, 250, 86);
            box-shadow: 0 0 8px rgba(0, 198, 255, 0.6);
            outline: none;
        }

        button:hover {
            background: #3a8ee6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Enter your username" required>
            <button type="submit">Send Reset OTP</button>
        </form>
    </div>
</body>
</html>
