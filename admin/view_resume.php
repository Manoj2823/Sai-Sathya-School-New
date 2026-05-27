<?php
session_start();
// Protect the page so only admins can view the resumes
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['file'])) {
    die('No file specified.');
}

$file = $_GET['file'];
$filepath = '../' . $file;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Resume – Sai Schools</title>
    <!-- School Logo for the Browser Tab (Favicon) -->
    <link rel="icon" type="image/png" href="../images/academiya_heading_logo.png">
    <style>
        body, html { margin: 0; padding: 0; height: 100%; background: #333; overflow: hidden; }
        iframe { width: 100%; height: 100%; border: none; display: block; }
    </style>
</head>
<body>
    <iframe src="<?= htmlspecialchars($filepath) ?>"></iframe>
</body>
</html>