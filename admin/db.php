<?php
// ── Database connection for Admin Panel ──────────────────────
// Uses the SAME school_website database
$conn = mysqli_connect('127.0.0.1', 'root', '', 'school_website',3307);

if (!$conn) {
    die('<div style="font-family:sans-serif;padding:40px;color:red;">
        ❌ Database connection failed: ' . mysqli_connect_error() . '
        <br><br>Make sure MySQL is running and <strong>school_website</strong> database exists.
    </div>');
}
mysqli_set_charset($conn, 'utf8mb4');