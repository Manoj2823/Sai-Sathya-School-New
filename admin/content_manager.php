<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

$is_admin = ($_SESSION['admin_role'] ?? 'user') === 'admin';
$dashboard_link = $is_admin ? 'index.php' : 'user%20dashboard.php';

$msg = '';
$msg_type = '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Gracefully add is_active column if it doesn't exist
$check_col = @mysqli_query($conn, "SHOW COLUMNS FROM page_contents LIKE 'is_active'");
if ($check_col && mysqli_num_rows($check_col) == 0) {
    @mysqli_query($conn, "ALTER TABLE page_contents ADD COLUMN is_active TINYINT(1) DEFAULT 1");
}

// ── ADD / EDIT TEXT ACTION ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit_save')) {
    $page_name = mysqli_real_escape_string($conn, $_POST['page_name']);
    $section_title = mysqli_real_escape_string($conn, trim($_POST['section_title']));
    $content_text = mysqli_real_escape_string($conn, trim($_POST['content_text']));
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($section_title) && !empty($content_text)) {
        if ($action === 'add') {
            mysqli_query($conn, "INSERT INTO page_contents (page_name, section_title, content_text, is_active) VALUES ('$page_name', '$section_title', '$content_text', 1)");
            $msg = '✅ Content added successfully!';
            $msg_type = 'success';
        } else {
            $id = (int) $_POST['id'];
            mysqli_query($conn, "UPDATE page_contents SET page_name='$page_name', section_title='$section_title', content_text='$content_text', is_active=$is_active WHERE id=$id");
            $msg = '✅ Content updated successfully!';
            $msg_type = 'success';
        }
    } else {
        $msg = '❌ All fields are required!';
        $msg_type = 'error';
    }
}

// ── DELETE ACTION ────────────────────────────────────────────
if ($action === 'delete' && isset($_GET['id'])) {
    mysqli_query($conn, "DELETE FROM page_contents WHERE id=" . (int) $_GET['id']);
    header('Location: content_manager.php?msg=deleted');
    exit;
}

// ── TOGGLE ACTION ────────────────────────────────────────────
if ($action === 'toggle' && isset($_GET['id'])) {
    mysqli_query($conn, "UPDATE page_contents SET is_active = 1-is_active WHERE id=" . (int) $_GET['id']);
    header('Location: content_manager.php?msg=toggled');
    exit;
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') {
        $msg = '🗑️ Content deleted successfully!';
        $msg_type = 'success';
    } elseif ($_GET['msg'] === 'toggled') {
        $msg = '👁️ Content visibility toggled!';
        $msg_type = 'success';
    }
}

// FETCH FOR EDIT
$edit_row = null;
if (isset($_GET['edit'])) {
    $edit_res = mysqli_query($conn, "SELECT * FROM page_contents WHERE id=" . (int) $_GET['edit']);
    $edit_row = mysqli_fetch_assoc($edit_res);
}

// FETCH ALL CONTENTS
$q = trim($_GET['q'] ?? '');
$where_sql = '1=1';
if ($q !== '') {
    $esc = mysqli_real_escape_string($conn, $q);
    $where_sql .= " AND (section_title LIKE '%$esc%' OR content_text LIKE '%$esc%')";
}

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$total_rows = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM page_contents WHERE $where_sql"))[0];
$total_pages = ceil($total_rows / $limit);
$contents_res = mysqli_query($conn, "SELECT * FROM page_contents WHERE $where_sql ORDER BY page_name ASC, id DESC LIMIT $limit OFFSET $offset");
$q_param = $q !== '' ? '&q=' . urlencode($q) : '';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Manager – Admin</title>
    <link rel="icon" type="image/png" href="../images/academiya_heading_logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --navy: #0a1f44;
            --deep: #071530;
            --gold: #c8932a;
            --gold-lt: #f0c060;
            --sidebar-w: 240px;
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
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 2px;
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
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.3);
            color: #fff;
        }

        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 28px;
            height: 64px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .content-wrap {
            padding: 24px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.07);
            margin-bottom: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--navy);
            text-transform: uppercase;
        }

        input[type=text],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-family: inherit;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, var(--gold), #e8a030);
            color: var(--deep);
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert.success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge.home {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge.samacheer {
            background: #fef3c7;
            color: #92400e;
        }

        .badge.cbse {
            background: #dcfce7;
            color: #166534;
        }

    .badge.active {
        background: #dcfce7;
        color: #166534;
    }

    .badge.inactive {
        background: #fee2e2;
        color: #991b1b;
    }

        .table-wrap {
            overflow-x: auto;
            margin-top: 15px;
        }

        .action-btns {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .btn-edit {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .btn-edit:hover {
            background: #bfdbfe;
        }

    .btn-toggle {
        background: #fef3c7;
        color: #92400e;
    }

    .btn-toggle:hover {
        background: #fde68a;
    }

        .btn-del {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-del:hover {
            background: #fecaca;
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

        .content-wrap>* {
            animation: fadeSlideUp 0.5s ease forwards;
            opacity: 0;
        }

        .content-wrap>*:nth-child(1) {
            animation-delay: 0.1s;
        }

        .content-wrap>*:nth-child(2) {
            animation-delay: 0.2s;
        }

        .content-wrap>*:nth-child(3) {
            animation-delay: 0.3s;
        }

        .content-wrap>*:nth-child(4) {
            animation-delay: 0.4s;
        }

        .content-wrap>*:nth-child(5) {
            animation-delay: 0.5s;
        }

        .content-wrap>*:nth-child(6) {
            animation-delay: 0.6s;
        }

        @media(max-width:1100px) {
            .topbar {
                padding: 0 16px;
            }

            .topbar h1 {
                font-size: 1.05rem;
                white-space: nowrap;
            }

            .admin-badge span {
                display: none;
            }
        }

        @media(max-width:900px) {
            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;
            }
        }

        @media(max-width:768px) {
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

            .form-grid {
                grid-template-columns: 1fr;
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

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .content-card {
                padding: 16px;
            }

            textarea,
            input[type=text],
            select {
                font-size: 0.9rem;
            }
        }

        @media(max-width:360px) {
            .topbar h1 {
                font-size: 0.72rem;
            }

            .content {
                padding: 12px 10px;
            }

            .btn {
                font-size: 0.8rem;
                padding: 8px 14px;
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

    <?php if ($is_admin): ?>
        <!-- ═══ SIDEBAR OVERLAY ═══ -->
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
                <a href="content_manager.php" class="nav-item active">
                    <span class="icon">📝</span> Content Manager
                </a>
                <a href="testimonials.php" class="nav-item">
                    <span class="icon">💬</span> Testimonials
                </a>
                <div class="nav-label" style="margin-top:12px;">Leads</div>
                <a href="applications.php" class="nav-item">
                    <span class="icon">📋</span> Applications
                </a>
                <a href="approve-users.php" class="nav-item">
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
    <?php endif; ?>

    <div class="main" <?= !$is_admin ? 'style="margin-left:0;"' : '' ?>>
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:16px;">
                <?php if ($is_admin): ?>
                    <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
                <?php endif; ?>
                <h1>📝 Page Content Management</h1>
            </div>
            <div style="display:flex; align-items:center; gap:20px;">
                <a href="<?= $dashboard_link ?>" style="color:#64748b;text-decoration:none;font-size:.9rem;">←
                    Dashboard</a>
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

        <div class="content-wrap">

            <?php if ($msg): ?>
                <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <div class="card">
                <h3><?= $edit_row ? '✏️ Edit Section Content' : '➕ Add New Text Content' ?></h3>
                <form method="POST" action="content_manager.php">
                    <input type="hidden" name="action" value="<?= $edit_row ? 'edit_save' : 'add' ?>">
                    <?php if ($edit_row): ?> <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Select Page</label>
                            <select name="page_name" required>
                                <option value="home" <?= ($edit_row['page_name'] ?? '') === 'home' ? 'selected' : '' ?>>
                                    Home
                                    Page</option>
                                <option value="samacheer" <?= ($edit_row['page_name'] ?? '') === 'samacheer' ? 'selected' : '' ?>>Samacheer Page</option>
                                <option value="cbse" <?= ($edit_row['page_name'] ?? '') === 'cbse' ? 'selected' : '' ?>>
                                    CBSE
                                    Page</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Section Title / Heading</label>
                            <input type="text" name="section_title"
                                value="<?= htmlspecialchars($edit_row['section_title'] ?? '') ?>"
                                placeholder="e.g., About Our School, Principal Message" required>
                        </div>

                        <div class="form-group full">
                            <label>Content Text (HTML Allowed)</label>
                            <textarea name="content_text" placeholder="Enter your text paragraph here..."
                                required><?= htmlspecialchars($edit_row['content_text'] ?? '') ?></textarea>
                        <?php if ($edit_row): ?>
                            <label style="margin-top:14px; display:flex; align-items:center; gap:8px; cursor:pointer; text-transform:none;">
                                <input type="checkbox" name="is_active" <?= (!isset($edit_row['is_active']) || $edit_row['is_active']) ? 'checked' : '' ?>>
                                Active (shown on website)
                            </label>
                        <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit"
                        class="btn"><?= $edit_row ? '💾 Save Changes' : '➕ Publish Content' ?></button>
                    <?php if ($edit_row): ?>
                        <a href="content_manager.php" style="margin-left:10px; color:#666; text-decoration:none;">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card">
                <div
                    style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:16px;">
                    <h3 style="margin:0;">📋 Existing Page Contents</h3>
                    <form method="GET" style="display:flex; gap:10px; align-items:center;">
                        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                            placeholder="Search title or text..."
                            style="width:240px; padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:8px;">
                        <button type="submit" class="btn" style="padding:8px 16px;">Search</button>
                        <?php if ($q !== ''): ?>
                            <a href="content_manager.php"
                                style="padding:8px 16px; background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:8px; text-decoration:none; font-weight:700;">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Page</th>
                                <th>Section Title</th>
                                <th>Content Preview</th>
                            <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($contents_res)): ?>
                                <tr>
                                    <td><span class="badge <?= $row['page_name'] ?>"><?= $row['page_name'] ?></span></td>
                                    <td><strong><?= htmlspecialchars($row['section_title']) ?></strong></td>
                                    <td><?= htmlspecialchars(substr(strip_tags($row['content_text']), 0, 80)) ?>...</td>
                                <td>
                                    <span class="badge <?= (!isset($row['is_active']) || $row['is_active']) ? 'active' : 'inactive' ?>">
                                        <?= (!isset($row['is_active']) || $row['is_active']) ? 'Active' : 'Hidden' ?>
                                    </span>
                                </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="content_manager.php?edit=<?= $row['id'] ?>"
                                                class="btn-sm btn-edit">✏️ Edit</a>
                                        <a href="content_manager.php?action=toggle&id=<?= $row['id'] ?>"
                                            class="btn-sm btn-toggle" title="Show/Hide">👁 <?= (!isset($row['is_active']) || $row['is_active']) ? 'Hide' : 'Show' ?></a>
                                            <a href="content_manager.php?action=delete&id=<?= $row['id'] ?>"
                                                onclick="return confirm('Delete this content?')"
                                                class="btn-sm btn-del">🗑️ Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= $q_param ?>" class="page-btn">« Prev</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?><?= $q_param ?>"
                                class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?><?= $q_param ?>" class="page-btn">Next »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div><!-- /content-wrap -->
    </div><!-- /main -->

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