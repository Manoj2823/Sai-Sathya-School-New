<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
// Role helpers
$is_admin = ($_SESSION['admin_role'] ?? 'user') === 'admin';
$current_user = $_SESSION['admin_username'] ?? '';
$dashboard_link = $is_admin ? 'index.php' : '../user/dashboard.php';
require_once 'db.php';

$school = in_array($_GET['school'] ?? '', ['samacheer', 'cbse']) ? $_GET['school'] : 'samacheer';
$school_label = $school === 'samacheer' ? 'Samacheer' : 'CBSE';

$msg = '';
$msg_type = '';

// ── UPLOAD HELPER ─────────────────────────────────────────────
function uploadGalleryImage($file_key, $subfolder = 'gallery')
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
    $filename = 'gal_' . time() . '_' . rand(100, 999) . '.' . $ext;
    $dest = $dest_dir . $filename;

    if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $dest)) {
        return $subfolder . '/' . $filename;
    }
    return ['error' => 'Upload failed. Check folder permissions.'];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── ADD ───────────────────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload = uploadGalleryImage('image_file', 'gallery');
    if (is_array($upload) && isset($upload['error'])) {
        $msg = $upload['error'];
        $msg_type = 'error';
    } else {
        $image_path = $upload ?? mysqli_real_escape_string($conn, trim($_POST['image_path'] ?? ''));
        $caption = mysqli_real_escape_string($conn, trim($_POST['caption'] ?? ''));
        $sort_order = (int) ($_POST['sort_order'] ?? 0);
        $sch = mysqli_real_escape_string($conn, $school);

        if ($image_path) {
            mysqli_query($conn, "INSERT INTO gallery_images (school, image_path, caption, sort_order, is_active)
                VALUES ('$sch','$image_path','$caption',$sort_order,1)");
            $msg = '✅ Image added successfully!';
            $msg_type = 'success';
        } else {
            $msg = 'Image is required.';
            $msg_type = 'error';
        }
    }
}

// ── EDIT SAVE ─────────────────────────────────────────────────
if ($action === 'edit_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $upload = uploadGalleryImage('image_file', 'gallery');
    $new_img = (!is_array($upload) && $upload) ? $upload : null;

    $caption = mysqli_real_escape_string($conn, trim($_POST['caption'] ?? ''));
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $img_sql = $new_img ? ", image_path='$new_img'" : '';

    mysqli_query($conn, "UPDATE gallery_images SET
        caption='$caption', sort_order=$sort_order, is_active=$is_active
        $img_sql WHERE id=$id");
    $msg = '✅ Image updated!';
    $msg_type = 'success';
}

// ── TOGGLE ────────────────────────────────────────────────────
if ($action === 'toggle' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    mysqli_query($conn, "UPDATE gallery_images SET is_active = 1-is_active WHERE id=$id");
    header("Location: gallery.php?school=$school&msg=toggled");
    exit;
}

// ── DELETE ────────────────────────────────────────────────────
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    // Check ownership if not admin
    if (!$is_admin) {
        $own_check = mysqli_query($conn, "SELECT id FROM gallery_images WHERE id=$id AND uploaded_by='$current_user'");
        if (mysqli_num_rows($own_check) === 0) {
            header('Location: gallery.php?school=' . $_GET['school'] . '&error=noperm');
            exit;
        }
    }
    mysqli_query($conn, "DELETE FROM gallery_images WHERE id=$id");
    header("Location: gallery.php?school=$school&msg=deleted");
    exit;
}

if (isset($_GET['msg'])) {
    $msgs = ['toggled' => 'Image status changed!', 'deleted' => 'Image deleted!'];
    $msg = $msgs[$_GET['msg']] ?? '';
    $msg_type = 'success';
}

// ── FETCH EDIT ROW ────────────────────────────────────────────
$edit_row = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $edit_res = mysqli_query($conn, "SELECT * FROM gallery_images WHERE id=$edit_id");
    $edit_row = mysqli_fetch_assoc($edit_res);
}

// ── FETCH ALL IMAGES ──────────────────────────────────────────
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;
$total_rows = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM gallery_images WHERE school='$school'"))[0];
$total_pages = ceil($total_rows / $limit);
$gal_res = mysqli_query($conn, "SELECT * FROM gallery_images WHERE school='$school' ORDER BY sort_order ASC, id ASC LIMIT $limit OFFSET $offset");

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
    <title><?= $school_label ?> Gallery – Admin</title>
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

        /* ── SIDEBAR ── */
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

        /* ── MAIN ── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
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
            font-weight: 700;
        }

        .topbar a {
            font-size: 0.84rem;
            color: var(--gold);
            text-decoration: none;
        }

        /* School switcher tabs */
        .school-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 22px;
        }

        .school-tab {
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 0.86rem;
            font-weight: 700;
            text-decoration: none;
            border: 2px solid #e2e8f0;
            color: #64748b;
            transition: all 0.2s;
        }

        .school-tab.active {
            background: var(--navy);
            color: #fff;
            border-color: var(--navy);
        }

        .school-tab:hover:not(.active) {
            border-color: var(--gold);
            color: var(--gold);
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

        /* ── CARD ── */
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
        textarea {
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

        .hint {
            font-size: 0.74rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        .current-thumb {
            width: 100%;
            max-height: 130px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            margin-bottom: 6px;
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

        /* ── GALLERY GRID ── */
        .gallery-admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
        }

        .gal-item {
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            border: 1.5px solid #e2e8f0;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .gal-item:hover {
            box-shadow: 0 6px 20px rgba(10, 31, 68, 0.12);
            transform: translateY(-2px);
        }

        .gal-item img {
            width: 100%;
            height: 130px;
            object-fit: cover;
            display: block;
        }

        .gal-item-body {
            padding: 10px 12px;
        }

        .gal-caption {
            font-size: 0.82rem;
            color: var(--navy);
            font-weight: 600;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gal-order {
            font-size: 0.72rem;
            color: #94a3b8;
            margin-bottom: 8px;
        }

        .action-btns {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 3px;
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

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 0.68rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .badge.active {
            background: #dcfce7;
            color: #166534;
        }

        .badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: #94a3b8;
        }

        .empty-state .em-icon {
            font-size: 3rem;
            margin-bottom: 12px;
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

            .form-grid {
                grid-template-columns: 1fr;
            }

            .gallery-admin-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .topbar { padding: 0 16px; }
            .topbar h1 { font-size: 0.95rem; }
            .topbar > div { gap: 12px !important; }
        }

        @media(max-width:480px) {
            .topbar { padding: 0 12px; }
            .topbar h1 { font-size: 0.8rem; }
            .topbar a { font-size: 0.75rem !important; }
            .topbar > div { gap: 8px !important; }
            .admin-avatar { width: 32px; height: 32px; font-size: 0.8rem; }
        }

        /* ── BACK TO TOP ── */

        /* ── TOPBAR ADMIN BADGE ── */
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

        /* ── PROFILE SLIDE-IN PANEL ── */
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
            overflow-y: auto;
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
            z-index: 1;
        }

        .close-panel:hover {
            color: var(--navy);
        }

        .panel-content {
            padding: 60px 24px 24px;
            text-align: center;
        }

        .panel-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 16px;
            display: block;
            border: 3px solid var(--gold);
            box-shadow: 0 4px 12px rgba(200, 147, 42, 0.2);
        }

        .panel-pic-text {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 16px;
            border: 3px solid var(--gold);
            box-shadow: 0 4px 12px rgba(200, 147, 42, 0.2);
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

        <!-- ═══ SIDEBAR ═══ -->
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
                <a href="gallery.php?school=samacheer" class="nav-item <?= $school === 'samacheer' ? 'active' : '' ?>">
                    <span class="icon">📸</span> Samacheer Gallery
                </a>
                <a href="gallery.php?school=cbse" class="nav-item <?= $school === 'cbse' ? 'active' : '' ?>">
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
                <?php if ($is_admin): ?>
                    <a href="approve-users.php" class="nav-item">
                        <span class="icon">👥</span> User Approvals
                    </a>
                <?php endif; ?>

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
                    <span class="icon">🚪</span> Logout
                </a>
            </div>
        </aside>
    <?php endif; ?>

    <!-- ═══ MAIN ═══ -->
    <div class="main" <?= !$is_admin ? 'style="margin-left:0;"' : '' ?>>
        <header class="topbar">
            <div style="display:flex; align-items:center; gap:16px;">
                <?php if ($is_admin): ?>
                    <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
                <?php endif; ?>
                <h1>📸 <?= $school_label ?> Gallery</h1>
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

        <div class="content">

            <?php if ($msg): ?>
                <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <!-- School switcher -->
            <div class="school-tabs">
                <a href="gallery.php?school=samacheer"
                    class="school-tab <?= $school === 'samacheer' ? 'active' : '' ?>">📸 Samacheer Gallery</a>
                <a href="gallery.php?school=cbse" class="school-tab <?= $school === 'cbse' ? 'active' : '' ?>">🏫 CBSE
                    Gallery</a>
            </div>

            <!-- ══ ADD / EDIT FORM ══ -->
            <div class="card">
                <div class="card-title">
                    <?= $edit_row ? '✏️ Edit Image' : '➕ Add New Image' ?>
                </div>
                <form method="POST" enctype="multipart/form-data" action="gallery.php?school=<?= $school ?>">
                    <input type="hidden" name="action" value="<?= $edit_row ? 'edit_save' : 'add' ?>">
                    <?php if ($edit_row): ?>
                        <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
                    <?php endif; ?>

                    <div class="form-grid">

                        <div class="form-group">
                            <label>📷 Upload Image</label>
                            <?php if ($edit_row && $edit_row['image_path']): ?>
                                <img src="../<?= htmlspecialchars($edit_row['image_path']) ?>" class="current-thumb"
                                    onerror="this.style.display='none'">
                                <div class="hint">Current: <?= htmlspecialchars($edit_row['image_path']) ?></div>
                            <?php endif; ?>
                            <input type="file" name="image_file" accept="image/*">
                            <div class="hint">JPG/PNG/WEBP · Max
                                5MB<?= $edit_row ? ' · Leave blank to keep current' : '' ?></div>
                        </div>

                        <div class="form-group">
                            <label>🗂 Or Enter Image Path</label>
                            <input type="text" name="image_path"
                                value="<?= htmlspecialchars($edit_row['image_path'] ?? '') ?>"
                                placeholder="gallery/1.jpg">
                            <div class="hint">If an image is uploaded, this path will be ignored.</div>

                            <label style="margin-top:14px;">Sort Order (1,2,3…)</label>
                            <input type="number" name="sort_order" value="<?= $edit_row['sort_order'] ?? 0 ?>" min="0">

                            <?php if ($edit_row): ?>
                                <label style="margin-top:14px; display:flex; align-items:center; gap:8px; cursor:pointer;">
                                    <input type="checkbox" name="is_active" <?= ($edit_row['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    Active (shown in gallery)
                                </label>
                            <?php endif; ?>
                        </div>

                        <div class="form-group full">
                            <label>💬 Caption</label>
                            <input type="text" name="caption"
                                value="<?= htmlspecialchars($edit_row['caption'] ?? '') ?>"
                                placeholder="e.g. Annual Day 2024 · Science Fair · Sports Meet">
                        </div>

                    </div>

                    <div style="display:flex; gap:10px; margin-top:20px;">
                        <button type="submit" class="btn btn-primary">
                            <?= $edit_row ? '💾 Save Changes' : '➕ Add Image' ?>
                        </button>
                        <?php if ($edit_row): ?>
                            <a href="gallery.php?school=<?= $school ?>" class="btn btn-secondary">✕ Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- ══ GALLERY GRID ══ -->
            <div class="card">
                <div class="card-title">🖼️ <?= $school_label ?> Gallery Images</div>

                <?php if (mysqli_num_rows($gal_res) === 0): ?>
                    <div class="empty-state">
                        <div class="em-icon">📭</div>
                        <p>No images yet. Add your first image above!</p>
                    </div>
                <?php else: ?>
                    <div class="gallery-admin-grid">
                        <?php while ($row = mysqli_fetch_assoc($gal_res)): ?>
                            <div class="gal-item">
                                <img src="../<?= htmlspecialchars($row['image_path']) ?>"
                                    alt="<?= htmlspecialchars($row['caption']) ?>"
                                    onerror="this.src='https://placehold.co/180x130?text=No+Image'">
                                <div class="gal-item-body">
                                    <span class="badge <?= $row['is_active'] ? 'active' : 'inactive' ?>">
                                        <?= $row['is_active'] ? 'Active' : 'Hidden' ?>
                                    </span>
                                    <div class="gal-caption"><?= htmlspecialchars($row['caption'] ?: '(No caption)') ?></div>
                                    <div class="gal-order">Order: <?= $row['sort_order'] ?> &nbsp;·&nbsp; #<?= $row['id'] ?>
                                    </div>
                                    <div class="action-btns">
                                        <a href="gallery.php?school=<?= $school ?>&edit=<?= $row['id'] ?>"
                                            class="btn-sm btn-edit">✏️ Edit</a>
                                        <a href="gallery.php?school=<?= $school ?>&action=toggle&id=<?= $row['id'] ?>"
                                            class="btn-sm btn-toggle">
                                            👁 <?= $row['is_active'] ? 'Hide' : 'Show' ?>
                                        </a>
                                        <a href="gallery.php?school=<?= $school ?>&action=delete&id=<?= $row['id'] ?>"
                                            class="btn-sm btn-del" onclick="return confirm('Delete this image?')">🗑</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?school=<?= $school ?>&page=<?= $page - 1 ?>" class="page-btn">« Prev</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?school=<?= $school ?>&page=<?= $i ?>"
                                class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?school=<?= $school ?>&page=<?= $page + 1 ?>" class="page-btn">Next »</a>
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