<?php
if (!isset($_GET['file'])) {
    die("No file specified!");
}

$path = $_GET['file'];

// Check for [zipfile.zip]/inner_file format
if (!preg_match('/^\[(.+?)\.zip\]\/(.+)$/', $path, $matches)) {
    die("Invalid format! Use [zipfile.zip]/inner_file");
}

$zipFilename = $matches[1] . '.zip';
$fileInsideZip = $matches[2];

// Find ZIP in persistent or temp storage
$zipPathPersistent = "Uploads/" . $zipFilename;
$zipPathTemp = "temp/uploads/" . $zipFilename;

if (file_exists($zipPathPersistent)) {
    $zipPath = $zipPathPersistent;
    $isPersistent = true;
} elseif (file_exists($zipPathTemp)) {
    $zipPath = $zipPathTemp;
    $isPersistent = false;
} else {
    die("ZIP not found!");
}

// Block dangerous extensions in ZIP
$blocked_extensions = [
    'php', 'php3', 'php4', 'php5', 'phtml', 'phps', // PHP variants
    'asp', 'aspx', 'jsp', 'cgi',                    // Server-side scripts
    'exe', 'dll', 'bat', 'sh', 'py', 'pl', 'rb', 'jar', // Executables
    'html', 'htm', 'shtml', 'js', 'jsx', 'vbs'      // Client-side scripts and HTML
];
$extension = strtolower(pathinfo($fileInsideZip, PATHINFO_EXTENSION));
if (in_array($extension, $blocked_extensions)) {
    die("Error: This file type is not allowed in ZIP!");
}

// Extract the file
$tempDir = "temp/extract_" . uniqid();
mkdir($tempDir);

$zip = new ZipArchive();
if ($zip->open($zipPath) === TRUE) {
    $zip->extractTo($tempDir, $fileInsideZip);
    $zip->close();
} else {
    die("Failed to open ZIP!");
}

$extractedFile = $tempDir . '/' . $fileInsideZip;

if (!file_exists($extractedFile)) {
    removeDirectory($tempDir);
    die("File not in ZIP!");
}

// Serve with detected MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $extractedFile) ?? 'application/octet-stream';
finfo_close($finfo);

header("Content-Type: $mime_type");
readfile($extractedFile);

// Clean up
removeDirectory($tempDir);
if (!$isPersistent) {
    unlink($zipPath);
}

function removeDirectory($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                unlink($dir . '/' . $file);
            }
        }
        rmdir($dir);
    }
}
?>
