<?php
session_start();
// This file has been moved to /user/dashboard.php
// Redirect everyone to correct destination
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
if (($_SESSION['admin_role'] ?? 'user') === 'admin') {
    header('Location: index.php');
    exit;
}
// User → new separate user panel
header('Location: ../user/dashboard.php');
exit;

$username = $_SESSION['admin_username'] ?? 'User';

// Fetch counts for display
$gal_sam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM gallery_images WHERE school='samacheer' AND is_active=1"))['c'];
$gal_cbse = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM gallery_images WHERE school='cbse' AND is_active=1"))['c'];
$slides = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM hero_slides WHERE is_active=1"))['c'];
$contents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM page_contents"))['c'];

// Fetch user details for profile slide-in
$session_user = mysqli_real_escape_string($conn, $username);
$user_query = @mysqli_query($conn, "SELECT * FROM admin_users WHERE username = '$session_user' LIMIT 1");
if ($user_query && mysqli_num_rows($user_query) > 0) {
    $user_data = mysqli_fetch_assoc($user_query);
} else {
    $user_data = [
        'username' => $username,
        'email' => $_SESSION['admin_email'] ?? 'No Email',
        'profile_pic' => $_SESSION['admin_pic'] ?? '',
        'auth_type' => $_SESSION['auth_type'] ?? 'manual',
        'status' => 'approved'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard – Sai Schools</title>
    <link rel="icon" type="image/png" href="../images/academiya_heading_logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0a1f44;
            --gold: #c8932a;
            --blue: #3b82f6;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Lato', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Topbar */
        .topbar {
            background: var(--navy);
            padding: 0 28px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .topbar-left img {
            width: 38px;
            height: 38px;
            border-radius: 8px;
        }

        .topbar-left h1 {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .role-badge {
            background: var(--blue);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .logout-btn {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 7px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.4);
            color: #fff;
        }

        /* Main Content */
        .container {
            max-width: 900px;
            margin: 36px auto;
            padding: 0 20px;
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--navy), #1a3a6e);
            border-radius: 16px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .welcome-banner h2 {
            font-size: 1.4rem;
            margin-bottom: 6px;
        }

        .welcome-banner p {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.9rem;
        }

        .welcome-banner .emoji {
            font-size: 3rem;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
        }

        .stat-icon {
            font-size: 1.8rem;
            margin-bottom: 8px;
        }

        .stat-num {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--navy);
        }

        .stat-label {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 4px;
        }

        /* Action Cards */
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--gold);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .action-card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .action-card .card-icon {
            font-size: 2rem;
            margin-bottom: 12px;
        }

        .action-card h3 {
            color: var(--navy);
            font-size: 1rem;
            margin-bottom: 6px;
        }

        .action-card p {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 16px;
            flex: 1;
            line-height: 1.5;
        }

        .card-actions {
            display: flex;
            gap: 10px;
        }

        .btn-manage {
            flex: 1;
            padding: 9px;
            border-radius: 8px;
            background: var(--gold);
            color: #fff;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            text-align: center;
            transition: background 0.2s;
        }

        .btn-manage:hover {
            background: #b07d20;
        }

        /* Notice box */
        .notice {
            background: #eff6ff;
            border: 1.5px solid #bfdbfe;
            border-radius: 12px;
            padding: 16px 20px;
            color: #1e40af;
            font-size: 0.88rem;
            line-height: 1.6;
        }

        .notice strong {
            display: block;
            margin-bottom: 4px;
            font-size: 0.95rem;
        }

        /* ── PROFILE SLIDE-IN PANEL ── */
        .topbar-right .admin-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            color: #fff;
            cursor: pointer;
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), var(--navy));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
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
            border: 3px solid var(--blue);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .panel-pic {
            object-fit: cover;
        }

        .panel-pic-text {
            background: linear-gradient(135deg, var(--blue), var(--navy));
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

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .welcome-banner .emoji {
                display: none;
            }

            .topbar {
                padding: 0 16px;
            }

            .topbar-left h1 {
                font-size: 0.95rem;
            }

            .role-badge {
                display: none;
            }

            .admin-badge span {
                display: none;
            }

            .container {
                padding: 20px 16px;
            }

            .welcome-banner {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .topbar {
                padding: 0 12px;
            }

            .topbar-left h1 {
                font-size: 0.82rem;
            }

            .topbar-left img {
                width: 28px;
                height: 28px;
            }

            .admin-avatar {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
            }

            .container {
                padding: 16px 12px;
            }

            .stats-grid {
                gap: 10px;
            }

            .stat-card {
                padding: 14px 10px;
            }

            .stat-num {
                font-size: 1.4rem;
            }

            .stat-label {
                font-size: 0.7rem;
            }

            .welcome-banner h2 {
                font-size: 1.15rem;
            }

            .notice {
                font-size: 0.8rem;
                padding: 14px;
                line-height: 1.6;
            }
        }

        @media (max-width: 360px) {
            .topbar-left h1 {
                font-size: 0.72rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .stat-num {
                font-size: 1.2rem;
            }

            .card-actions {
                flex-direction: column;
                gap: 6px;
            }

            .btn-view,
            .btn-edit {
                justify-content: center;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <img src="../images/academiya_heading_logo.png" alt="Logo">
            <h1>Sai Schools</h1>
        </div>
        <div class="topbar-right">
            <span class="role-badge">👤 User</span>
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

    <div class="container">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div>
                <h2>Hello, <?= htmlspecialchars($username) ?>! 👋</h2>
                <p>You can view and edit content below. Contact admin for full access.</p>
            </div>
            <span class="emoji">🏫</span>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🖼️</div>
                <div class="stat-num"><?= $slides ?></div>
                <div class="stat-label">Hero Slides</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📸</div>
                <div class="stat-num"><?= $gal_sam ?></div>
                <div class="stat-label">Samacheer Gallery</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏫</div>
                <div class="stat-num"><?= $gal_cbse ?></div>
                <div class="stat-label">CBSE Gallery</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-num"><?= $contents ?></div>
                <div class="stat-label">Page Contents</div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="section-title">📂 Manage Content</div>
        <div class="actions-grid">

            <div class="action-card">
                <div class="card-icon">🖼️</div>
                <h3>Hero Slides</h3>
                <p>View and edit the hero slider images shown on the main website homepage.</p>
                <div class="card-actions">
                    <a href="slides.php" class="btn-manage">⚙️ Manage Slides</a>
                </div>
            </div>

            <div class="action-card">
                <div class="card-icon">📸</div>
                <h3>Samacheer Gallery</h3>
                <p>View and edit gallery images for the Samacheer school page.</p>
                <div class="card-actions">
                    <a href="gallery.php?school=samacheer" class="btn-manage">⚙️ Manage Gallery</a>
                </div>
            </div>

            <div class="action-card">
                <div class="card-icon">🏫</div>
                <h3>CBSE Gallery</h3>
                <p>View and edit gallery images for the CBSE school page.</p>
                <div class="card-actions">
                    <a href="gallery.php?school=cbse" class="btn-manage">⚙️ Manage Gallery</a>
                </div>
            </div>

            <div class="action-card">
                <div class="card-icon">📝</div>
                <h3>Page Content</h3>
                <p>View and edit text content sections across the website pages.</p>
                <div class="card-actions">
                    <a href="content_manager.php" class="btn-manage">⚙️ Manage Content</a>
                </div>
            </div>

        </div>

        <!-- Notice -->
        <div class="notice">
            <strong>ℹ️ User Access Notice</strong>
            You have <strong>View & Edit</strong> access only. Delete option is available for your own uploaded content.
            For full admin access, contact the site administrator.
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
        });
    </script>
</body>

</html>