<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "⚠️ Both fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, login_attempts, lockout_time FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $db_username, $hashed_password, $login_attempts, $lockout_time);
            $stmt->fetch();

            if ($login_attempts >= 2 && strtotime($lockout_time) > time()) {
                $error = "⏳ Account locked. Try again after 2 minutes.";
            } else {
                if ($login_attempts >= 2 && strtotime($lockout_time) <= time()) {
                    $reset_stmt = $conn->prepare("UPDATE users SET login_attempts = 0, lockout_time = NULL WHERE username = ?");
                    $reset_stmt->bind_param("s", $username);
                    $reset_stmt->execute();
                    $reset_stmt->close();
                    $login_attempts = 0;
                    $error = "✅ Account unlocked! Please try logging in again.";
                }

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $db_username;

                    $reset_stmt = $conn->prepare("UPDATE users SET login_attempts = 0, lockout_time = NULL WHERE username = ?");
                    $reset_stmt->bind_param("s", $username);
                    $reset_stmt->execute();
                    $reset_stmt->close();

                    header("Location: gpass_login.php?user_id=" . $_SESSION['user_id']);
                    exit();
                } else {
                    $new_attempts = $login_attempts + 1;
                    $lock_time = ($new_attempts >= 2) ? date("Y-m-d H:i:s", strtotime("+2 minutes")) : NULL;

                    $update_stmt = $conn->prepare("UPDATE users SET login_attempts = ?, lockout_time = ? WHERE username = ?");
                    $update_stmt->bind_param("iss", $new_attempts, $lock_time, $username);
                    $update_stmt->execute();
                    $update_stmt->close();

                    $error = "❌ Invalid Username or Password!";
                }
            }
        } else {
            $error = "❌ Invalid Username or Password!";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            width: 400px;
            padding: 20px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }
        h2 {
            color: #333;
            font-size: 32px;
            margin-bottom: 20px;
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
        input {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #4facfe;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus {
            border-color: rgb(4, 250, 86);
            box-shadow: 0 0 8px rgba(0, 198, 255, 0.6);
            outline: none;
        }
        button {
            width: 90%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: rgb(0, 68, 255);
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: rgb(0, 50, 200);
        }
        a {
            color: #4facfe;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            font-size: 16px;
            transition: color 0.3s;
        }
        a:hover {
            color: #00c6ff;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($error)) {
            echo '<div class="' . (strpos($error, "✅") !== false ? "success" : "error") . '">' . $error . '</div>';
        } ?>
        <form action="" method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" name="login">Login</button>
        </form>
        <a href="forgotpw.php">Forgot password? Click here</a>
    </div>
</body>
</html>
