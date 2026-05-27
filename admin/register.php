<?php
session_start();
require_once 'db.php';

define('GOOGLE_CLIENT_ID', '1092959896540-cthmm7u6cfhu4hmak52e6ihuitsqpasi.apps.googleusercontent.com');
define('GOOGLE_REDIRECT_URI', 'http://localhost/SAI%20SCHOOL/admin/google-callback.php');
define('ADMIN_SECRET_KEY', '79zKla3f1R7M2eXu65TfWw'); // 🔑 Change this to your own secret!

$google_auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
  'client_id' => GOOGLE_CLIENT_ID,
  'redirect_uri' => GOOGLE_REDIRECT_URI,
  'response_type' => 'code',
  'scope' => 'email profile',
  'access_type' => 'online',
  'prompt' => 'select_account',
]);

$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = mysqli_real_escape_string($conn, trim($_POST['username']));
  $password = trim($_POST['password']);
  $confirm_password = trim($_POST['confirm_password'] ?? '');
  $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
  $secret_key = trim($_POST['secret_key'] ?? '');

  if (empty($username) || empty($password) || empty($confirm_password)) {
    $msg = 'Please fill in all fields.';
    $msg_type = 'error';
  } elseif (strlen($username) < 4) {
    $msg = 'Username must be at least 4 characters.';
    $msg_type = 'error';
  } elseif (strlen($password) < 6) {
    $msg = 'Password must be at least 6 characters.';
    $msg_type = 'error';
  } elseif ($password !== $confirm_password) {
    $msg = 'Passwords do not match.';
    $msg_type = 'error';
  } elseif ($role === 'admin' && $secret_key !== ADMIN_SECRET_KEY) {
    $msg = '❌ Invalid Admin Secret Key! Registering as User instead.';
    $msg_type = 'error';
    $role = 'user'; // Downgrade to user silently
  } else {
    $check = mysqli_query($conn, "SELECT id FROM admin_users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
      $msg = 'Username already exists! Try another one.';
      $msg_type = 'error';
    } else {
      $password_escaped = mysqli_real_escape_string($conn, $password);
      $insert = "INSERT INTO admin_users (username, password, auth_type, status, role) 
                       VALUES ('$username', '$password_escaped', 'manual', 'approved', '$role')";
      if (mysqli_query($conn, $insert)) {
        $msg = $role === 'admin'
          ? '✅ Admin Registered Successfully! You can now login.'
          : '✅ User Registered Successfully! You can now login.';
        $msg_type = 'success';
      } else {
        $msg = 'Error: ' . mysqli_error($conn);
        $msg_type = 'error';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register – Sai Schools Admin</title>
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

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Lato', sans-serif;
      background: linear-gradient(135deg, #0a1f44 0%, #1a3a6e 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .card {
      background: #fff;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      width: 100%;
      max-width: 440px;
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
    }

    h2 {
      color: var(--navy);
      text-align: center;
      font-size: 1.5rem;
      margin-bottom: 6px;
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
      text-decoration: none;
      transition: all 0.2s;
      margin-bottom: 20px;
    }

    .btn-google:hover {
      border-color: #4285F4;
      background: #f8faff;
      box-shadow: 0 2px 8px rgba(66, 133, 244, 0.2);
    }

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

    input:focus {
      border-color: var(--gold);
    }

    /* Role Selector */
    .role-selector {
      display: flex;
      gap: 12px;
      margin-bottom: 15px;
    }

    .role-option {
      flex: 1;
    }

    .role-option input[type=radio] {
      display: none;
    }

    .role-option label {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      cursor: pointer;
      font-size: 0.9rem;
      font-weight: 700;
      color: #64748b;
      transition: all 0.2s;
    }

    .role-option input[type=radio]:checked+label {
      border-color: var(--gold);
      background: #fffbf0;
      color: var(--navy);
    }

    .role-option label .role-icon {
      font-size: 1.3rem;
    }

    /* Secret Key Box — hidden by default */
    #secret-key-wrap {
      display: none;
      background: #fef3c7;
      border: 1.5px solid #fde68a;
      border-radius: 10px;
      padding: 14px;
      margin-bottom: 15px;
    }

    #secret-key-wrap label {
      color: #92400e;
    }

    #secret-key-wrap input {
      border-color: #fbbf24;
      background: #fffbeb;
    }

    #secret-key-wrap .key-hint {
      font-size: 0.78rem;
      color: #b45309;
      margin-top: 6px;
    }

    .btn {
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

    .btn:hover {
      background: #b07d20;
    }

    .alert {
      padding: 12px;
      border-radius: 8px;
      font-size: 0.88rem;
      margin-bottom: 16px;
      text-align: center;
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

    .note {
      background: #fef3c7;
      color: #92400e;
      border: 1px solid #fde68a;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 0.82rem;
      margin-bottom: 20px;
      text-align: center;
    }

    .login-link {
      text-align: center;
      margin-top: 20px;
      font-size: 0.88rem;
      color: #64748b;
    }

    .login-link a {
      color: var(--gold);
      font-weight: 700;
      text-decoration: none;
    }
  </style>
</head>

<body>
  <div class="card">
    <div class="logo-wrap">
      <img src="../images/samacheer_logo.png" alt="Sai Schools">
    </div>
    <h2>Create Account</h2>
    <p class="subtitle">Register to access Sai Schools panel</p>

    <?php if ($msg): ?>
      <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Google Register -->
    <a href="<?= $google_auth_url ?>" class="btn-google">
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
      Register with Google (as User)
    </a>

    <div class="note">💡 Registering with Google assigns the <strong>User role</strong>. For Admin access, manual
      registration is required.</div>

    <div class="divider">or register with username</div>

    <form method="POST" action="">
      <!-- Role Selector -->
      <div class="form-group">
        <label>Select Role</label>
        <div class="role-selector">
          <div class="role-option">
            <input type="radio" name="role" id="role-user" value="user" checked onchange="toggleSecretKey(this)">
            <label for="role-user">
              <span class="role-icon">👤</span> User
            </label>
          </div>
          <div class="role-option">
            <input type="radio" name="role" id="role-admin" value="admin" onchange="toggleSecretKey(this)">
            <label for="role-admin">
              <span class="role-icon">🛡️</span> Admin
            </label>
          </div>
        </div>
      </div>

      <!-- Secret Key (shown only for Admin) -->
      <div id="secret-key-wrap">
        <label>🔑 Admin Secret Key</label>
        <input type="password" name="secret_key" id="secret_key" placeholder="Enter admin secret key">
        <p class="key-hint">⚠️ If you don't have the Admin secret key, you will be registered as a User.</p>
      </div>

      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter username" minlength="4" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter password" minlength="6" required>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Confirm password" minlength="6" required>
      </div>

      <button type="submit" class="btn">Register</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="login.php">Login here</a>
    </div>
  </div>

  <script>
    function toggleSecretKey(radio) {
      const wrap = document.getElementById('secret-key-wrap');
      const keyInput = document.getElementById('secret_key');
      if (radio.value === 'admin') {
        wrap.style.display = 'block';
        keyInput.required = true;
      } else {
        wrap.style.display = 'none';
        keyInput.required = false;
        keyInput.value = '';
      }
    }
  </script>
</body>

</html>