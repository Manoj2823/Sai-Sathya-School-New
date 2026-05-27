<?php
require_once 'Auth guard.php';
require_once 'db.php';

$username = $_SESSION['admin_username'] ?? 'User';
$msg = '';
$msg_type = '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Gracefully add is_active column if it doesn't exist
$check_col = @mysqli_query($conn, "SHOW COLUMNS FROM page_contents LIKE 'is_active'");
if ($check_col && mysqli_num_rows($check_col) == 0) {
    @mysqli_query($conn, "ALTER TABLE page_contents ADD COLUMN is_active TINYINT(1) DEFAULT 1");
}

// ── EDIT SAVE (user can edit text — cannot add/delete) ────────
if ($action === 'edit_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $section_title = mysqli_real_escape_string($conn, trim($_POST['section_title'] ?? ''));
    $content_text = mysqli_real_escape_string($conn, trim($_POST['content_text'] ?? ''));
    $page_name = mysqli_real_escape_string($conn, trim($_POST['page_name'] ?? ''));
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($section_title && $content_text) {
        mysqli_query($conn, "UPDATE page_contents SET section_title='$section_title', content_text='$content_text', page_name='$page_name', is_active=$is_active WHERE id=$id");
        $msg = '✅ Content updated successfully!';
        $msg_type = 'success';

        // ── NOTIFY ADMINS ──
        $admins = mysqli_query($conn, "SELECT email FROM admin_users WHERE role='admin' AND status='approved' AND email != ''");
        $admin_emails = [];
        while ($a = mysqli_fetch_assoc($admins)) {
            $admin_emails[] = $a['email'];
        }
        if (!empty($admin_emails)) {
            $to = implode(', ', $admin_emails);
            $subject = "Content Modified by User: $username";
            $body = "Hello Admin,\n\nThe user '$username' has modified a Page Content section (ID: $id - $section_title).\n\nPlease log in to the admin panel to review the changes.\n\nBest regards,\nSai Schools System";
            $headers = "From: noreply@sathyasaischools.org\r\n";
            @mail($to, $subject, $body, $headers);
        }
    } else {
        $msg = '❌ All fields required.';
        $msg_type = 'error';
    }
}

// ── FETCH EDIT ROW ────────────────────────────────────────────
$edit_row = null;
if (isset($_GET['edit'])) {
    $edit_res = mysqli_query($conn, "SELECT * FROM page_contents WHERE id=" . (int) $_GET['edit']);
    $edit_row = mysqli_fetch_assoc($edit_res);
}

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

// User data
$esc_user = mysqli_real_escape_string($conn, $username);
$user_res = @mysqli_query($conn, "SELECT * FROM admin_users WHERE username='$esc_user' LIMIT 1");
$user_data = ($user_res && mysqli_num_rows($user_res) > 0)
    ? mysqli_fetch_assoc($user_res)
    : ['username' => $username, 'email' => $_SESSION['admin_email'] ?? '', 'profile_pic' => $_SESSION['admin_pic'] ?? '', 'auth_type' => $_SESSION['auth_type'] ?? 'manual', 'status' => 'approved'];

// Group by page
$pages = [];
if ($contents_res) {
    while ($r = mysqli_fetch_assoc($contents_res)) {
        $pages[$r['page_name']][] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Page Content – User Panel</title>
    <link rel="icon" type="image/png" href="../images/academiya_heading_logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0a1f44;
            --gold: #c8932a;
            --blue: #3b82f6;
            --deep: #071530;
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

        body {
            font-family: 'Lato', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .topbar {
            background: linear-gradient(135deg, var(--deep), #0d2960);
            padding: 0 28px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .2);
        }

        .topbar h1 {
            color: #fff;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .role-badge {
            background: rgba(59, 130, 246, .25);
            border: 1px solid rgba(59, 130, 246, .5);
            color: #93c5fd;
            font-size: .72rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .back-link {
            color: rgba(255, 255, 255, .7);
            text-decoration: none;
            font-size: .85rem;
            transition: .2s;
        }

        .back-link:hover {
            color: #fff;
        }

        .admin-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .admin-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), var(--navy));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
            object-fit: cover;
        }

        .content {
            max-width: 1100px;
            margin: 28px auto;
            padding: 0 20px;
        }

        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: .9rem;
        }

        .alert.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

    .badge {
        padding: 3px 8px;
        border-radius: 99px;
        font-size: .7rem;
        font-weight: 700;
    }
    .badge.active { background: #dcfce7; color: #166534; }
    .badge.inactive { background: #f1f5f9; color: #64748b; }

        .user-notice {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-left: 4px solid var(--gold);
            border-radius: 8px;
            padding: 10px 16px;
            color: #92400e;
            font-size: .84rem;
            margin-bottom: 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .07);
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f4ff;
        }

        .page-group {
            margin-bottom: 24px;
        }

        .page-label {
            font-size: .82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--gold);
            margin-bottom: 12px;
            padding: 6px 12px;
            background: #fff8ec;
            border-radius: 6px;
            display: inline-block;
        }

        .content-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 12px;
            transition: all .2s;
        }

        .content-item:hover {
            border-color: var(--gold);
            box-shadow: 0 2px 8px rgba(200, 147, 42, .1);
        }

        .content-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .content-section-title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--navy);
        }

        .content-text {
            font-size: .85rem;
            color: #475569;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        .btn-sm {
            padding: 5px 11px;
            border-radius: 6px;
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all .2s;
        }

        .btn-edit-sm {
            background: var(--blue);
            color: #fff;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.25);
        }

        .btn-edit-sm:hover {
            background: #2563eb;
        }

        .no-delete-note {
            font-size: .72rem;
            color: #94a3b8;
            font-style: italic;
            margin-top: 4px;
        }

        /* edit form */
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

        .form-group label {
            font-size: .83rem;
            font-weight: 700;
            color: var(--navy);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: .9rem;
            outline: none;
            font-family: 'Lato', sans-serif;
            transition: border .2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--gold);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            padding: 10px 22px;
            border: none;
            border-radius: 8px;
            font-size: .9rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold), #e8a030);
            color: #fff;
        }

        .btn-primary:hover {
            opacity: .9;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        /* profile panel */
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
            width: 340px;
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
            font-size: 1.4rem;
            color: #64748b;
            cursor: pointer;
        }

        .panel-content {
            padding: 56px 22px 22px;
            text-align: center;
        }

        .panel-pic,
        .panel-pic-text {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            margin: 0 auto 14px;
            border: 3px solid var(--blue);
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
            font-size: 2rem;
        }

        .panel-content h3 {
            font-size: 1.2rem;
            color: var(--navy);
            margin-bottom: 4px;
        }

        .panel-email {
            font-size: .85rem;
            color: #64748b;
            margin-bottom: 18px;
        }

        .panel-details {
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px;
            text-align: left;
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: .85rem;
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

        .panel-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
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

        @media(max-width:640px) {
            .topbar {
                padding: 0 16px;
            }

            .topbar h1 {
                font-size: 0.85rem;
            }

            .admin-badge span {
                display: none;
            }

            .role-badge {
                display: none;
            }

            .back-link {
                font-size: 0.75rem;
            }

            .admin-avatar {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 20px 16px;
            }

            .search-bar {
                flex-wrap: wrap;
            }
        }

        @media(max-width:480px) {
            .topbar {
                padding: 0 12px;
            }

            .topbar h1 {
                font-size: 0.78rem;
            }

            .topbar-right {
                gap: 8px;
            }

            .back-link {
                padding: 5px 8px;
                font-size: 0.7rem;
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
                font-size: 0.7rem;
            }

            .back-link {
                font-size: 0.65rem;
            }
        }
    </style>
</head>

<body>

    <header class="topbar">
        <h1>📝 Page Content</h1>
        <div class="topbar-right">
            <span class="role-badge">👤 USER</span>
            <a href="dashboard.php" class="back-link">← Dashboard</a>
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

        <div class="user-notice">ℹ️ <strong>User Access:</strong> Edit section title and content text. Add and Delete
            are admin-only.</div>

        <?php if ($edit_row): ?>
            <!-- ── EDIT FORM ── -->
            <div class="card">
                <div class="card-title">✏️ Edit Content — #<?= $edit_row['id'] ?></div>
                <form method="POST" action="content.php"
                    onsubmit="return confirm('Are you sure you want to save these changes?');">
                    <input type="hidden" name="action" value="edit_save">
                    <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>📄 Page</label>
                            <select name="page_name">
                                <?php foreach (['home', 'samacheer', 'cbse', 'about'] as $pg): ?>
                                    <option value="<?= $pg ?>" <?= ($edit_row['page_name'] ?? '') === $pg ? 'selected' : '' ?>>
                                        <?= ucfirst($pg) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>📌 Section Title</label>
                            <input type="text" name="section_title"
                                value="<?= htmlspecialchars($edit_row['section_title'] ?? '') ?>" required>
                        </div>
                        <div class="form-group full">
                            <label>✍️ Content Text</label>
                            <textarea name="content_text" rows="5"
                                required><?= htmlspecialchars($edit_row['content_text'] ?? '') ?></textarea>
                        <?php if ($edit_row): ?>
                            <label style="margin-top:14px; display:flex; align-items:center; gap:8px; cursor:pointer; text-transform:none;">
                                <input type="checkbox" name="is_active" <?= (!isset($edit_row['is_active']) || $edit_row['is_active']) ? 'checked' : '' ?>>
                                Active (shown on website)
                            </label>
                        <?php endif; ?>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;margin-top:18px;">
                        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                        <a href="content.php" class="btn btn-secondary">✕ Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- ── CONTENT LIST ── -->
        <form method="GET" style="display:flex; gap:10px; margin-bottom: 24px; flex-wrap:wrap;">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search title or text..."
                style="flex:1; max-width:300px; padding:10px 12px; border:1.5px solid #e2e8f0; border-radius:8px; outline:none; font-family:'Lato',sans-serif;">
            <button type="submit" class="btn btn-primary" style="padding:10px 20px;">Search</button>
            <?php if ($q !== ''): ?>
                <a href="content.php" class="btn btn-secondary" style="padding:10px 20px;">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (empty($pages)): ?>
            <div class="card">
                <p style="text-align:center;color:#94a3b8;padding:40px 0;">
                    <?= $q ? 'No matching content found for your search.' : 'No content yet. Ask admin to add content.' ?>
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($pages as $page_name => $items): ?>
                <div class="page-group">
                    <div class="page-label">📄 <?= htmlspecialchars(ucfirst($page_name)) ?> Page</div>
                    <?php foreach ($items as $row): ?>
                        <div class="content-item">
                            <div class="content-item-header">
                            <div class="content-section-title">
                                <?= htmlspecialchars($row['section_title']) ?>
                                <span class="badge <?= (!isset($row['is_active']) || $row['is_active']) ? 'active' : 'inactive' ?>" style="margin-left:8px; vertical-align:middle;">
                                    <?= (!isset($row['is_active']) || $row['is_active']) ? 'Active' : 'Hidden' ?>
                                </span>
                            </div>
                                <a href="content.php?edit=<?= $row['id'] ?>" class="btn-sm btn-edit-sm">✏️ Edit</a>
                            </div>
                            <div class="content-text"><?= htmlspecialchars($row['content_text']) ?></div>
                            <div class="no-delete-note">Delete: Admin only</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $q_param ?>" class="page-btn">« Prev</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $q_param ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $q_param ?>" class="page-btn">Next »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Profile Panel -->
    <div class="profile-overlay" id="profileOverlay"></div>
    <div class="profile-panel" id="profilePanel">
        <button class="close-panel" id="closeProfile">✕</button>
        <div class="panel-content">
            <?php if (!empty($user_data['profile_pic'])): ?>
                <img src="<?= htmlspecialchars($user_data['profile_pic']) ?>" alt="Profile" class="panel-pic">
            <?php else: ?>
                <div class="panel-pic-text"><?= strtoupper(substr($user_data['username'], 0, 1)) ?></div>
            <?php endif; ?>
            <h3><?= htmlspecialchars($user_data['username']) ?></h3>
            <p class="panel-email"><?= htmlspecialchars($user_data['email'] ?? '') ?></p>
            <div class="panel-details">
                <div class="detail-row"><span>Role:</span><strong>User</strong></div>
                <div class="detail-row">
                    <span>Login:</span><strong><?= htmlspecialchars(ucfirst($user_data['auth_type'] ?? 'manual')) ?></strong>
                </div>
            </div>
            <a href="../admin/logout.php" class="panel-logout">🚪 Logout</a>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const t = document.getElementById('profileToggle'), p = document.getElementById('profilePanel'), o = document.getElementById('profileOverlay'), c = document.getElementById('closeProfile');
            const open = () => { p.classList.add('active'); o.classList.add('active'); };
            const shut = () => { p.classList.remove('active'); o.classList.remove('active'); };
            t.addEventListener('click', open); c.addEventListener('click', shut); o.addEventListener('click', shut);
        });
    </script>
</body>

</html>