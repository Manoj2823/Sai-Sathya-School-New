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

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS admission_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school ENUM('samacheer','cbse') NOT NULL,
    student_name VARCHAR(150) NOT NULL,
    date_of_birth DATE NOT NULL,
    class_applied VARCHAR(50) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    parent_name VARCHAR(150) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(150) NOT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS teacher_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school ENUM('samacheer','cbse') NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    date_of_birth DATE NOT NULL,
    qualification VARCHAR(150) NOT NULL,
    years_experience INT NOT NULL DEFAULT 0,
    subjects VARCHAR(200) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(150) NOT NULL,
    resume_path VARCHAR(300) DEFAULT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$type = ($_GET['type'] ?? 'admission') === 'teacher' ? 'teacher' : 'admission';
$school = $_GET['school'] ?? 'all';
$q = trim($_GET['q'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');

$msg = '';
$msg_type = '';

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $id = (int) $_GET['id'];
    $table = $type === 'admission' ? 'admission_applications' : 'teacher_applications';
    mysqli_query($conn, "DELETE FROM $table WHERE id=$id");
    $params = http_build_query(array_filter([
        'type' => $type,
        'school' => $school !== 'all' ? $school : null,
        'q' => $q !== '' ? $q : null,
        'date_from' => $date_from !== '' ? $date_from : null,
        'date_to' => $date_to !== '' ? $date_to : null,
        'msg' => 'deleted',
    ]));
    header('Location: applications.php?' . $params);
    exit;
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $msg = 'Application deleted successfully.';
    $msg_type = 'success';
}

$where = ['1=1'];
if (in_array($school, ['samacheer', 'cbse'], true)) {
    $where[] = "school='" . mysqli_real_escape_string($conn, $school) . "'";
}
if ($q !== '') {
    $esc = mysqli_real_escape_string($conn, $q);
    if ($type === 'admission') {
        $where[] = "(student_name LIKE '%$esc%' OR parent_name LIKE '%$esc%' OR email LIKE '%$esc%' OR phone LIKE '%$esc%')";
    } else {
        $where[] = "(full_name LIKE '%$esc%' OR email LIKE '%$esc%' OR phone LIKE '%$esc%' OR subjects LIKE '%$esc%')";
    }
}
if ($date_from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
    $where[] = "DATE(created_at) >= '" . mysqli_real_escape_string($conn, $date_from) . "'";
}
if ($date_to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $where[] = "DATE(created_at) <= '" . mysqli_real_escape_string($conn, $date_to) . "'";
}
$where_sql = implode(' AND ', $where);

$table = $type === 'admission' ? 'admission_applications' : 'teacher_applications';

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;
$total_rows = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM $table WHERE $where_sql"))[0];
$total_pages = ceil($total_rows / $limit);

$rows_res = mysqli_query($conn, "SELECT * FROM $table WHERE $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$rows = [];
while ($r = mysqli_fetch_assoc($rows_res)) {
    $rows[] = $r;
}

$admission_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM admission_applications"))[0] ?? 0;
$teacher_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM teacher_applications"))[0] ?? 0;

$filter_query = function ($extra = []) use ($type, $school, $q, $date_from, $date_to) {
    return http_build_query(array_filter(array_merge([
        'type' => $type,
        'school' => $school !== 'all' ? $school : null,
        'q' => $q !== '' ? $q : null,
        'date_from' => $date_from !== '' ? $date_from : null,
        'date_to' => $date_to !== '' ? $date_to : null,
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
    <title>Applications – Admin</title>
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
            grid-template-columns: 1fr 1fr;
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
            border-left-color: #3b82f6;
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

        .type-tabs,
        .school-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
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

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto auto;
            gap: 12px;
            align-items: end;
        }

        .form-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold), #e8a030);
            color: var(--deep);
        }

        .btn-navy {
            background: var(--navy);
            color: #fff;
        }

        .btn-danger {
            background: #fee2e2;
            color: #b91c1c;
            padding: 6px 12px;
            font-size: 0.78rem;
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

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .data-table th,
        .data-table td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        .data-table th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: capitalize;
        }

        .badge.samacheer {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge.cbse {
            background: #dcfce7;
            color: #166534;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
        }

        .table-wrap {
            overflow-x: auto;
        }

        /* ── RESUME MODAL ── */
        .resume-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(10, 31, 68, 0.7);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .resume-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .resume-modal {
            width: 90%;
            max-width: 800px;
            height: 85vh;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .resume-modal-overlay.active .resume-modal {
            transform: translateY(0);
        }

        .resume-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .resume-modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--navy);
        }

        .resume-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
            transition: color 0.2s;
        }

        .resume-modal-close:hover {
            color: var(--navy);
        }

        .resume-modal-body {
            flex: 1;
            padding: 0;
            background: #e2e8f0;
        }

        .resume-modal-body iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
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
            .filter-grid {
                grid-template-columns: 1fr 1fr;
            }
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

            .filter-grid {
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

            .tab {
                padding: 8px 14px;
                font-size: 0.82rem;
            }

            .data-table th,
            .data-table td {
                padding: 10px 8px;
                font-size: 0.82rem;
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

            .tab {
                padding: 7px 10px;
                font-size: 0.78rem;
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

            .tab {
                padding: 6px 8px;
                font-size: 0.73rem;
            }

            .data-table th,
            .data-table td {
                font-size: 0.73rem;
                padding: 6px 4px;
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
            <a href="applications.php" class="nav-item active">
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
                <h1>📋 Form Applications</h1>
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
                    <div class="stat-num"><?= (int) $admission_count ?></div>
                    <div class="stat-label">Total Admission Inquiries</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= (int) $teacher_count ?></div>
                    <div class="stat-label">Total Teacher Applications</div>
                </div>
            </div>

            <div class="type-tabs">
                <a href="applications.php?<?= $filter_query(['type' => 'admission']) ?>"
                    class="tab <?= $type === 'admission' ? 'active' : '' ?>">🎓 Admission Inquiries</a>
                <a href="applications.php?<?= $filter_query(['type' => 'teacher']) ?>"
                    class="tab <?= $type === 'teacher' ? 'active' : '' ?>">👩‍🏫 Teacher Applications</a>
            </div>

            <div class="school-tabs">
                <a href="applications.php?<?= $filter_query(['school' => 'all']) ?>"
                    class="tab <?= $school === 'all' ? 'active' : '' ?>">All Schools</a>
                <a href="applications.php?<?= $filter_query(['school' => 'samacheer']) ?>"
                    class="tab <?= $school === 'samacheer' ? 'active' : '' ?>">Samacheer</a>
                <a href="applications.php?<?= $filter_query(['school' => 'cbse']) ?>"
                    class="tab <?= $school === 'cbse' ? 'active' : '' ?>">CBSE</a>
            </div>

            <div class="card">
                <form method="GET" class="filter-grid">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                    <input type="hidden" name="school" value="<?= htmlspecialchars($school) ?>">
                    <div class="form-group">
                        <label>Search</label>
                        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                            placeholder="Name, email, phone...">
                    </div>
                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                    </div>
                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                    </div>
                    <div></div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="applications_export.php?<?= $filter_query() ?>" class="btn btn-navy">⬇ Download CSV</a>
                </form>
            </div>

            <div class="card">
                <h3 style="color:var(--navy);margin-bottom:16px;">
                    <?= $type === 'admission' ? 'Admission Inquiries' : 'Teacher Applications' ?>
                    <span style="color:#94a3b8;font-weight:400;font-size:.9rem;">(<?= count($rows) ?> shown)</span>
                </h3>

                <?php if (empty($rows)): ?>
                    <div class="empty">No applications found for the selected filters.</div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <?php if ($type === 'admission'): ?>
                                    <tr>
                                        <th>ID</th>
                                        <th>School</th>
                                        <th>Student</th>
                                        <th>DOB</th>
                                        <th>Class</th>
                                        <th>Parent</th>
                                        <th>Contact</th>
                                        <th>Submitted</th>
                                        <th></th>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <th>ID</th>
                                        <th>School</th>
                                        <th>Name</th>
                                        <th>Qualification</th>
                                        <th>Subjects</th>
                                        <th>Experience</th>
                                        <th>Contact</th>
                                        <th>Resume</th>
                                        <th>Submitted</th>
                                        <th></th>
                                    </tr>
                                <?php endif; ?>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <td>#<?= (int) $row['id'] ?></td>
                                        <td><span
                                                class="badge <?= htmlspecialchars($row['school']) ?>"><?= htmlspecialchars($row['school']) ?></span>
                                        </td>
                                        <?php if ($type === 'admission'): ?>
                                            <td><strong><?= htmlspecialchars($row['student_name']) ?></strong><br><small><?= htmlspecialchars($row['gender']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($row['date_of_birth']) ?></td>
                                            <td><?= htmlspecialchars($row['class_applied']) ?></td>
                                            <td><?= htmlspecialchars($row['parent_name']) ?></td>
                                            <td>
                                                <a
                                                    href="tel:<?= htmlspecialchars($row['phone']) ?>"><?= htmlspecialchars($row['phone']) ?></a><br>
                                                <a
                                                    href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a>
                                            </td>
                                            <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($row['created_at']))) ?></td>
                                        <?php else: ?>
                                            <td><strong><?= htmlspecialchars($row['full_name']) ?></strong><br><small><?= htmlspecialchars($row['gender']) ?>
                                                    · DOB: <?= htmlspecialchars($row['date_of_birth']) ?></small></td>
                                            <td><?= htmlspecialchars($row['qualification']) ?></td>
                                            <td><?= htmlspecialchars($row['subjects']) ?></td>
                                            <td><?= (int) $row['years_experience'] ?> yrs</td>
                                            <td>
                                                <a
                                                    href="tel:<?= htmlspecialchars($row['phone']) ?>"><?= htmlspecialchars($row['phone']) ?></a><br>
                                                <a
                                                    href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a>
                                            </td>
                                            <td>
                                                <?php if ($row['resume_path']): ?>
                                                    <a href="#" onclick="openResumeModal('view_resume.php?file=<?= urlencode($row['resume_path']) ?>', '../<?= htmlspecialchars($row['resume_path'], ENT_QUOTES) ?>'); return false;">View</a>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($row['created_at']))) ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <a class="btn btn-danger"
                                                href="applications.php?<?= $filter_query(['action' => 'delete', 'id' => $row['id']]) ?>"
                                                onclick="return confirm('Delete this application permanently?');">Delete</a>
                                        </td>
                                    </tr>
                                    <?php if ($type === 'admission'): ?>
                                        <tr>
                                            <td colspan="9" style="background:#fafafa;font-size:.8rem;color:#64748b;">
                                                <strong>Address:</strong> <?= nl2br(htmlspecialchars($row['address'])) ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" style="background:#fafafa;font-size:.8rem;color:#64748b;">
                                                <strong>Address:</strong> <?= nl2br(htmlspecialchars($row['address'])) ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
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
        </div>
    </div>

    <!-- ══ RESUME MODAL ══ -->
    <div class="resume-modal-overlay" id="resumeModalOverlay">
        <div class="resume-modal" id="resumeModal">
            <div class="resume-modal-header">
                <h3>📄 View Resume</h3>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <a href="#" id="downloadResumeBtn" class="btn btn-navy" download style="padding: 6px 14px; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; gap: 6px;">⬇ Download</a>
                    <button class="resume-modal-close" id="closeResumeModal">✕</button>
                </div>
            </div>
            <div class="resume-modal-body">
                <iframe id="resumeIframe" src=""></iframe>
            </div>
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

            // Resume Modal Logic
            const resumeModalOverlay = document.getElementById('resumeModalOverlay');
            const closeResumeModalBtn = document.getElementById('closeResumeModal');
            const resumeIframe = document.getElementById('resumeIframe');
            const downloadResumeBtn = document.getElementById('downloadResumeBtn');

            window.openResumeModal = function(viewUrl, fileUrl) {
                resumeIframe.src = viewUrl;
                downloadResumeBtn.href = fileUrl;
                resumeModalOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            };

            function closeResumeModal() {
                resumeModalOverlay.classList.remove('active');
                setTimeout(() => { resumeIframe.src = ''; }, 300);
                document.body.style.overflow = '';
            }

            if (closeResumeModalBtn) closeResumeModalBtn.addEventListener('click', closeResumeModal);
            if (resumeModalOverlay) resumeModalOverlay.addEventListener('click', (e) => {
                if (e.target === resumeModalOverlay) closeResumeModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && resumeModalOverlay && resumeModalOverlay.classList.contains('active')) {
                    closeResumeModal();
                }
            });
        });
    </script>
</body>

</html>