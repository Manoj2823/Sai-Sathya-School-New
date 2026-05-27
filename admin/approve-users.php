<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
if (($_SESSION['admin_role'] ?? 'user') !== 'admin') {
    header('Location: user%20dashboard.php');
    exit;
}

require_once 'db.php';

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    google_id VARCHAR(100) DEFAULT NULL,
    profile_pic VARCHAR(500) DEFAULT NULL,
    auth_type ENUM('manual','google') NOT NULL DEFAULT 'manual',
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    password VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$status = $_GET['status'] ?? 'all';
if (!in_array($status, ['all', 'pending', 'approved', 'rejected'], true)) {
    $status = 'all';
}

$q = trim($_GET['q'] ?? '');
$msg = '';
$msg_type = '';

$current_user = $_SESSION['admin_username'] ?? '';
$current_email = $_SESSION['admin_email'] ?? '';

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];
    $target = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM admin_users WHERE id=$id"));

    if (!$target) {
        $msg = 'User not found.';
        $msg_type = 'error';
    } elseif (
        $action !== 'delete' &&
        (($target['username'] === $current_user) || ($target['email'] && $target['email'] === $current_email))
    ) {
        $msg = 'You cannot change the status of your own account.';
        $msg_type = 'error';
    } elseif ($action === 'approve') {
        mysqli_query($conn, "UPDATE admin_users SET status='approved' WHERE id=$id");
        header('Location: approve-users.php?status=' . urlencode($status) . '&msg=approved');
        exit;
    } elseif ($action === 'reject') {
        mysqli_query($conn, "UPDATE admin_users SET status='rejected' WHERE id=$id");
        header('Location: approve-users.php?status=' . urlencode($status) . '&msg=rejected');
        exit;
    } elseif ($action === 'delete') {
        if (($target['username'] === $current_user) || ($target['email'] && $target['email'] === $current_email)) {
            $msg = 'You cannot delete your own account.';
            $msg_type = 'error';
        } else {
            mysqli_query($conn, "DELETE FROM admin_users WHERE id=$id");
            header('Location: approve-users.php?status=' . urlencode($status) . '&msg=deleted');
            exit;
        }
    }
}

$messages = [
    'approved' => 'User approved successfully. They can now log in.',
    'rejected' => 'User has been rejected.',
    'deleted' => 'User account deleted.',
];
if (isset($_GET['msg']) && isset($messages[$_GET['msg']])) {
    $msg = $messages[$_GET['msg']];
    $msg_type = 'success';
}

$where = ['1=1'];
if ($status !== 'all') {
    $where[] = "status='" . mysqli_real_escape_string($conn, $status) . "'";
}
if ($q !== '') {
    $esc = mysqli_real_escape_string($conn, $q);
    $where[] = "(username LIKE '%$esc%' OR email LIKE '%$esc%')";
}
$where_sql = implode(' AND ', $where);

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$total_rows = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM admin_users WHERE $where_sql"))[0];
$total_pages = ceil($total_rows / $limit);

$rows = [];
$res = mysqli_query($conn, "SELECT * FROM admin_users WHERE $where_sql ORDER BY
    FIELD(status, 'pending', 'approved', 'rejected'), created_at DESC LIMIT $limit OFFSET $offset");
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
}

$pending_count = (int) (mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM admin_users WHERE status='pending'"))[0] ?? 0);
$approved_count = (int) (mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM admin_users WHERE status='approved'"))[0] ?? 0);
$rejected_count = (int) (mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM admin_users WHERE status='rejected'"))[0] ?? 0);

$filter_query = function ($extra = []) use ($status, $q) {
    return http_build_query(array_filter(array_merge([
        'status' => $status !== 'all' ? $status : null,
        'q' => $q !== '' ? $q : null,
    ], $extra)));
};

// Fetch logged in user details for profile slide-in
$session_user = mysqli_real_escape_string($conn, $_SESSION['admin_username'] ?? 'Admin');
$user_query = @mysqli_query($conn, "SELECT * FROM admin_users WHERE username = '$session_user' LIMIT 1");
if ($user_query && mysqli_num_rows($user_query) > 0) {
    $user_data = mysqli_fetch_assoc($user_query);
} else {
    $user_data = [
        'username' => $_SESSION['admin_username'] ?? 'Admin',
        'email' => $_SESSION['admin_email'] ?? 'No Email',
        'profile_pic' => $_SESSION['admin_pic'] ?? '',
        'auth_type' => $_SESSION['auth_type'] ?? 'manual',
        'status' => 'approved',
        'role' => $_SESSION['admin_role'] ?? 'admin'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>User Approvals – Admin</title>
    <link rel="icon" type="image/png" href="../images/academiya_heading_logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Playfair+Display:wght@600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --navy: #0a1f44;
            --deep: #071530;
            --gold: #c8932a;
            --gold-lt: #f0c060;
            --sidebar-w: 240px;
        }

        .pagination {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .page-btn {
            padding: 6px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            text-decoration: none;
            color: var(--navy);
            font-size: 0.85rem;
            font-weight: 700;
            transition: all 0.2s;
            background: #fff;
        }

        .page-btn:hover,
        .page-btn.active {
            background: var(--navy);
            color: #fff;
            border-color: var(--navy);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Lato', sans-serif;
            background: #f1f5f9;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--deep) 0%, #0d2960 100%);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 24px 20px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand .logo-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--gold), var(--gold-lt));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--deep);
            margin-bottom: 8px;
        }

        .sidebar-brand h2 {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 1.15rem;
        }

        .sidebar-brand p {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.45);
            margin-top: 3px;
            text-transform: uppercase;
        }

        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .nav-label {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.35);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 8px 8px 4px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s;
            margin-bottom: 2px;
            cursor: pointer;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(200, 147, 42, 0.18);
            color: #fff;
        }

        .nav-item.active {
            border-left: 3px solid var(--gold);
            font-weight: 700;
        }

        .nav-item .icon {
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 8px;
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
            width: 100%;
            border: none;
            cursor: pointer;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.3);
            color: #fff;
        }

        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            min-width: 0;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 28px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar h1 {
            font-size: 1.15rem;
            color: var(--navy);
        }

        .content {
            padding: 28px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
            border-left: 4px solid var(--gold);
        }

        .stat-card:nth-child(2) {
            border-left-color: #22c55e;
        }

        .stat-card:nth-child(3) {
            border-left-color: #ef4444;
        }

        .stat-num {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--navy);
        }

        .stat-label {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 4px;
        }

        .type-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 18px;
            border-radius: 8px;
            background: #fff;
            color: var(--navy);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            border: 1px solid #e2e8f0;
        }

        .tab.active {
            background: var(--navy);
            color: #fff;
            border-color: var(--navy);
        }

        .tab .count {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            margin-left: 6px;
        }

        .tab:not(.active) .count {
            background: #fef3c7;
            color: #92400e;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
        }

        .filter-row {
            display: flex;
            gap: 12px;
            align-items: end;
            flex-wrap: wrap;
        }

        .form-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .form-group input {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            min-width: 260px;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold), #e8a030);
            color: var(--deep);
        }

        .btn-approve {
            background: #dcfce7;
            color: #166534;
            padding: 6px 12px;
        }

        .btn-reject {
            background: #fee2e2;
            color: #b91c1c;
            padding: 6px 12px;
        }

        .btn-delete {
            background: #f1f5f9;
            color: #64748b;
            padding: 6px 12px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .alert.success {
            background: #dcfce7;
            color: #166534;
        }

        .alert.error {
            background: #fee2e2;
            color: #b91c1c;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .data-table th,
        .data-table td {
            padding: 14px 10px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .data-table th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background: linear-gradient(135deg, var(--navy), var(--gold));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: capitalize;
        }

        .badge.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge.approved {
            background: #dcfce7;
            color: #166534;
        }

        .badge.rejected {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge.google {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge.manual {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
        }

        .you-tag {
            font-size: 0.7rem;
            color: var(--gold);
            font-weight: 700;
            margin-left: 6px;
        }

        /* ── PROFILE SLIDE-IN PANEL ── */
        .topbar .admin-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: #64748b;
            cursor: pointer;
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--navy), var(--gold));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
            object-fit: cover;
        }

        .profile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(10, 31, 68, 0.5);
            z-index: 1049;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .profile-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .profile-panel {
            position: fixed;
            top: 0;
            right: -360px;
            width: 350px;
            height: 100vh;
            background: #fff;
            box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
            z-index: 1050;
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .profile-panel.active {
            right: 0;
        }

        .close-panel {
            position: absolute;
            top: 16px;
            right: 20px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close-panel:hover {
            color: var(--navy);
        }

        .panel-content {
            padding: 60px 24px 24px;
            text-align: center;
        }

        .panel-pic,
        .panel-pic-text {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 16px;
            border: 3px solid var(--gold);
            box-shadow: 0 4px 12px rgba(200, 147, 42, 0.2);
        }

        .panel-pic {
            object-fit: cover;
        }

        .panel-pic-text {
            background: linear-gradient(135deg, var(--navy), var(--deep));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-family: 'Playfair Display', serif;
        }

        .panel-content h3 {
            font-size: 1.4rem;
            color: var(--navy);
            margin-bottom: 4px;
        }

        .panel-email {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 24px;
        }

        .panel-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            text-align: left;
            margin-bottom: 30px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row span {
            color: #64748b;
        }

        .detail-row strong {
            color: var(--navy);
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.75rem;
            background: #e2e8f0;
            color: #475569;
        }

        .status-approved {
            background: #dcfce7;
            color: #166534;
        }

        .panel-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            background: #fee2e2;
            color: #b91c1c;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s;
        }

        .panel-logout:hover {
            background: #fecaca;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--navy);
            cursor: pointer;
            padding: 0 10px 0 0;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10, 31, 68, 0.5);
            z-index: 90;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .topbar {
            animation: fadeInDown 0.5s ease forwards;
        }

        .content>* {
            animation: fadeSlideUp 0.5s ease forwards;
            opacity: 0;
        }

        .content>*:nth-child(1) {
            animation-delay: 0.1s;
        }

        .content>*:nth-child(2) {
            animation-delay: 0.2s;
        }

        .content>*:nth-child(3) {
            animation-delay: 0.3s;
        }

        .content>*:nth-child(4) {
            animation-delay: 0.4s;
        }

        .content>*:nth-child(5) {
            animation-delay: 0.5s;
        }

        .content>*:nth-child(6) {
            animation-delay: 0.6s;
        }

        @media(max-width:768px) {
            .admin-badge span {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .sidebar {
                position: fixed;
                left: -100%;
                width: 260px;
                transition: left 0.3s ease;
            }

            .sidebar.open {
                left: 0;
            }

            .main {
                margin-left: 0;
            }

            .stats-row {
                grid-template-columns: 1fr 1fr;
            }

            .topbar {
                padding: 0 16px;
            }

            .topbar h1 {
                font-size: 0.95rem;
            }

            .topbar>div {
                gap: 12px !important;
            }

            .content {
                padding: 20px 16px;
            }

            .filter-row {
                flex-wrap: wrap;
                gap: 10px;
            }

            .type-tabs {
                flex-wrap: wrap;
                gap: 6px;
            }
        }

        @media(max-width:480px) {
            .topbar {
                padding: 0 12px;
            }

            .topbar h1 {
                font-size: 0.8rem;
            }

            .topbar a {
                font-size: 0.75rem !important;
            }

            .topbar>div {
                gap: 8px !important;
            }

            .admin-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }

            .content {
                padding: 16px 12px;
            }

            .stats-row {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-num {
                font-size: 1.6rem;
            }

            .data-table th,
            .data-table td {
                padding: 8px 6px;
                font-size: 0.78rem;
                white-space: normal;
                word-break: break-word;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        @media(max-width:360px) {
            .topbar h1 {
                font-size: 0.72rem;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .stat-num {
                font-size: 1.4rem;
            }
        }

        /* ── BACK TO TOP ── */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 44px;
            height: 44px;
            background: var(--gold);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            border: none;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: #b07d20;
            transform: translateY(-3px);
        }
    </style>
</head>

<body>
    <!-- SIDEBAR OVERLAY -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="../images/academiya_heading_logo.png" alt="Logo" class="logo-icon"
                style="background:transparent; padding:0;">
            <h2>Sai Schools Admin</h2>
            <p>Management Panel</p>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="index.php" class="nav-item">
                <span class="icon">🏠</span> Dashboard
            </a>
            <div class="nav-label" style="margin-top:12px;">Content</div>
            <a href="slides.php" class="nav-item">
                <span class="icon">🖼️</span> Hero Slides
            </a>
            <a href="gallery.php?school=samacheer" class="nav-item">
                <span class="icon">📸</span> Samacheer Gallery
            </a>
            <a href="gallery.php?school=cbse" class="nav-item">
                <span class="icon">🏫</span> CBSE Gallery
            </a>
            <a href="videos.php?school=samacheer" class="nav-item">
                <span class="icon">▶️</span> Samacheer Videos
            </a>
            <a href="videos.php?school=cbse" class="nav-item">
                <span class="icon">▶️</span> CBSE Videos
            </a>
            <a href="content_manager.php" class="nav-item">
                <span class="icon">📝</span> Content Manager
            </a>
            <a href="testimonials.php" class="nav-item">
                <span class="icon">💬</span> Testimonials
            </a>
            <div class="nav-label" style="margin-top:12px;">Leads</div>
            <a href="applications.php" class="nav-item">
                <span class="icon">📋</span> Applications
            </a>
            <a href="approve-users.php" class="nav-item active">
                <span class="icon">👥</span> User Approvals
            </a>
            <div class="nav-label" style="margin-top:12px;">Preview</div>
            <a href="../index.php" target="_blank" class="nav-item">
                <span class="icon">🌐</span> View Main Site
            </a>
            <a href="../samacheer.php" target="_blank" class="nav-item">
                <span class="icon">📖</span> Samacheer Page
            </a>
            <a href="../cbse.php" target="_blank" class="nav-item">
                <span class="icon">📚</span> CBSE Page
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <span>🚪</span> Logout
            </a>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:16px;">
                <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
                <h1>👥 User Approvals</h1>
            </div>
            <div style="display:flex; align-items:center; gap:20px;">
                <a href="index.php" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Dashboard</a>
                <div class="admin-badge" id="profileToggle">
                    <span>Welcome, <strong><?= htmlspecialchars($user_data['username']) ?></strong></span>
                    <?php if (!empty($user_data['profile_pic'])): ?>
                        <img src="<?= htmlspecialchars($user_data['profile_pic']) ?>" alt="Avatar" class="admin-avatar">
                    <?php else: ?>
                        <div class="admin-avatar"><?= strtoupper(substr($user_data['username'], 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="content">
            <?php if ($msg): ?>
                <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-num"><?= $pending_count ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= $approved_count ?></div>
                    <div class="stat-label">Approved Admins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= $rejected_count ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>

            <div class="type-tabs">
                <a href="approve-users.php?status=all" class="tab <?= $status === 'all' ? 'active' : '' ?>">All
                    Users</a>
                <a href="approve-users.php?status=pending" class="tab <?= $status === 'pending' ? 'active' : '' ?>">
                    Pending<?php if ($pending_count > 0): ?><span
                            class="count"><?= $pending_count ?></span><?php endif; ?>
                </a>
                <a href="approve-users.php?status=approved"
                    class="tab <?= $status === 'approved' ? 'active' : '' ?>">Approved</a>
                <a href="approve-users.php?status=rejected"
                    class="tab <?= $status === 'rejected' ? 'active' : '' ?>">Rejected</a>
            </div>

            <div class="card">
                <form method="GET" class="filter-row">
                    <?php if ($status !== 'all'): ?>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Search</label>
                        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                            placeholder="Username or email...">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if ($q !== ''): ?>
                        <a href="approve-users.php?<?= $filter_query(['q' => null]) ?>"
                            style="color:#64748b;font-size:.9rem;">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card">
                <h3 style="color:var(--navy);margin-bottom:16px;">
                    Admin Accounts
                    <span style="color:#94a3b8;font-weight:400;font-size:.9rem;">(<?= count($rows) ?> shown)</span>
                </h3>

                <?php if (empty($rows)): ?>
                    <div class="empty">No admin users found<?= $status === 'pending' ? ' awaiting approval' : '' ?>.</div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Login Type</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row):
                                    $is_self = ($row['username'] === $current_user) || ($row['email'] && $row['email'] === $current_email);
                                    $initial = strtoupper(substr($row['username'], 0, 1));
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="user-cell">
                                                <div class="avatar">
                                                    <?php if (!empty($row['profile_pic'])): ?>
                                                        <img src="<?= htmlspecialchars($row['profile_pic']) ?>" alt="">
                                                    <?php else: ?>
                                                        <?= $initial ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($row['username']) ?></strong>
                                                    <?php if ($is_self): ?><span class="you-tag">(You)</span><?php endif; ?>
                                                    <?php if ($row['email']): ?>
                                                        <br><small
                                                            style="color:#64748b;"><?= htmlspecialchars($row['email']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span
                                                class="badge <?= htmlspecialchars($row['auth_type']) ?>"><?= htmlspecialchars($row['auth_type']) ?></span>
                                        </td>
                                        <td><span
                                                class="badge <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span>
                                        </td>
                                        <td><?= $row['created_at'] ? htmlspecialchars(date('d M Y, h:i A', strtotime($row['created_at']))) : '—' ?>
                                        </td>
                                        <td>
                                            <?php if ($is_self): ?>
                                                <span style="color:#94a3b8;font-size:.8rem;">—</span>
                                            <?php else: ?>
                                                <div class="actions">
                                                    <?php if ($row['status'] !== 'approved'): ?>
                                                        <a class="btn btn-approve"
                                                            href="approve-users.php?<?= $filter_query(['action' => 'approve', 'id' => $row['id']]) ?>"
                                                            onclick="return confirm('Approve this user?');">✓ Approve</a>
                                                    <?php endif; ?>
                                                    <?php if ($row['status'] !== 'rejected'): ?>
                                                        <a class="btn btn-reject"
                                                            href="approve-users.php?<?= $filter_query(['action' => 'reject', 'id' => $row['id']]) ?>"
                                                            onclick="return confirm('Reject this user? They will not be able to log in.');">✕
                                                            Reject</a>
                                                    <?php endif; ?>
                                                    <a class="btn btn-delete"
                                                        href="approve-users.php?<?= $filter_query(['action' => 'delete', 'id' => $row['id']]) ?>"
                                                        onclick="return confirm('Permanently delete this account?');">🗑 Delete</a>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?= $filter_query(['page' => $page - 1]) ?>" class="page-btn">« Prev</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?<?= $filter_query(['page' => $i]) ?>"
                                class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?<?= $filter_query(['page' => $page + 1]) ?>" class="page-btn">Next »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <p style="color:#94a3b8;font-size:.82rem;margin-top:8px;">
                Google sign-ups are registered as <strong>pending</strong> until you approve them here.
                Manual username registrations are auto-approved.
            </p>
        </div>
    </div>

    <!-- ══ PROFILE SLIDE-IN PANEL ══ -->
    <div class="profile-overlay" id="profileOverlay"></div>
    <div class="profile-panel" id="profilePanel">
        <button class="close-panel" id="closeProfile">✕</button>
        <div class="panel-content">
            <?php if (!empty($user_data['profile_pic'])): ?>
                <img src="<?= htmlspecialchars($user_data['profile_pic']) ?>" alt="Profile Picture" class="panel-pic">
            <?php else: ?>
                <div class="panel-pic-text"><?= strtoupper(substr($user_data['username'], 0, 1)) ?></div>
            <?php endif; ?>

            <h3><?= htmlspecialchars($user_data['username']) ?></h3>
            <p class="panel-email"><?= htmlspecialchars($user_data['email'] ?? 'No Email') ?></p>

            <div class="panel-details">
                <div class="detail-row">
                    <span>Role:</span>
                    <strong><?= htmlspecialchars(ucfirst($user_data['role'] ?? $_SESSION['admin_role'] ?? 'Admin')) ?></strong>
                </div>
                <div class="detail-row">
                    <span>Login Type:</span>
                    <strong><?= htmlspecialchars(ucfirst($user_data['auth_type'] ?? 'Manual')) ?></strong>
                </div>
                <div class="detail-row">
                    <span>Account Status:</span>
                    <strong><span
                            class="status-badge <?= ($user_data['status'] ?? '') === 'approved' ? 'status-approved' : '' ?>">
                            <?= htmlspecialchars(ucfirst($user_data['status'] ?? 'Approved')) ?>
                        </span></strong>
                </div>
            </div>

            <a href="logout.php" class="panel-logout">🚪 Logout</a>
        </div>
    </div>

    <!-- ══ BACK TO TOP ══ -->
    <button class="back-to-top" id="backToTop">↑</button>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const profileToggle = document.getElementById('profileToggle');
            const profilePanel = document.getElementById('profilePanel');
            const profileOverlay = document.getElementById('profileOverlay');
            const closeProfile = document.getElementById('closeProfile');

            if (profileToggle) {
                function openPanel() { profilePanel.classList.add('active'); profileOverlay.classList.add('active'); }
                function closePanel() { profilePanel.classList.remove('active'); profileOverlay.classList.remove('active'); }

                profileToggle.addEventListener('click', openPanel);
                closeProfile.addEventListener('click', closePanel);
                profileOverlay.addEventListener('click', closePanel);
            }

            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', () => { sidebar.classList.add('open'); sidebarOverlay.classList.add('active'); });
                sidebarOverlay.addEventListener('click', () => { sidebar.classList.remove('open'); sidebarOverlay.classList.remove('active'); });
            }

            const backToTop = document.getElementById('backToTop');
            if (backToTop) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 300) backToTop.classList.add('show');
                    else backToTop.classList.remove('show');
                });
                backToTop.addEventListener('click', () => { window.scrollTo({ top: 0, behavior: 'smooth' }); });
            }
        });
    </script>
</body>

</html>