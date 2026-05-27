<?php
session_start();
require_once 'db.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Fetch the latest user details from the database based on email
$email = mysqli_real_escape_string($conn, $_SESSION['admin_email']);
$query = "SELECT * FROM admin_users WHERE email = '$email' LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
} else {
    // Fallback to session data if not found in DB
    $user_data = [
        'username' => $_SESSION['admin_username'] ?? 'Admin',
        'email' => $_SESSION['admin_email'] ?? 'Unknown',
        'profile_pic' => $_SESSION['admin_pic'] ?? '',
        'auth_type' => $_SESSION['auth_type'] ?? 'local',
        'status' => 'approved'
    
];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            color: #2c2c2c;
        }

        .profile-container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(10, 31, 68, 0.1);
            text-align: center;
        }

        .profile-container h2 {
            color: #0a1f44;
            margin-top: 0;
            margin-bottom: 24px;
        }

        .profile-pic {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #c8932a;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(200, 147, 42, 0.3);
        }

        .profile-details {
            text-align: left;
            margin-top: 20px;
            background: #fdf6ec;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid rgba(200, 147, 42, 0.2);
        }

        .profile-details p {
            font-size: 1rem;
            color: #444;
            border-bottom: 1px solid rgba(10, 31, 68, 0.05);
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .profile-details p:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .profile-details p strong {
            display: inline-block;
            width: 130px;
            color: #0a1f44;
        }

        .back-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #0a1f44, #0d2960);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(10, 31, 68, 0.3);
            background: linear-gradient(135deg, #c8932a, #e8a030);
            color: #071530;
        }

        @media(max-width:480px) {
            body { padding: 14px; }
            .profile-container { padding: 28px 20px; margin: 20px auto; }
            .profile-pic { width: 110px; height: 110px; font-size: 48px; }
            .profile-container h2 { font-size: 1.3rem; }
            .profile-details p { font-size: 0.9rem; }
            .profile-details p strong { width: 110px; }
        }

        @media(max-width:360px) {
            body { padding: 10px; }
            .profile-container { padding: 20px 14px; }
            .profile-pic { width: 90px; height: 90px; font-size: 40px; }
            .profile-container h2 { font-size: 1.15rem; }
            .profile-details p { font-size: 0.83rem; flex-direction: column; }
            .profile-details p strong { width: auto; margin-bottom: 2px; }
            .back-btn { font-size: 0.85rem; padding: 10px 18px; }
        }
    </style>
</head>

<body>

    <div class="profile-container">
        <h2>My Profile</h2>

        <?php if (!empty($user_data['profile_pic'])): ?>
            <img src="<?= htmlspecialchars($user_data['profile_pic']) ?>" alt="Profile Picture" class="profile-pic">
        <?php else: ?>
            <div class="profile-pic"
                style="background-color: #0a1f44; display: inline-flex; align-items: center; justify-content: center; font-size: 60px; color: #fff; font-family: 'Playfair Display', serif;">
                <?= strtoupper(substr($user_data['username'], 0, 1)) ?>
            </div>
        <?php endif; ?>

        <div class="profile-details">
            <p><strong>Username:</strong> <?= htmlspecialchars($user_data['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user_data['email']) ?></p>
            <p><strong>Role:</strong>
                <?= htmlspecialchars(ucfirst($user_data['role'] ?? $_SESSION['admin_role'] ?? 'Admin')) ?></p>
            <p><strong>Login Type:</strong> <?= htmlspecialchars(ucfirst($user_data['auth_type'])) ?></p>
            <p><strong>Account Status:</strong> <?= htmlspecialchars(ucfirst($user_data['status'])) ?></p>
        </div>

        <a href="index.php" class="back-btn">← Back to Dashboard</a>
    </div>

</body>

</html>