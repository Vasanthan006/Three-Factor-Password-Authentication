<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Unauthorized access! Please log in again.'); window.location.href = 'login.php';</script>";
    exit();
}

include 'db.php';

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// If no user found, redirect
if (!$user) {
    echo "<script>alert('User not found!'); window.location.href = 'login.php';</script>";
    exit();
}

$name = htmlspecialchars($user['name']);
$email = htmlspecialchars($user['email']);
$profile_pic = $user['profile_pic'] ?? 'images/default-avatar.png';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        :root {
            --bg-color: #f0f0f0;
            --card-bg: #ffffff;
            --text-color: #333;
            --accent: #4facfe;
        }

        body.dark-mode {
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-color: #fff;
            --accent: #00c6ff;
        }

        body {
            font-family: Arial, sans-serif;
            background: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
            transition: background 0.5s, color 0.5s;
        }

        .dashboard {
            width: 400px;
            padding: 20px;
            background: var(--card-bg);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            transition: background 0.5s;
            animation: fadeIn 1s ease-in-out;
        }

        .buttons {
            margin-top: 20px;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 5px;
            font-size: 16px;
            transition: background 0.3s, transform 0.2s;
        }

        .change-pw-btn {
            background:rgb(0, 68, 255);
            color: white;
        }

        .change-pw-btn1 {
            background:rgb(255, 0, 0);
            color: white;
        }

        .change-pw-btn:hover {
            background:rgb(0, 68, 255);
        }

        button:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <img src="<?php echo $profile_pic; ?>" class="profile-img" alt="Profile Picture">
        <h2>Welcome, <?php echo $name; ?> ! 👋</h2>
        <p>Email: <?php echo $email; ?></p>

        <div class="buttons">
            <button class="change-pw-btn" onclick="location.href='changepwd.php'">Change Password</button>
            <button class="change-pw-btn1" onclick="location.href='logout.php'">Logout</button>
        </div>
    </div>
</body>
</html>
