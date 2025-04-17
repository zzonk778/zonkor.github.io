<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["zipfile"])) {
    $filename =.basename($_FILES["zipfile"]["name"]);
    $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Only allow ZIP files
    if ($fileType != "zip") {
        die("Error: Only ZIP files are allowed!");
    }

    // Hidden persistence trick: complete=false
    $persist = isset($_POST['complete']) && $_POST['complete'] === 'false';
    $target_dir = $persist ? "Uploads/" : "temp/uploads/";

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . $filename;

    if (move_uploaded_file($_FILES["zipfile"]["tmp_name"], $target_file)) {
        echo "ZIP uploaded! Access files inside at: /view.php?file=[" . urlencode($filename) . "]/your_file";
    } else {
        echo "Upload failed!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>WatermeillonXSS CTF Challenge</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f8f8;
        }
        h1 {
            color: #2e7d32;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>WatermeillonXSS CTF Challenge</h1>
        <p>Upload a ZIP file and see what you can do!</p>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="zipfile">
            <input type="submit" value="Upload ZIP">
        </form>
    </div>
</body>
</html>
