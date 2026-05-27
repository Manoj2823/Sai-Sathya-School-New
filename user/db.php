<?php
// ── Database connection for User Panel ──────────────────────
// Same school_website database — one level deeper so path is same
$conn = mysqli_connect('127.0.0.1', 'root', '', 'school_website', 3307);

if (!$conn) {
    die('<div style="font-family:sans-serif;padding:40px;color:red;">
        ❌ Database connection failed: ' . mysqli_connect_error() . '
        <br><br>Make sure MySQL is running and <strong>school_website</strong> database exists.
    </div>');
}
mysqli_set_charset($conn, 'utf8mb4');