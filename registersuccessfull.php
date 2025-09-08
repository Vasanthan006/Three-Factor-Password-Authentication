<?php
// registersuccessfull.php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }
        .container {
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }
        h1 {
            font-size: 48px;
            font-weight: bold;
            color: black;
            margin-bottom: 20px;
        }
        a {
            display: inline-block;
            font-size: 24px;
            color: black;
            text-decoration: none;
            border: 2px solid black;
            border-radius: 10px;
            padding: 10px 20px;
            transition: background 0.3s, color 0.3s;
        }
        a:hover {
            background: black;
            color: white;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registered Successfully!</h1>
        <a href="login.php">Click here to Login</a>
    </div>
</body>
</html>
