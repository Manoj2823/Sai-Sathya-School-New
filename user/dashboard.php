<?php
require_once 'Auth guard.php';
require_once 'db.php';

$username = $_SESSION['admin_username'] ?? 'User';

// Stats
$gal_sam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM gallery_images WHERE school='samacheer' AND is_active=1"))['c'] ?? 0;
$gal_cbse = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM gallery_images WHERE school='cbse' AND is_active=1"))['c'] ?? 0;
$slides = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM hero_slides WHERE is_active=1"))['c'] ?? 0;
$videos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM school_videos WHERE is_active=1"))['c'] ?? 0;
$contents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM page_contents"))['c'] ?? 0;

// Fetch user from DB for profile panel
$esc_user = mysqli_real_escape_string($conn, $username);
$user_res = @mysqli_query($conn, "SELECT * FROM admin_users WHERE username='$esc_user' LIMIT 1");
$user_data = ($user_res && mysqli_num_rows($user_res) > 0)
    ? mysqli_fetch_assoc($user_res)
    : ['username' => $username, 'email' => $_SESSION['admin_email'] ?? '', 'profile_pic' => $_SESSION['admin_pic'] ?? '', 'auth_type' => $_SESSION['auth_type'] ?? 'manual', 'status' => 'approved', 'role' => 'user'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard – Sai Schools</title>
    <link rel="icon" type="image/png" href="../images/academiya_heading_logo.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Playfair+Display:wght@600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --navy: #0a1f44;
            --gold: #c8932a;
            --blue: #3b82f6;
            --deep: #071530;
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

        /* ── TOPBAR ── */
        .topbar {
            background: linear-gradient(135deg, var(--deep), #0d2960);
            padding: 0 28px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .2);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .topbar-left img {
            width: 36px;
            height: 36px;
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
            background: rgba(59, 130, 246, .25);
            border: 1px solid rgba(59, 130, 246, .5);
            color: #93c5fd;
            font-size: .73rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            letter-spacing: .05em;
        }

        .logout-btn {
            background: rgba(239, 68, 68, .18);
            color: #fca5a5;
            padding: 7px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: .85rem;
            font-weight: 700;
            transition: all .2s;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, .35);
            color: #fff;
        }

        .admin-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .9rem;
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

        /* ── LAYOUT ── */
        .page-wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 20px;
        }

        /* ── WELCOME BANNER ── */
        .welcome-banner {
            background: linear-gradient(135deg, var(--navy), #1a3a6e);
            border-radius: 16px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .welcome-banner h2 {
            font-size: 1.4rem;
            margin-bottom: 6px;
        }

        .welcome-banner p {
            color: rgba(255, 255, 255, .65);
            font-size: .9rem;
        }

        .welcome-banner .emoji {
            font-size: 3rem;
        }

        /* ── STATS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 18px 14px;
            text-align: center;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .06);
            border-top: 3px solid var(--gold);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:nth-child(2) {
            border-top-color: #3b82f6;
        }

        .stat-card:nth-child(3) {
            border-top-color: #22c55e;
        }

        .stat-card:nth-child(4) {
            border-top-color: #ef4444;
        }

        .stat-card:nth-child(5) {
            border-top-color: #a855f7;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 12px auto;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: linear-gradient(135deg, rgba(200, 147, 42, 0.12), rgba(200, 147, 42, 0.06));
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(59, 130, 246, 0.06));
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.12), rgba(34, 197, 94, 0.06));
        }

        .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(239, 68, 68, 0.06));
        }

        .stat-card:nth-child(5) .stat-icon {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.12), rgba(168, 85, 247, 0.06));
        }

        .stat-num {
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--navy);
        }

        .stat-label {
            font-size: .74rem;
            color: #64748b;
            margin-top: 4px;
        }

        /* ── SECTION ── */
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--gold);
        }

        /* ── ACTION CARDS ── */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .action-card {
            background: #fff;
            border-radius: 14px;
            padding: 24px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .07);
            transition: all .25s;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 24px rgba(10, 31, 68, .12);
            border-color: var(--gold);
        }

        .card-icon {
            font-size: 2.4rem;
            margin-bottom: 12px;
        }

        .action-card h3 {
            color: var(--navy);
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .action-card p {
            color: #64748b;
            font-size: .84rem;
            line-height: 1.5;
            flex: 1;
            margin-bottom: 16px;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-view {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 14px;
            background: #f0f4ff;
            color: var(--navy);
            border-radius: 7px;
            text-decoration: none;
            font-size: .82rem;
            font-weight: 700;
            transition: all .2s;
        }

        .btn-view:hover {
            background: var(--navy);
            color: #fff;
        }

        .btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 14px;
            background: linear-gradient(135deg, var(--gold), #e8a030);
            color: #fff;
            border-radius: 7px;
            text-decoration: none;
            font-size: .82rem;
            font-weight: 700;
            transition: all .2s;
        }

        .btn-edit:hover {
            opacity: .9;
            transform: translateY(-1px);
        }

        /* ── NOTICE ── */
        .notice {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-left: 4px solid var(--gold);
            border-radius: 10px;
            padding: 14px 20px;
            color: #92400e;
            font-size: .88rem;
            margin-bottom: 28px;
        }

        .notice strong {
            display: block;
            margin-bottom: 4px;
            font-size: .95rem;
        }

        /* ── PROFILE PANEL ── */
        .profile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(10, 31, 68, .5);
            z-index: 1049;
            opacity: 0;
            visibility: hidden;
            transition: all .3s ease;
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
            box-shadow: -4px 0 24px rgba(0, 0, 0, .15);
            z-index: 1050;
            transition: right .3s ease;
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
            transition: color .2s;
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
            box-shadow: 0 4px 12px rgba(59, 130, 246, .2);
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
            font-size: 1.3rem;
            color: var(--navy);
            margin-bottom: 4px;
        }

        .panel-email {
            font-size: .88rem;
            color: #64748b;
            margin-bottom: 20px;
        }

        .panel-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 14px;
            text-align: left;
            margin-bottom: 24px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 9px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: .88rem;
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
            padding: 3px 10px;
            border-radius: 99px;
            font-size: .72rem;
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
            padding: 13px;
            border-radius: 10px;
            background: #fee2e2;
            color: #b91c1c;
            text-decoration: none;
            font-weight: 700;
            transition: all .2s;
        }

        .panel-logout:hover {
            background: #fecaca;
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

        .page-wrap>* {
            animation: fadeSlideUp 0.5s ease forwards;
            opacity: 0;
        }

        .page-wrap>*:nth-child(1) {
            animation-delay: 0.1s;
        }

        .page-wrap>*:nth-child(2) {
            animation-delay: 0.2s;
        }

        .page-wrap>*:nth-child(3) {
            animation-delay: 0.3s;
        }

        .page-wrap>*:nth-child(4) {
            animation-delay: 0.4s;
        }

        .page-wrap>*:nth-child(5) {
            animation-delay: 0.5s;
        }

        .page-wrap>*:nth-child(6) {
            animation-delay: 0.6s;
        }

        @media(max-width:900px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:640px) {
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

            .logout-btn {
                padding: 6px 10px;
                font-size: 0.75rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .welcome-banner .emoji {
                display: none;
            }

            .page-wrap {
                padding: 20px 16px;
            }

            .welcome-banner {
                padding: 20px;
            }

            .welcome-banner h2 {
                font-size: 1.15rem;
            }
        }

        @media(max-width:480px) {
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

            .logout-btn {
                padding: 5px 8px;
                font-size: 0.7rem;
            }

            .page-wrap {
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

            .action-card {
                padding: 18px 14px;
            }

            .action-card h3 {
                font-size: 0.92rem;
            }

            .action-card p {
                font-size: 0.8rem;
            }

            .notice {
                font-size: 0.8rem;
                padding: 14px;
            }

            .section-title {
                font-size: 0.9rem;
            }
        }

        @media(max-width:360px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .topbar-left h1 {
                font-size: 0.75rem;
            }

            .stat-num {
                font-size: 1.2rem;
            }

            .card-actions {
                flex-direction: column;
            }

            .btn-view,
            .btn-edit {
                text-align: center;
                justify-content: center;
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

    <!-- ── TOPBAR ── -->
    <header class="topbar">
        <div class="topbar-left">
            <img src="../images/academiya_heading_logo.png" alt="Logo" onerror="this.style.display='none'">
            <h1>Sai Schools</h1>
        </div>
        <div class="topbar-right">
            <span class="role-badge">👤 USER PANEL</span>
            <div class="admin-badge" id="profileToggle">
                <span>Hi, <strong>
                        <?= htmlspecialchars($user_data['username']) ?>
                    </strong></span>
                <?php if (!empty($user_data['profile_pic'])): ?>
                    <img src="<?= htmlspecialchars($user_data['profile_pic']) ?>" alt="Avatar" class="admin-avatar">
                <?php else: ?>
                    <div class="admin-avatar">
                        <?= strtoupper(substr($user_data['username'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            <a href="../admin/logout.php" class="logout-btn">🚪 Logout</a>
        </div>
    </header>

    <div class="page-wrap">

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div>
                <h2>Hello,
                    <?= htmlspecialchars($username) ?>! 👋
                </h2>
                <p>You have <strong>View &amp; Edit</strong> access. Contact admin for full access.</p>
            </div>
            <span class="emoji">🏫</span>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🖼️</div>
                <div class="stat-num">
                    <?= $slides ?>
                </div>
                <div class="stat-label">Hero Slides</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📸</div>
                <div class="stat-num">
                    <?= $gal_sam ?>
                </div>
                <div class="stat-label">Samacheer Gallery</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏫</div>
                <div class="stat-num">
                    <?= $gal_cbse ?>
                </div>
                <div class="stat-label">CBSE Gallery</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">▶️</div>
                <div class="stat-num">
                    <?= $videos ?>
                </div>
                <div class="stat-label">Active Videos</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-num">
                    <?= $contents ?>
                </div>
                <div class="stat-label">Page Contents</div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="section-title">📂 Your Access</div>
        <div class="actions-grid">

            <div class="action-card">
                <div class="card-icon">🖼️</div>
                <h3>Hero Slides</h3>
                <p>View the hero slider images on the main homepage. Edit titles, descriptions, and button text.</p>
                <div class="card-actions">
                    <a href="slides.php" class="btn-view">👁 View</a>
                    <a href="slides.php?mode=edit" class="btn-edit">✏️ Edit</a>
                </div>
            </div>

            <div class="action-card">
                <div class="card-icon">📸</div>
                <h3>Samacheer Gallery</h3>
                <p>View and edit gallery images for the Samacheer school page. Add captions and sort order.</p>
                <div class="card-actions">
                    <a href="gallery.php?school=samacheer" class="btn-view">👁 View</a>
                    <a href="gallery.php?school=samacheer&mode=edit" class="btn-edit">✏️ Edit</a>
                </div>
            </div>

            <div class="action-card">
                <div class="card-icon">🏫</div>
                <h3>CBSE Gallery</h3>
                <p>View and edit gallery images for the CBSE school page. Add captions and sort order.</p>
                <div class="card-actions">
                    <a href="gallery.php?school=cbse" class="btn-view">👁 View</a>
                    <a href="gallery.php?school=cbse&mode=edit" class="btn-edit">✏️ Edit</a>
                </div>
            </div>

            <div class="action-card">
                <div class="card-icon">▶️</div>
                <h3>Samacheer Videos</h3>
                <p>View and edit YouTube videos listed on the Samacheer school page.</p>
                <div class="card-actions">
                    <a href="videos.php?school=samacheer" class="btn-view">👁 View</a>
                    <a href="videos.php?school=samacheer&mode=edit" class="btn-edit">✏️ Edit</a>
                </div>
            </div>

            <div class="action-card">
                <div class="card-icon">🎬</div>
                <h3>CBSE Videos</h3>
                <p>View and edit YouTube videos listed on the CBSE school page.</p>
                <div class="card-actions">
                    <a href="videos.php?school=cbse" class="btn-view">👁 View</a>
                    <a href="videos.php?school=cbse&mode=edit" class="btn-edit">✏️ Edit</a>
                </div>
            </div>

            <div class="action-card">
                <div class="card-icon">📝</div>
                <h3>Page Content</h3>
                <p>View and edit text content sections across the website pages.</p>
                <div class="card-actions">
                    <a href="content.php" class="btn-view">👁 View</a>
                    <a href="content.php?mode=edit" class="btn-edit">✏️ Edit</a>
                </div>
            </div>

        </div>

        <!-- Notice -->
        <div class="notice">
            <strong>ℹ️ User Access — What you can &amp; cannot do</strong>
            ✅ View all content &nbsp;·&nbsp; ✅ Edit existing records (caption, title, sort order, text)
            &nbsp;·&nbsp; ❌ Cannot delete &nbsp;·&nbsp; ❌ Cannot add new slides (admin only)
            &nbsp;·&nbsp; ❌ Cannot access Admin Panel. Contact your admin for elevated access.
        </div>

        <!-- Preview Links -->
        <div class="section-title">🌐 Preview Site</div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:32px;">
            <a href="../index.php" target="_blank"
                style="padding:9px 18px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:var(--navy);font-size:.88rem;font-weight:700;transition:all .2s;"
                onmouseover="this.style.borderColor='#c8932a'" onmouseout="this.style.borderColor='#e2e8f0'">🏠 Main
                Site</a>
            <a href="../samacheer.php" target="_blank"
                style="padding:9px 18px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:var(--navy);font-size:.88rem;font-weight:700;transition:all .2s;"
                onmouseover="this.style.borderColor='#c8932a'" onmouseout="this.style.borderColor='#e2e8f0'">📖
                Samacheer Page</a>
            <a href="../cbse.php" target="_blank"
                style="padding:9px 18px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:var(--navy);font-size:.88rem;font-weight:700;transition:all .2s;"
                onmouseover="this.style.borderColor='#c8932a'" onmouseout="this.style.borderColor='#e2e8f0'">📚 CBSE
                Page</a>
        </div>

    </div><!-- /page-wrap -->

    <!-- Profile Panel -->
    <div class="profile-overlay" id="profileOverlay"></div>
    <div class="profile-panel" id="profilePanel">
        <button class="close-panel" id="closeProfile">✕</button>
        <div class="panel-content">
            <?php if (!empty($user_data['profile_pic'])): ?>
                <img src="<?= htmlspecialchars($user_data['profile_pic']) ?>" alt="Profile" class="panel-pic">
            <?php else: ?>
                <div class="panel-pic-text">
                    <?= strtoupper(substr($user_data['username'], 0, 1)) ?>
                </div>
            <?php endif; ?>
            <h3>
                <?= htmlspecialchars($user_data['username']) ?>
            </h3>
            <p class="panel-email">
                <?= htmlspecialchars($user_data['email'] ?? 'No Email') ?>
            </p>
            <div class="panel-details">
                <div class="detail-row"><span>Role:</span><strong>User</strong></div>
                <div class="detail-row"><span>Login Type:</span><strong>
                        <?= htmlspecialchars(ucfirst($user_data['auth_type'] ?? 'Manual')) ?>
                    </strong></div>
                <div class="detail-row"><span>Status:</span><strong><span
                            class="status-badge status-approved">Approved</span></strong></div>
            </div>
            <a href="../admin/logout.php" class="panel-logout">🚪 Logout</a>
        </div>
    </div>

    <!-- ══ BACK TO TOP ══ -->
    <button class="back-to-top" id="backToTop">↑</button>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('profileToggle');
            const panel = document.getElementById('profilePanel');
            const overlay = document.getElementById('profileOverlay');
            const close = document.getElementById('closeProfile');
            const open = () => { panel.classList.add('active'); overlay.classList.add('active'); };
            const shut = () => { panel.classList.remove('active'); overlay.classList.remove('active'); };
            toggle.addEventListener('click', open);
            close.addEventListener('click', shut);
            overlay.addEventListener('click', shut);

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