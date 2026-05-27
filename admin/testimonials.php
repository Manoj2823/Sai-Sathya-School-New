<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

// Create table if not exists
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

$msg = '';
$msg_type = '';

// ── UPLOAD HELPER ────────────────────────────────────────────
function uploadAvatar($file_key, $subfolder = 'images')
{
    if (empty($_FILES[$file_key]['name']))
        return null;
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    $ftype = mime_content_type($_FILES[$file_key]['tmp_name']);
    if (!in_array($ftype, $allowed))
        return ['error' => 'Only JPG, PNG, WEBP allowed.'];
    if ($_FILES[$file_key]['size'] > 5 * 1024 * 1024)
        return ['error' => 'Max 5MB allowed.'];

    $dest_dir = '../' . $subfolder . '/';
    if (!is_dir($dest_dir))
        mkdir($dest_dir, 0755, true);

    $ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
    $filename = 'avatar_' . time() . '_' . rand(100, 999) . '.' . $ext;
    $dest = $dest_dir . $filename;

    if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $dest)) {
        return $subfolder . '/' . $filename;
    }
    return ['error' => 'Upload failed.'];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── ADD ──────────────────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload = uploadAvatar('avatar_file', 'images');
    if (is_array($upload) && isset($upload['error'])) {
        $msg = $upload['error'];
        $msg_type = 'error';
    } else {
        $avatar_path = $upload ?? mysqli_real_escape_string($conn, trim($_POST['avatar_path'] ?? ''));
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $role = mysqli_real_escape_string($conn, trim($_POST['role']));
        $content = mysqli_real_escape_string($conn, trim($_POST['content']));
        $sort_order = (int) ($_POST['sort_order'] ?? 0);

        if ($name && $content) {
            mysqli_query($conn, "INSERT INTO testimonials (name, role, content, avatar_path, sort_order, is_active)
                VALUES ('$name', '$role', '$content', '$avatar_path', $sort_order, 1)");
            $msg = '✅ Testimonial added successfully!';
            $msg_type = 'success';
        } else {
            $msg = '❌ Name and Review content are required.';
            $msg_type = 'error';
        }
    }
}

// ── EDIT SAVE ────────────────────────────────────────────────
if ($action === 'edit_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $upload = uploadAvatar('avatar_file', 'images');
    $new_img = (!is_array($upload) && $upload) ? $upload : null;

    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $role = mysqli_real_escape_string($conn, trim($_POST['role']));
    $content = mysqli_real_escape_string($conn, trim($_POST['content']));
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $img_sql = $new_img ? ", avatar_path='$new_img'" : '';
    mysqli_query($conn, "UPDATE testimonials SET 
        name='$name', role='$role', content='$content',
        sort_order=$sort_order, is_active=$is_active
        $img_sql WHERE id=$id");
    $msg = '✅ Testimonial updated!';
    $msg_type = 'success';
}

// ── TOGGLE ACTIVE ────────────────────────────────────────────
if ($action === 'toggle' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    mysqli_query($conn, "UPDATE testimonials SET is_active = 1-is_active WHERE id=$id");
    header('Location: testimonials.php?msg=toggled');
    exit;
}

// ── DELETE ───────────────────────────────────────────────────
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    mysqli_query($conn, "DELETE FROM testimonials WHERE id=$id");
    header('Location: testimonials.php?msg=deleted');
    exit;
}

if (isset($_GET['msg'])) {
    $msgs = ['toggled' => '👁 Visibility changed!', 'deleted' => '🗑 Testimonial deleted!'];
    $msg = $msgs[$_GET['msg']] ?? '';
    $msg_type = 'success';
}

// ── FETCH ALL ────────────────────────────────────────────────
$edit_row = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $edit_res = mysqli_query($conn, "SELECT * FROM testimonials WHERE id=$edit_id");
    $edit_row = mysqli_fetch_assoc($edit_res);
}
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$total_rows = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM testimonials"))[0];
$total_pages = ceil($total_rows / $limit);
$testi_res = mysqli_query($conn, "SELECT * FROM testimonials ORDER BY sort_order ASC, id DESC LIMIT $limit OFFSET $offset");

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
    <title>Testimonials – Admin</title>
    <link rel="icon" type="image/png" href="../images/academiya_heading_logo.png">
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
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
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
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar h1 {
            font-size: 1.1rem;
            color: var(--navy);
            font-weight: 700;
        }

        .topbar a {
            font-size: 0.84rem;
            color: var(--gold);
            text-decoration: none;
        }

        .content {
            padding: 24px;
        }

        .alert {
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .alert.success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.07);
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1/-1;
        }

        label {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--navy);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        input[type=text],
        input[type=number],
        input[type=file],
        textarea,
        select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: 'Lato', sans-serif;
            color: var(--navy);
            transition: border-color 0.2s;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--gold);
        }

        textarea {
            resize: vertical;
            min-height: 90px;
        }

        .hint {
            font-size: 0.74rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        .btn {
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.88rem;
            border: none;
            cursor: pointer;
            transition: all 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold), #e8a030);
            color: var(--deep);
        }

        .btn-primary:hover {
            box-shadow: 0 4px 16px rgba(200, 147, 42, 0.45);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: var(--navy);
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .current-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
            margin-bottom: 8px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.86rem;
        }

        th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 10px 14px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: var(--navy);
        }

        tr:hover td {
            background: #fafbfc;
        }

        .testi-thumb {
            width: 46px;
            height: 46px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
        }

        .testi-thumb-text {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--navy), var(--gold));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .badge.active {
            background: #dcfce7;
            color: #166534;
        }

        .badge.inactive {
            background: #fee2e2;
            color: #991b1b;
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

            .testimonial-card {
                padding: 16px;
            }
        }

        @media(max-width:360px) {
            .topbar h1 {
                font-size: 0.72rem;
            }

            .content {
                padding: 12px 10px;
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

    <!-- SIDEBAR -->
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
            <a href="testimonials.php" class="nav-item active">
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

    <div class="main">
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:16px;">
                <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
                <h1>💬 Testimonials Management</h1>
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

            <!-- ══ ADD / EDIT FORM ══ -->
            <div class="card">
                <div class="card-title">
                    <?= $edit_row ? '✏️ Edit Testimonial' : '➕ Add New Testimonial' ?>
                </div>
                <form method="POST" enctype="multipart/form-data" action="testimonials.php">
                    <input type="hidden" name="action" value="<?= $edit_row ? 'edit_save' : 'add' ?>">
                    <?php if ($edit_row): ?>
                        <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
                        <input type="hidden" name="avatar_path"
                            value="<?= htmlspecialchars($edit_row['avatar_path'] ?? '') ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>📷 Reviewer Photo</label>
                            <?php if ($edit_row && $edit_row['avatar_path']): ?>
                                <img src="../<?= htmlspecialchars($edit_row['avatar_path']) ?>" class="current-thumb"
                                    onerror="this.style.display='none'">
                            <?php endif; ?>
                            <input type="file" name="avatar_file" accept="image/*">
                            <div class="hint">JPG/PNG/WEBP. Max 5MB. Leave blank to show name initials.</div>
                        </div>

                        <div class="form-group">
                            <label>🔢 Sort Order (1, 2, 3...)</label>
                            <input type="number" name="sort_order" value="<?= $edit_row['sort_order'] ?? 0 ?>" min="0">
                            <?php if ($edit_row): ?>
                                <label
                                    style="margin-top:14px;display:flex;align-items:center;gap:8px;cursor:pointer;text-transform:none;">
                                    <input type="checkbox" name="is_active" <?= ($edit_row['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    Active (shown on website)
                                </label>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>👤 Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($edit_row['name'] ?? '') ?>"
                                placeholder="E.g., Shiyam M" required>
                        </div>

                        <div class="form-group">
                            <label>🏷️ Role / Designation</label>
                            <input type="text" name="role" value="<?= htmlspecialchars($edit_row['role'] ?? '') ?>"
                                placeholder="E.g., Student, VII A2" required>
                        </div>

                        <div class="form-group full">
                            <label>📝 Review Content</label>
                            <textarea name="content" placeholder="Testimonial / Review text..."
                                required><?= htmlspecialchars($edit_row['content'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;margin-top:20px;">
                        <button type="submit" class="btn btn-primary">
                            <?= $edit_row ? '💾 Save Changes' : '➕ Add Testimonial' ?>
                        </button>
                        <?php if ($edit_row): ?>
                            <a href="testimonials.php" class="btn btn-secondary">✕ Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- ══ TABLE ══ -->
            <div class="card">
                <div class="card-title">📋 All Testimonials</div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Name / Role</th>
                                <th>Review</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($testi_res)):
                                $initial = strtoupper(substr($row['name'], 0, 1));
                                ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td>
                                        <?php if (!empty($row['avatar_path'])): ?>
                                            <img src="../<?= htmlspecialchars($row['avatar_path']) ?>" class="testi-thumb"
                                                onerror="this.style.display='none'">
                                        <?php else: ?>
                                            <div class="testi-thumb-text"><?= $initial ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['name']) ?></strong><br>
                                        <span
                                            style="color:#94a3b8;font-size:0.78rem;"><?= htmlspecialchars($row['role']) ?></span>
                                    </td>
                                    <td>
                                        <span
                                            style="font-size:0.8rem;"><?= htmlspecialchars(substr(strip_tags($row['content']), 0, 70)) ?>...</span>
                                    </td>
                                    <td><?= $row['sort_order'] ?></td>
                                    <td>
                                        <span class="badge <?= $row['is_active'] ? 'active' : 'inactive' ?>">
                                            <?= $row['is_active'] ? 'Active' : 'Hidden' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="testimonials.php?edit=<?= $row['id'] ?>" class="btn-sm btn-edit">✏️
                                                Edit</a>
                                            <a href="testimonials.php?action=toggle&id=<?= $row['id'] ?>"
                                                class="btn-sm btn-toggle" title="Show/Hide">👁
                                                <?= $row['is_active'] ? 'Hide' : 'Show' ?></a>
                                            <a href="testimonials.php?action=delete&id=<?= $row['id'] ?>"
                                                class="btn-sm btn-del"
                                                onclick="return confirm('Delete this testimonial?')">🗑 Delete</a>
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
                            <a href="?page=<?= $page - 1 ?>" class="page-btn">« Prev</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="page-btn">Next »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div><!-- /content -->
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