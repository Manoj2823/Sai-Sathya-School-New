<?php
session_start();
require_once 'db.php';

// ============================================================
//  REPLACE YOUR GOOGLE CLIENT ID HERE
// ============================================================
define('GOOGLE_CLIENT_ID', '1092959896540-cthmm7u6cfhu4hmak52e6ihuitsqpasi.apps.googleusercontent.com');
define('GOOGLE_REDIRECT_URI', 'http://localhost/SAI%20SCHOOL/admin/google-callback.php');
// ============================================================

// Already logged in → dashboard (only if index.php exists)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
  if (($_SESSION['admin_role'] ?? 'user') === 'admin') {
    header('Location: index.php');
  } else {
    header('Location: ../user/dashboard.php');
  }
  exit;
}

// Build Google OAuth URL
$google_auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
  'client_id' => GOOGLE_CLIENT_ID,
  'redirect_uri' => GOOGLE_REDIRECT_URI,
  'response_type' => 'code',
  'scope' => 'email profile',
  'access_type' => 'online',
  'prompt' => 'select_account',
]);

$error = '';

// Error messages from Google callback
$error_map = [
  'google_failed' => 'Google login failed. Please try again.',
  'token_failed' => 'Could not get token from Google. Try again.',
  'userinfo_failed' => 'Could not fetch your Google profile.',
  'pending' => '⏳ Your account is awaiting admin approval.',
  'rejected' => '❌ Your account has been rejected by admin.',
  'db_error' => 'Database error. Please contact admin.',
];
if (isset($_GET['error']) && isset($error_map[$_GET['error']])) {
  $error = $error_map[$_GET['error']];
}

$new_registration = isset($_GET['new']) && $_GET['new'] == '1';

// Manual login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = mysqli_real_escape_string($conn, trim($_POST['username']));
  $password = mysqli_real_escape_string($conn, trim($_POST['password']));

  if (empty($username) || empty($password)) {
    $error = 'Please enter both username and password.';
  } else {
    $query = "SELECT * FROM admin_users WHERE username='$username' AND password='$password' AND auth_type='manual' AND status='approved'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
      $row = mysqli_fetch_assoc($result);
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_username'] = $username;
      $_SESSION['admin_email'] = $row['email'] ?? '';
      $_SESSION['admin_pic'] = '';
      $_SESSION['auth_type'] = 'manual';
      $_SESSION['admin_role'] = $row['role'] ?? 'user';
      // Redirect based on role
      if (($_SESSION['admin_role']) === 'admin') {
        header('Location: index.php');
      } else {
        header('Location: ../user/dashboard.php');
      }
      exit;
    } else {
      $error = 'Invalid credentials or account not approved!';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login – Sai Schools</title>
  <link rel="icon" type="image/png" href="../images/academiya_heading_logo.png">
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --navy: #0a1f44;
      --gold: #c8932a;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Lato', sans-serif;
      background: linear-gradient(135deg, #0a1f44 0%, #1a3a6e 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .login-card {
      background: #fff;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      width: 100%;
      max-width: 420px;
      animation: slideUp 0.4s ease;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .logo-wrap {
      text-align: center;
      margin-bottom: 20px;
    }

    .logo-wrap img {
      width: 65px;
      height: auto;
    }

    .login-card h2 {
      color: var(--navy);
      margin-bottom: 6px;
      text-align: center;
      font-size: 1.5rem;
    }

    .subtitle {
      text-align: center;
      color: #64748b;
      font-size: 0.85rem;
      margin-bottom: 24px;
    }

    /* Google Button */
    .btn-google {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      width: 100%;
      padding: 12px;
      background: #fff;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 0.95rem;
      font-weight: 700;
      color: #333;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s;
      margin-bottom: 20px;
    }

    .btn-google:hover {
      border-color: #4285F4;
      background: #f8faff;
      box-shadow: 0 2px 8px rgba(66, 133, 244, 0.2);
    }

    .btn-google svg {
      flex-shrink: 0;
    }

    /* Divider */
    .divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
      color: #94a3b8;
      font-size: 0.8rem;
    }

    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #e2e8f0;
    }

    /* Form */
    .form-group {
      margin-bottom: 15px;
      display: flex;
      flex-direction: column;
    }

    label {
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--navy);
      margin-bottom: 5px;
    }

    input[type=text],
    input[type=password] {
      padding: 12px;
      border: 1.5px solid #e2e8f0;
      border-radius: 8px;
      font-size: 1rem;
      outline: none;
      transition: border 0.2s;
    }

    input[type=text]:focus,
    input[type=password]:focus {
      border-color: var(--gold);
    }

    .btn-login {
      background: var(--gold);
      color: #fff;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      width: 100%;
      margin-top: 6px;
      transition: background 0.2s;
    }

    .btn-login:hover {
      background: #b07d20;
    }

    /* Alerts */
    .alert {
      padding: 12px;
      border-radius: 8px;
      font-size: 0.88rem;
      margin-bottom: 16px;
      text-align: center;
    }

    .alert.error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    .alert.info {
      background: #fef3c7;
      color: #92400e;
      border: 1px solid #fde68a;
    }

    .alert.success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    .register-link {
      text-align: center;
      margin-top: 20px;
      font-size: 0.88rem;
      color: #64748b;
    }

    .register-link a {
      color: var(--gold);
      font-weight: 700;
      text-decoration: none;
    }

    .register-link a:hover {
      text-decoration: underline;
    }

    @media(max-width:480px) {
      body {
        padding: 16px;
        align-items: flex-start;
        padding-top: 30px;
      }

      .login-card {
        padding: 28px 22px;
        border-radius: 12px;
      }

      .login-card h2 {
        font-size: 1.3rem;
      }

      .logo-wrap img {
        width: 52px;
      }

      input[type=text],
      input[type=password] {
        font-size: 0.95rem;
        padding: 10px;
      }

      .btn-login {
        font-size: 0.95rem;
      }

      .btn-google {
        font-size: 0.88rem;
        padding: 10px;
      }
    }

    @media(max-width:360px) {
      body {
        padding: 12px;
        padding-top: 20px;
      }

      .login-card {
        padding: 22px 16px;
      }

      .login-card h2 {
        font-size: 1.15rem;
      }

      .subtitle {
        font-size: 0.78rem;
      }

      input[type=text],
      input[type=password] {
        padding: 9px;
        font-size: 0.9rem;
      }

      label {
        font-size: 0.8rem;
      }
    }
  </style>
</head>

<body>

  <div class="login-card">
    <div class="logo-wrap">
      <img src="../images/samacheer_logo.png" alt="Sai Schools Logo">
    </div>
    <h2>Admin Login</h2>
    <p class="subtitle">Sign in to manage Sai Schools</p>

    <?php if ($new_registration): ?>
      <div class="alert info">
        ✅ Registered! Your account is <strong>awaiting admin approval</strong>. You'll be notified once approved.
      </div>
    <?php elseif ($error): ?>
      <div class="alert <?= (strpos($error, '⏳') !== false || strpos($error, '❌') !== false) ? 'info' : 'error' ?>">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- Google Sign-In -->
    <a href="<?= $google_auth_url ?>" class="btn-google">
      <!-- Google G Logo SVG -->
      <svg width="20" height="20" viewBox="0 0 48 48">
        <path fill="#EA4335"
          d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
        <path fill="#4285F4"
          d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
        <path fill="#FBBC05"
          d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
        <path fill="#34A853"
          d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
      </svg>
      Sign in with Google
    </a>

    <div class="divider">or sign in with username</div>

    <!-- Manual Login Form -->
    <form method="POST" action="">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter your username" minlength="4" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" minlength="6" required>
      </div>
      <button type="submit" class="btn-login">Login</button>
    </form>

    <div class="register-link">
      Don't have an account?
      <a href="register.php">Register Here</a>
    </div>
  </div>

</body>

</html>