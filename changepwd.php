<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Unauthorized access! Please log in again.'); window.location.href = 'login.php';</script>";
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $img1 = $_POST['img1'] ?? '';
    $img2 = $_POST['img2'] ?? '';
    $img3 = $_POST['img3'] ?? '';

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location.href = 'changepw.php';</script>";
        exit();
    }

    if (empty($img1) || empty($img2) || empty($img3)) {
        echo "<script>alert('Please select all three images!'); window.location.href = 'changepw.php';</script>";
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password and graphical password in the database
    $stmt = $conn->prepare("UPDATE users SET password = ?, img1 = ?, img2 = ?, img3 = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $hashed_password, $img1, $img2, $img3, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Password changed successfully!'); window.location.href = 'dashboard.php';</script>";
    } else {
        echo "<script>alert('Error updating password.'); window.location.href = 'changepw.php';</script>";
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #4facfe, #00f2fe);
        }
        .container {
            width: 450px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        input, button {
            width: 90%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            border-color: rgb(4, 4, 4);
        }

        input:focus {
            border-color: rgb(4, 250, 86);
            box-shadow: 0 0 8px rgba(0, 198, 255, 0.6);
            outline: none;
        }

        button {
            background: rgb(0, 68, 255);
            color: white;
            border: none;
            cursor: pointer;
            padding: 12px;
            font-size: 16px;
        }
        button:hover {
            background: #3a8ee6;
        }
        .image-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-top: 10px;
        }
        .image-grid img {
            width: 80px;
            height: 80px;
            cursor: pointer;
            border-radius: 10px;
            border: 3px solid transparent;
            transition: transform 0.2s, border 0.2s;
        }
        .image-grid img.selected {
            border-color: rgb(4, 250, 86);
            background-color: rgba(4, 250, 86, 0.2);
            transform: scale(1.1);
        }
        .selected-images {
            display: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Change Password</h2>
    <form method="POST" action="">
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <h3>Select Graphical Password</h3>
        <div class="image-grid">
            <img src="images/img1.jpg" data-filename="img1.jpg" onclick="selectImage(this)">
            <img src="images/img2.jpg" data-filename="img2.jpg" onclick="selectImage(this)">
            <img src="images/img3.jpg" data-filename="img3.jpg" onclick="selectImage(this)">
            <img src="images/img4.jpg" data-filename="img4.jpg" onclick="selectImage(this)">
            <img src="images/img5.jpg" data-filename="img5.jpg" onclick="selectImage(this)">
        </div>

        <input type="hidden" name="img1" id="img1">
        <input type="hidden" name="img2" id="img2">
        <input type="hidden" name="img3" id="img3">

        <button type="submit">Change Password</button>
    </form>
</div>

<script>
    let selectedImages = [];

    function selectImage(img) {
        let filename = img.getAttribute('data-filename');

        if (selectedImages.includes(filename)) {
            selectedImages = selectedImages.filter(i => i !== filename);
            img.classList.remove('selected');
        } else if (selectedImages.length < 3) {
            selectedImages.push(filename);
            img.classList.add('selected');
        }

        // If more than 3 images are selected, remove the first one
        if (selectedImages.length > 3) {
            let removedImage = selectedImages.shift();
            document.querySelector(`[data-filename='${removedImage}']`).classList.remove('selected');
        }

        // Update hidden input fields
        document.getElementById('img1').value = selectedImages[0] || '';
        document.getElementById('img2').value = selectedImages[1] || '';
        document.getElementById('img3').value = selectedImages[2] || '';
    }
</script>

</body>
</html>
