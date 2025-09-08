<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            text-align: center;
            margin: 0;
            padding: 0;
        }
        nav {
            background: #333;
            padding: 15px;
        }
        nav a {
            color: #fff;
            margin: 0 15px;
            text-decoration: none;
            font-size: 18px;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #4facfe;
        }
        .container {
            margin-top: 50px;
            color: #fff;
        }
        h1 {
            font-size: 48px;
            margin-bottom: 20px;
            animation: fadeIn 1s ease-in-out;
        }
        p {
            font-size: 20px;
            animation: fadeIn 1.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
        <a href="about.php">About Us</a>
    </nav>

    <div class="container">
        <h1>Welcome to Our Website</h1>
        <p>Your secure and reliable Three-Factor authentication.</p>
    </div>
</body>
</html>

