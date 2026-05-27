<?php
/**
 * auth_guard.php
 * Include this at the top of every user panel page.
 * - If not logged in → redirect to admin/login.php
 * - If admin role → redirect to admin/index.php  (admin cannot use user panel)
 * - If user role  → allow access
 */
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../admin/login.php');
    exit;
}

if (($_SESSION['admin_role'] ?? 'user') === 'admin') {
    header('Location: ../admin/index.php');
    exit;
}