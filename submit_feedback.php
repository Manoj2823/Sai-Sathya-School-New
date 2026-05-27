<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create table if it doesn't exist
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        role VARCHAR(150),
        content TEXT NOT NULL,
        avatar_path VARCHAR(300) DEFAULT NULL,
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $name = mysqli_real_escape_string($conn, trim($_POST['fb_name'] ?? ''));
    $role = mysqli_real_escape_string($conn, trim($_POST['fb_role'] ?? ''));
    $content = mysqli_real_escape_string($conn, trim($_POST['fb_content'] ?? ''));
    
    if ($name && $content) {
        $query = "INSERT INTO testimonials (name, role, content, is_active) VALUES ('$name', '$role', '$content', 1)";
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Thank you! Your feedback has been added.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Please provide name and feedback.']);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request.']);