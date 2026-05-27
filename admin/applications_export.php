<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

$type = ($_GET['type'] ?? 'admission') === 'teacher' ? 'teacher' : 'admission';
$school = $_GET['school'] ?? 'all';
$q = trim($_GET['q'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');

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

$filename = ($type === 'admission' ? 'admission_inquiries' : 'teacher_applications') . '_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

if ($type === 'admission') {
    fputcsv($out, ['ID', 'School', 'Student Name', 'Date of Birth', 'Class', 'Gender', 'Parent Name', 'Phone', 'Email', 'Address', 'Submitted At']);
    $res = mysqli_query($conn, "SELECT * FROM admission_applications WHERE $where_sql ORDER BY created_at DESC");
    while ($row = mysqli_fetch_assoc($res)) {
        fputcsv($out, [
            $row['id'],
            $row['school'],
            $row['student_name'],
            $row['date_of_birth'],
            $row['class_applied'],
            $row['gender'],
            $row['parent_name'],
            $row['phone'],
            $row['email'],
            $row['address'],
            $row['created_at'],
        ]);
    }
} else {
    fputcsv($out, ['ID', 'School', 'Full Name', 'Date of Birth', 'Qualification', 'Experience (Yrs)', 'Subjects', 'Gender', 'Phone', 'Email', 'Resume', 'Address', 'Submitted At']);
    $res = mysqli_query($conn, "SELECT * FROM teacher_applications WHERE $where_sql ORDER BY created_at DESC");
    while ($row = mysqli_fetch_assoc($res)) {
        fputcsv($out, [
            $row['id'],
            $row['school'],
            $row['full_name'],
            $row['date_of_birth'],
            $row['qualification'],
            $row['years_experience'],
            $row['subjects'],
            $row['gender'],
            $row['phone'],
            $row['email'],
            $row['resume_path'],
            $row['address'],
            $row['created_at'],
        ]);
    }
}

fclose($out);
exit;
