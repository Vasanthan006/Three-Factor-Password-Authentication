<?php
session_start();
include 'db.php';

// Ensure session is started only once
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user_id is available in URL or session
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $user_id = $_SESSION['user_id']; // Get user ID from session
} else {
    $user_id = intval($_GET['user_id']); // Get user ID from URL
    $_SESSION['user_id'] = $user_id; // Store in session for persistence
}

// Fetch graphical password and attempt details
$stmt = $conn->prepare("SELECT img1, img2, img3, gpass_attempts, gpass_lockout_time FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$error_message = ""; // Store error messages to display on the page

// Ensure valid user record is retrieved
if (!$user || !isset($user['img1'], $user['img2'], $user['img3'])) {
    header("Location: register.php");
    exit();
}

// Check if account is locked
if ($user['gpass_attempts'] >= 2 && strtotime($user['gpass_lockout_time']) > time()) {
    $error_message = "⏳ Account locked. Try again after 2 minutes.";
} elseif ($user['gpass_attempts'] >= 3 && strtotime($user['gpass_lockout_time']) <= time()) {
    // Reset lockout if expired
    $reset_stmt = $conn->prepare("UPDATE users SET gpass_attempts = 0, gpass_lockout_time = NULL WHERE id = ?");
    $reset_stmt->bind_param("i", $user_id);
    $reset_stmt->execute();
    $reset_stmt->close();
    $error_message = "✅ Lockout expired. Please enter your graphical password again.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error_message)) {
    // Extract filenames from user selection
    $img1 = $_POST['img1'] ?? '';
    $img2 = $_POST['img2'] ?? '';
    $img3 = $_POST['img3'] ?? '';

    // Compare filenames (case-sensitive match)
    if (trim($img1) === trim($user['img1']) && trim($img2) === trim($user['img2']) && trim($img3) === trim($user['img3'])) {
        // Reset attempts on success
        $reset_stmt = $conn->prepare("UPDATE users SET gpass_attempts = 0, gpass_lockout_time = NULL WHERE id = ?");
        $reset_stmt->bind_param("i", $user_id);
        $reset_stmt->execute();
        $reset_stmt->close();

        header("Location: otp_verification.php");
        exit();
    } else {
        // Increment failed attempts
        $new_attempts = $user['gpass_attempts'] + 1;
        $lock_time = ($new_attempts >= 2) ? date("Y-m-d H:i:s", strtotime("+2 minutes")) : NULL;

        $update_stmt = $conn->prepare("UPDATE users SET gpass_attempts = ?, gpass_lockout_time = ? WHERE id = ?");
        $update_stmt->bind_param("isi", $new_attempts, $lock_time, $user_id);
        $update_stmt->execute();
        $update_stmt->close();

        $error_message = "❌ Incorrect graphical password! Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphical Password Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }
        .container {
            width: 450px;
            padding: 20px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
            position: relative;
        }
        .error-message {
            color: red;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .success-message {
            color: green;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 20px auto;
            max-width: 320px;
        }
        .grid img {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .grid img.selected {
            border-color: rgb(4, 250, 86);
            transform: scale(1.1);
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
            background: rgb(0, 50, 200);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    <script>
        let selectedImages = [];

        function selectImage(img) {
            let filename = img.dataset.filename; // Use data attribute to get filename
            if (selectedImages.includes(filename)) {
                selectedImages = selectedImages.filter(i => i !== filename);
                img.classList.remove('selected');
            } else if (selectedImages.length < 3) {
                selectedImages.push(filename);
                img.classList.add('selected');
            }
            document.getElementById('img1').value = selectedImages[0] || '';
            document.getElementById('img2').value = selectedImages[1] || '';
            document.getElementById('img3').value = selectedImages[2] || '';
        }
    </script>
</head>
<body>
    <div class="container">
        <!-- Display error or success messages -->
        <?php if (!empty($error_message)): ?>
            <div class="<?= strpos($error_message, '✅') !== false ? 'success-message' : 'error-message' ?>">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <h2>Enter Your Graphical Password</h2>
        <form method="POST">
            <div class="grid">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <img src="images/img<?php echo $i; ?>.jpg" data-filename="img<?php echo $i; ?>.jpg" onclick="selectImage(this)">
                <?php endfor; ?>
            </div>
            <input type="hidden" name="img1" id="img1">
            <input type="hidden" name="img2" id="img2">
            <input type="hidden" name="img3" id="img3">
            <button type="submit">Login</button>
        </form>
        <a href="forgotpw.php">Forgot password ? click here</a>
    </div>
</body>
</html>