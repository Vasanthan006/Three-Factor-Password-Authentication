<?php
session_start();
include 'db.php';

// Load PHPMailer correctly from the src directory
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Unauthorized access! Please log in again.'); window.location.href = 'login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    echo "<script>alert('User not found!'); window.location.href = 'login.php';</script>";
    exit();
}

$email = $user['email'];

// Generate and send OTP if not set
if (!isset($_SESSION['otp']) || time() > $_SESSION['otp_expiry']) {
    $_SESSION['otp'] = rand(100000, 999999);
    $_SESSION['otp_expiry'] = time() + (3 * 60); // Valid for 3 minutes

    // Send OTP via Gmail SMTP
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'prakatheeshsubbaya@gmail.com'; // Your Gmail
        $mail->Password = 'esta bakx avrj bwjb'; // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('prakatheeshsubbaya@gmail.com', 'Your Verification Code');
        $mail->addAddress($email);
        $mail->Subject = "Verification Code";
        $mail->isHTML(true);  // Set the email format to HTML
        $mail->Body = "Your OTP is: <b>" . $_SESSION['otp'] . "</b>. This OTP is valid only for <b>3 minutes</b>.";
        $mail->send();
    } catch (Exception $e) {
        echo "<script>alert('Failed to send OTP: {$mail->ErrorInfo}'); window.location.href = 'gpass_login.php';</script>";
        exit();
    }
}

// OTP Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'] ?? '';

    if ($entered_otp == $_SESSION['otp']) {
        unset($_SESSION['otp']); // OTP used, remove it
        echo "<script>window.location.href = 'dashboard.php';</script>";
        exit();
    } else {
        $error = "❌ Invalid OTP code!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
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
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #ccc;
            border-radius: 5px;
            font-size: 18px;
            text-align: center;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus {
            border-color: rgb(4, 250, 86);
            box-shadow: 0 0 8px rgba(0, 198, 255, 0.6);
            outline: none;
        }
        .error {
            color: red;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            font-size: 18px;
            margin-bottom: 10px;
        }
        input.correct {
            border-color: green;
        }
        button {
            padding: 10px 20px;
            background:rgb(0, 68, 255);
            border-color: rgb(4, 250, 86);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background:rgb(0, 68, 255);
        }
    </style>
    <script>
        function validateOTP() {
            let inputField = document.getElementById('otp');
            let correctOTP = "<?php echo $_SESSION['otp']; ?>";

            if (inputField.value === correctOTP) {
                inputField.classList.add('correct');
            } else {
                inputField.classList.remove('correct');
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Enter OTP</h2>
        <?php if (!empty($error)) {
            echo '<div class="' . (strpos($error, "✅") !== false ? "success" : "error") . '">' . $error . '</div>';
        } ?>
     <form method="POST">
    <input type="text" name="otp" id="otp" maxlength="6" placeholder="Enter 6-digit OTP" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    <button type="submit">Verify</button>
</form>
    </div>
</body>
</html>
