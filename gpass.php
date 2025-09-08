<?php
session_start();
include 'db.php';

// Ensure user just registered before setting graphical password
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo "<script>alert('Unauthorized Access! Please Register First.'); window.location.href = 'register.php';</script>";
    exit();
}
$user_id = $_GET['user_id']; // Get the user ID from the URL

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Extract filenames from user selection
    $img1 = basename($_POST['img1']); 
    $img2 = basename($_POST['img2']); 
    $img3 = basename($_POST['img3']);

    if (empty($img1) || empty($img2) || empty($img3)) {
        echo "<script>alert('Please select exactly three images.');</script>";
    } else {
        $stmt = $conn->prepare("UPDATE users SET img1 = ?, img2 = ?, img3 = ? WHERE id = ?");
        $stmt->bind_param("sssi", $img1, $img2, $img3, $user_id);
        
        if ($stmt->execute()) {
            echo "<script>window.location.href = 'registersuccessfull.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error saving graphical password. Please try again.');</script>";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Graphical Password</title>
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
            font-size: 28px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 20px auto;
            max-width: 350px;
        }
        .grid img {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            border: 3px solid transparent;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .grid img.selected {
            border-color: rgb(4, 250, 86);
            transform: scale(1.1);
        }
        .selected-images {
            margin-top: 15px;
            font-size: 18px;
            color: rgb(0, 0, 0);
        }
        button {
            padding: 10px 20px;
            background:rgb(0, 68, 255);
            color: #fff;
            border: rgb(14, 146, 235);
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 16px;
            margin-top: 15px;
        }
        button:hover {
            background:rgb(0, 68, 255);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    <script>
        let selectedImages = [];

        function selectImage(img) {
            let filename = img.dataset.filename; // Get filename from data attribute

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

            document.getElementById('selected-password').innerText = selectedImages.length > 0 
                ? 'Your Selected Graphical Password: ' + selectedImages.join (' : ') 
                : 'No images selected';
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Set Your Graphical Password</h2>
        <form method="POST">
            <div class="grid">
                <img src="images/img1.jpg" data-filename="img1.jpg" onclick="selectImage(this)">
                <img src="images/img2.jpg" data-filename="img2.jpg" onclick="selectImage(this)">
                <img src="images/img3.jpg" data-filename="img3.jpg" onclick="selectImage(this)">
                <img src="images/img4.jpg" data-filename="img4.jpg" onclick="selectImage(this)">
                <img src="images/img5.jpg" data-filename="img5.jpg" onclick="selectImage(this)">
            </div>
            <p class="selected-images" id="selected-password">No images selected</p>
            <input type="hidden" name="img1" id="img1">
            <input type="hidden" name="img2" id="img2">
            <input type="hidden" name="img3" id="img3">
            <button type="submit">Set Graphical Password</button>
        </form>
    </div>
</body>
</html>
