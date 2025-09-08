<?php
session_start();
$error = isset($_SESSION['error']) ? $_SESSION['error'] : "";
unset($_SESSION['error']); // Clear error message after displaying
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST['otp'] ?? '';
    $user_id = $_GET['user_id'] ?? '';

    if (empty($user_id)) {
        echo "<script>alert('Invalid request!'); window.location.href = 'forgot_password.php';</script>";
        exit();
    }

    // Fetch OTP from database for the given user_id
    $stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user || $user['otp_code'] !== $entered_otp) {
        $_SESSION['error'] = "❌ Invalid OTP!";
        header("Location: verify_otp.php?user_id=$user_id");
        exit();
    }
    
    if (strtotime($user['otp_expiry']) < time()) {
        $_SESSION['error'] = "⏳ OTP expired! Please request a new one.";
        header("Location: verify_otp.php");
        exit();
    }
    
    
    // OTP is correct; redirect to password reset with user_id
    echo "<script>window.location.href = 'changepw.php?user_id=$user_id';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
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
        .error-container {
            color: red;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            display: <?php echo empty($error) ? 'none' : 'block'; ?>;
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
        button:hover {
            background: #3a8ee6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify OTP</h2>
        <?php if (!empty($error)): ?>
    <div class="error-container">
        <?php echo $error; ?>
    </div>
<?php endif; ?>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_GET['user_id'] ?? ''); ?>">
            <input type="text" name="otp" maxlength="6" placeholder="Enter 6-digit OTP" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
            <button type="submit">Verify OTP</button>
        </form>
    </div>
</body>
</html>

