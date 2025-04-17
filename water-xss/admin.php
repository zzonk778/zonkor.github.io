<?php
session_start();

// Set admin cookie and session
if (isset($_GET['session']) && $_GET['session'] === 'admin123') {
    $_SESSION['is_admin'] = true;
    setcookie('admin_token', 'super_secret_admin_123', time() + 3600, '/');
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access denied! Admins only.");
}

echo "<h1>Admin Panel</h1>";

?>
