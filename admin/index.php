<?php
session_start();
// Login check
if (!isset($_SESSION['admin_logged_in'])) {
  header('Location: login.php');
  exit;
}

require_once 'db.php';

// Determine admin privileges
$is_admin = ($_SESSION['admin_role'] ?? 'user') === 'admin';

// Default values
$slides_count = 0;
$sam_gal_count = 0;
$cbse_gal_count = 0;
$content_count = 0;
$video_count = 0;
$applications_count = 0;
$pending_users_count = 0;
$testimonials_count = 0;

// Database iruntha mathi values-a fetch pannum
if ($conn) {
  // 1. Active Hero Slides count
  $q1 = mysqli_query($conn, "SELECT COUNT(*) FROM hero_slides WHERE is_active=1");
  $slides_count = mysqli_fetch_row($q1)[0] ?? 0;

  // 2. Samacheer Gallery count
  $q2 = mysqli_query($conn, "SELECT COUNT(*) FROM gallery_images WHERE school='samacheer' AND is_active=1");
  $sam_gal_count = mysqli_fetch_row($q2)[0] ?? 0;

  // 3. CBSE Gallery count
  $q3 = mysqli_query($conn, "SELECT COUNT(*) FROM gallery_images WHERE school='cbse' AND is_active=1");
  $cbse_gal_count = mysqli_fetch_row($q3)[0] ?? 0;

  $q4 = mysqli_query($conn, "SELECT COUNT(*) FROM page_contents");
  $content_count = mysqli_fetch_row($q4)[0] ?? 0;

  $q5 = mysqli_query($conn, "SELECT COUNT(*) FROM school_videos WHERE is_active=1");
  $video_count = mysqli_fetch_row($q5)[0] ?? 0;

  $q6_1 = @mysqli_query($conn, "SELECT COUNT(*) FROM admission_applications");
  $adm_count = $q6_1 ? (mysqli_fetch_row($q6_1)[0] ?? 0) : 0;
  $q6_2 = @mysqli_query($conn, "SELECT COUNT(*) FROM teacher_applications");
  $tch_count = $q6_2 ? (mysqli_fetch_row($q6_2)[0] ?? 0) : 0;
  $applications_count = $adm_count + $tch_count;

  $q7 = @mysqli_query($conn, "SELECT COUNT(*) FROM admin_users WHERE status='pending'");
  $pending_users_count = $q7 ? (mysqli_fetch_row($q7)[0] ?? 0) : 0;

  $q8 = @mysqli_query($conn, "SELECT COUNT(*) FROM testimonials");
  $testimonials_count = $q8 ? (mysqli_fetch_row($q8)[0] ?? 0) : 0;
}

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
  <title>Admin Dashboard – Sai Schools</title>
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
      --cream: #fdf6ec;
      --white: #fff;
      --green: #22c55e;
      --red: #ef4444;
      --blue: #3b82f6;
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
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      z-index: 100;
    }

    .sidebar-brand {
      padding: 28px 20px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .sidebar-brand .logo-icon {
      width: 48px;
      height: 48px;
      background: linear-gradient(135deg, var(--gold), var(--gold-lt));
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--deep);
      margin-bottom: 10px;
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
      letter-spacing: 0.08em;
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

    /* ── PROFILE SLIDE-IN PANEL ── */
    .topbar .admin-badge { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; color: #64748b; cursor: pointer; }
    .admin-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--navy), var(--gold)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.9rem; object-fit: cover; }
    .profile-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(10, 31, 68, 0.5); z-index: 1049; opacity: 0; visibility: hidden; transition: all 0.3s ease; }
    .profile-overlay.active { opacity: 1; visibility: visible; }
    .profile-panel { position: fixed; top: 0; right: -360px; width: 350px; height: 100vh; background: #fff; box-shadow: -4px 0 24px rgba(0,0,0,0.15); z-index: 1050; transition: right 0.3s ease; display: flex; flex-direction: column; }
    .profile-panel.active { right: 0; }
    .close-panel { position: absolute; top: 16px; right: 20px; background: none; border: none; font-size: 1.5rem; color: #64748b; cursor: pointer; transition: color 0.2s; }
    .close-panel:hover { color: var(--navy); }
    .panel-content { padding: 60px 24px 24px; text-align: center; }
    .panel-pic, .panel-pic-text { width: 100px; height: 100px; border-radius: 50%; margin: 0 auto 16px; border: 3px solid var(--gold); box-shadow: 0 4px 12px rgba(200, 147, 42, 0.2); }
    .panel-pic { object-fit: cover; }
    .panel-pic-text { background: linear-gradient(135deg, var(--navy), var(--deep)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-family: 'Playfair Display', serif; }
    .panel-content h3 { font-size: 1.4rem; color: var(--navy); margin-bottom: 4px; }
    .panel-email { font-size: 0.9rem; color: #64748b; margin-bottom: 24px; }
    .panel-details { background: #f8fafc; border-radius: 12px; padding: 16px; text-align: left; margin-bottom: 30px; }
    .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem; }
    .detail-row:last-child { border-bottom: none; }
    .detail-row span { color: #64748b; }
    .detail-row strong { color: var(--navy); }
    .status-badge { padding: 4px 10px; border-radius: 99px; font-size: 0.75rem; background: #e2e8f0; color: #475569; }
    .status-approved { background: #dcfce7; color: #166534; }
    .panel-logout { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 14px; border-radius: 10px; background: #fee2e2; color: #b91c1c; text-decoration: none; font-weight: 700; transition: all 0.2s; }
    .panel-logout:hover { background: #fecaca; }

    /* ── MAIN CONTENT ── */
    .main {
      margin-left: var(--sidebar-w);
      flex: 1;
      display: flex;
      flex-direction: column;
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

    .content {
      padding: 28px;
    }

    /* ── STAT CARDS ── */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-bottom: 28px;
    }

    .stat-card {
      background: #fff;
      border-radius: 14px;
      padding: 24px;
      box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
      display: flex;
      align-items: center;
      gap: 18px;
      border-left: 4px solid var(--gold);
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-card:nth-child(2) {
      border-left-color: #3b82f6;
    }

    .stat-card:nth-child(3) {
      border-left-color: #22c55e;
    }

    .stat-card:nth-child(4) {
      border-left-color: #a855f7;
      /* Purple color for Content Manager */
    }

    .stat-icon {
      width: 52px;
      height: 52px;
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

    .stat-info {
      flex: 1;
    }

    .stat-num {
      font-size: 2rem;
      font-weight: 700;
      color: var(--navy);
      line-height: 1;
    }

    .stat-label {
      font-size: 0.8rem;
      color: #64748b;
      margin-top: 4px;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }

    /* ── ACTION CARDS ── */
    .section-title {
      font-size: 1rem;
      font-weight: 700;
      color: var(--navy);
      margin-bottom: 16px;
    }

    .action-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 28px;
    }

    .action-card {
      background: #fff;
      border-radius: 14px;
      padding: 28px 24px;
      box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06);
      text-align: center;
      text-decoration: none;
      transition: all 0.25s;
      border: 2px solid transparent;
    }

    .action-card:hover {
      border-color: var(--gold);
      box-shadow: 0 8px 24px rgba(200, 147, 42, 0.15);
      transform: translateY(-3px);
    }

    .action-card .ac-icon {
      font-size: 2.5rem;
      margin-bottom: 12px;
    }

    .action-card h3 {
      color: var(--navy);
      font-size: 1rem;
      margin-bottom: 6px;
    }

    .action-card p {
      color: #64748b;
      font-size: 0.82rem;
      line-height: 1.5;
    }

    .action-btn {
      display: inline-block;
      margin-top: 16px;
      padding: 8px 20px;
      border-radius: 8px;
      background: linear-gradient(135deg, var(--gold), #e8a030);
      color: var(--deep);
      font-weight: 700;
      font-size: 0.82rem;
    }

    /* ── QUICK LINKS ── */
    .quick-row {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .quick-link {
      padding: 10px 18px;
      border-radius: 8px;
      background: #f1f5f9;
      color: var(--navy);
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
      border: 1px solid #e2e8f0;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .quick-link:hover {
      background: var(--navy);
      color: #fff;
    }

    .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; color: var(--navy); cursor: pointer; padding: 0 10px 0 0; }
    .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(10, 31, 68, 0.5); z-index: 90; opacity: 0; transition: opacity 0.3s; }
    .sidebar-overlay.active { display: block; opacity: 1; }

    @media(max-width:1100px) {
      .stats-row {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    @media(max-width:900px) {

      .stats-row,
      .action-grid {
        grid-template-columns: 1fr 1fr;
      }

      .sidebar {
        width: 200px;
      }

      .main {
        margin-left: 200px;
      }
    }

    /* ── ANIMATIONS ── */
    @keyframes fadeSlideUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .topbar { animation: fadeInDown 0.5s ease forwards; }
    .content > *, .quick-row, .section-title { animation: fadeSlideUp 0.5s ease forwards; opacity: 0; }
    .content > *:nth-child(1) { animation-delay: 0.1s; }
    .content > *:nth-child(2) { animation-delay: 0.2s; }
    .content > *:nth-child(3) { animation-delay: 0.3s; }
    .content > *:nth-child(4) { animation-delay: 0.4s; }
    .content > *:nth-child(5) { animation-delay: 0.5s; }
    .content > *:nth-child(6) { animation-delay: 0.6s; }

    @media(max-width:768px) {
      .admin-badge span { display: none; }
      .mobile-menu-btn {
        display: block;
      }

      .sidebar {
        position: fixed;
        left: -100%;
        width: 260px;
        transition: left 0.3s ease;
      }
      .sidebar.open { left: 0; }

      .main {
        margin-left: 0;
      }

      .stats-row,
      .action-grid {
        grid-template-columns: 1fr;
      }
      .topbar { padding: 0 16px; }
      .topbar h1 { font-size: 0.95rem; }
      .topbar > div { gap: 12px !important; }
    }

    @media(max-width:480px) {
      .topbar { padding: 0 12px; }
      .topbar h1 { font-size: 0.8rem; }
      .topbar > div { gap: 8px !important; }
      .admin-avatar { width: 32px; height: 32px; font-size: 0.8rem; }
    }

    /* ── BACK TO TOP ── */
    .back-to-top { position: fixed; bottom: 20px; right: 20px; width: 44px; height: 44px; background: var(--gold); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; border: none; cursor: pointer; opacity: 0; visibility: hidden; transition: all 0.3s; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
    .back-to-top.show { opacity: 1; visibility: visible; }
    .back-to-top:hover { background: #b07d20; transform: translateY(-3px); }
  </style>
</head>

<body>

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
      <a href="index.php" class="nav-item active">
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
        <span>🚪</span> Logout
      </a>
    </div>
  </aside>

  <!-- ═══ MAIN ═══ -->
  <div class="main">

    <!-- Top Bar -->
    <header class="topbar">
      <div style="display:flex; align-items:center; gap:16px;">
        <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
        <h1>Dashboard</h1>
      </div>
      <div class="admin-badge" id="profileToggle">
        <span>Welcome, <strong><?= htmlspecialchars($user_data['username']) ?></strong></span>
        <?php if (!empty($user_data['profile_pic'])): ?>
            <img src="<?= htmlspecialchars($user_data['profile_pic']) ?>" alt="Avatar" class="admin-avatar">
        <?php else: ?>
            <div class="admin-avatar"><?= strtoupper(substr($user_data['username'], 0, 1)) ?></div>
        <?php endif; ?>
      </div>
    </header>

    <div class="content">

      <div class="stats-row">

        <div class="stat-card">
          <div class="stat-icon">🖼️</div>
          <div class="stat-info">
            <div class="stat-num"><?= $slides_count ?></div>
            <div class="stat-label">Active Hero Slides</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">📸</div>
          <div class="stat-info">
            <div class="stat-num"><?= $sam_gal_count ?></div>
            <div class="stat-label">Samacheer Gallery</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">🏫</div>
          <div class="stat-info">
            <div class="stat-num"><?= $cbse_gal_count ?></div>
            <div class="stat-label">CBSE Gallery</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">📝</div>
          <div class="stat-info">
            <div class="stat-num"><?= $content_count ?></div>
            <div class="stat-label">Text Contents</div>
          </div>
        </div>

        <div class="stat-card" style="border-left-color:#ef4444;">
          <div class="stat-icon" style="background:linear-gradient(135deg,rgba(239,68,68,0.12),rgba(239,68,68,0.06));">
            ▶️</div>
          <div class="stat-info">
            <div class="stat-num"><?= $video_count ?></div>
            <div class="stat-label">Active Videos</div>
          </div>
        </div>

        <div class="stat-card" style="border-left-color:#f97316;">
          <div class="stat-icon" style="background:linear-gradient(135deg,rgba(249,115,22,0.12),rgba(249,115,22,0.06));">
            📋</div>
          <div class="stat-info">
            <div class="stat-num"><?= $applications_count ?></div>
            <div class="stat-label">Total Applications</div>
          </div>
        </div>

        <div class="stat-card" style="border-left-color:#06b6d4;">
          <div class="stat-icon" style="background:linear-gradient(135deg,rgba(6,182,212,0.12),rgba(6,182,212,0.06));">
            👥</div>
          <div class="stat-info">
            <div class="stat-num"><?= $pending_users_count ?></div>
            <div class="stat-label">Pending Users</div>
          </div>
        </div>

        <div class="stat-card" style="border-left-color:#ec4899;">
          <div class="stat-icon" style="background:linear-gradient(135deg,rgba(236,72,153,0.12),rgba(236,72,153,0.06));">
            💬</div>
          <div class="stat-info">
            <div class="stat-num"><?= $testimonials_count ?></div>
            <div class="stat-label">Testimonials</div>
          </div>
        </div>

      </div>
      <p class="section-title">⚡ Quick Actions</p>
      <div class="action-grid">

        <a href="slides.php" class="action-card">
          <div class="ac-icon">🖼️</div>
          <h3>Hero Slides</h3>
          <p>Main page slider images, titles, descriptions Add / Edit / Delete.</p>
          <span class="action-btn">Manage Slides →</span>
        </a>

        <a href="gallery.php?school=samacheer" class="action-card">
          <div class="ac-icon">📸</div>
          <h3>Samacheer Gallery</h3>
          <p>Samacheer school gallery photos add / remove.</p>
          <span class="action-btn">Manage Gallery →</span>
        </a>

        <a href="gallery.php?school=cbse" class="action-card">
          <div class="ac-icon">🏫</div>
          <h3>CBSE Gallery</h3>
          <p>CBSE school gallery photos add / remove.</p>
          <span class="action-btn">Manage Gallery →</span>
        </a>

        <a href="content_manager.php" class="action-card">
          <div class="ac-icon">📝</div>
          <h3>Content Manager</h3>
          <p>Home, Samacheer, CBSE pages-oda text content add / edit pannalam.</p>
          <span class="action-btn">Manage Content →</span>
        </a>

        <a href="videos.php?school=samacheer" class="action-card">
          <div class="ac-icon">▶️</div>
          <h3>Samacheer Videos</h3>
          <p>Samacheer school YouTube videos add / edit / remove.</p>
          <span class="action-btn">Manage Videos →</span>
        </a>

        <a href="videos.php?school=cbse" class="action-card">
          <div class="ac-icon">🎬</div>
          <h3>CBSE Videos</h3>
          <p>CBSE school YouTube videos add / edit / remove.</p>
          <span class="action-btn">Manage Videos →</span>
        </a>

        <a href="testimonials.php" class="action-card">
          <div class="ac-icon">💬</div>
          <h3>Testimonials</h3>
          <p>Manage student, parent, and teacher reviews shown on the home page.</p>
          <span class="action-btn">Manage Testimonials →</span>
        </a>

        <a href="applications.php" class="action-card">
          <div class="ac-icon">📋</div>
          <h3>Applications</h3>
          <p>View and manage admission inquiries and teacher job applications.</p>
          <span class="action-btn">View Applications →</span>
        </a>

        <?php if ($is_admin): ?>
        <a href="approve-users.php" class="action-card">
          <div class="ac-icon">👥</div>
          <h3>User Approvals</h3>
          <p>Approve, reject, or manage pending admin and user registrations.</p>
          <span class="action-btn">Manage Users →</span>
        </a>
        <?php endif; ?>

      </div><!-- /action-grid -->
    </div><!-- /content -->

    <!-- Quick Preview Links -->
    <p class="section-title" style="padding: 0 28px;">🌐 Preview Pages</p>
    <div class="quick-row" style="padding: 0 28px 28px;">
      <a href="../index.php" target="_blank" class="quick-link">🏠 Main Site</a>
      <a href="../samacheer.php" target="_blank" class="quick-link">📖 Samacheer Page</a>
      <a href="../cbse.php" target="_blank" class="quick-link">📚 CBSE Page</a>
    </div>

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
                  <strong><span class="status-badge <?= ($user_data['status'] ?? '') === 'approved' ? 'status-approved' : '' ?>">
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