<?php
session_start();
require_once 'db.php';

// ============================================================
//  REPLACE YOUR GOOGLE CLIENT ID & SECRET HERE
// ============================================================
define('GOOGLE_CLIENT_ID', 'your-id');
define('GOOGLE_CLIENT_SECRET', 'your-secret');
define('GOOGLE_REDIRECT_URI', 'http://localhost/SAI%20SCHOOL/admin/google-callback.php');
// For live server: define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/admin/google-callback.php');
// ============================================================

if (!isset($_GET['code'])) {
    header('Location: login.php?error=google_failed');
    exit;
}

// Step 1: Exchange code for access token
$token_url = 'https://oauth2.googleapis.com/token';
$token_data = http_build_query([
    'code' => $_GET['code'],
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code',
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $token_data,
    ]
]);

$token_response = file_get_contents($token_url, false, $context);
$token_json = json_decode($token_response, true);

if (!isset($token_json['access_token'])) {
    header('Location: login.php?error=token_failed');
    exit;
}

// Step 2: Get user info from Google
$user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
$user_context = stream_context_create([
    'http' => [
        'header' => 'Authorization: Bearer ' . $token_json['access_token'],
    ]
]);

$user_response = file_get_contents($user_info_url, false, $user_context);
$user = json_decode($user_response, true);

if (!isset($user['email'])) {
    header('Location: login.php?error=userinfo_failed');
    exit;
}

$google_id = mysqli_real_escape_string($conn, $user['id']);
$email = mysqli_real_escape_string($conn, $user['email']);
$name = mysqli_real_escape_string($conn, $user['name'] ?? $user['email']);
$profile_pic = mysqli_real_escape_string($conn, $user['picture'] ?? '');

// Step 3: Check if user already exists
$check = mysqli_query($conn, "SELECT * FROM admin_users WHERE google_id='$google_id' OR email='$email'");

if (mysqli_num_rows($check) > 0) {
    // Existing user — check status
    $existing = mysqli_fetch_assoc($check);

    if ($existing['status'] === 'approved') {
        // ✅ Approved — login
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $existing['username'] ?? $name;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_pic'] = $existing['profile_pic'] ?? $profile_pic;
        $_SESSION['auth_type'] = 'google';
        $_SESSION['admin_role'] = $existing['role'] ?? 'user';

        // Update profile pic if changed
        mysqli_query($conn, "UPDATE admin_users SET profile_pic='$profile_pic', google_id='$google_id' WHERE id={$existing['id']}");

        if (($_SESSION['admin_role']) === 'admin') {
            header('Location: index.php');
        } else {
            header('Location: ../user/dashboard.php');
        }
        exit;

    } elseif ($existing['status'] === 'pending') {
        header('Location: login.php?error=pending');
        exit;
    } else {
        header('Location: login.php?error=rejected');
        exit;
    }

} else {
    // New user — register as approved & auto-login directly
    $username = strtolower(explode('@', $email)[0]);
    $ucheck = mysqli_query($conn, "SELECT id FROM admin_users WHERE username='$username'");
    if (mysqli_num_rows($ucheck) > 0) {
        $username = $username . '_' . rand(100, 999);
    }
    $username = mysqli_real_escape_string($conn, $username);

    $insert = "INSERT INTO admin_users (username, email, google_id, profile_pic, auth_type, status, role, password) 
               VALUES ('$username', '$email', '$google_id', '$profile_pic', 'google', 'approved', 'user', '')";

    if (mysqli_query($conn, $insert)) {
        // Auto-login immediately
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_pic'] = $profile_pic;
        $_SESSION['auth_type'] = 'google';
        $_SESSION['admin_role'] = 'user';
        header('Location: ../user/dashboard.php');
    } else {
        header('Location: login.php?error=db_error');
    }
    exit;
}
?>