<?php
// ── Main Website – Database Connection ────────────────────────
$conn = mysqli_connect('127.0.0.1', 'root', '', 'school_website',3307);

if (!$conn) {
    // Silent fail — fallback data will be used in the main files [cite: 393]
    $conn = null;
} else {
    mysqli_set_charset($conn, 'utf8mb4');
}
?>