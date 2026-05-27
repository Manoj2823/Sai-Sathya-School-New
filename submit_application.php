<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

function jsonResponse(bool $success, string $message): void
{
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

if (!$conn) {
    jsonResponse(false, 'Database is unavailable. Please try again later.');
}

function ensureApplicationTables($conn): void
{
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
}

ensureApplicationTables($conn);

$type = trim($_POST['application_type'] ?? '');
$school = trim($_POST['school'] ?? '');

if (!in_array($type, ['admission', 'teacher'], true)) {
    jsonResponse(false, 'Invalid application type.');
}

if (!in_array($school, ['samacheer', 'cbse'], true)) {
    jsonResponse(false, 'Invalid school.');
}

function clean(string $key): string
{
    return trim($_POST[$key] ?? '');
}

function validName(string $name): bool
{
    return (bool) preg_match('/^[a-zA-Z\s\.]{3,150}$/', $name);
}

function validPhone(string $phone): bool
{
    return (bool) preg_match('/^[0-9]{10}$/', $phone);
}

function validEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validDob(string $dob): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $dob);
    if (!$dt || $dt->format('Y-m-d') !== $dob) {
        return false;
    }
    return $dt < new DateTime('today');
}

if ($type === 'admission') {
    $student_name = clean('student_name');
    $date_of_birth = clean('date_of_birth');
    $class_applied = clean('class_applied');
    $gender = clean('gender');
    $parent_name = clean('parent_name');
    $phone = clean('phone');
    $email = clean('email');
    $address = clean('address');

    if (!$student_name || !$date_of_birth || !$class_applied || !$gender || !$parent_name || !$phone || !$email || !$address) {
        jsonResponse(false, 'Please fill in all required fields.');
    }
    if (!validName($student_name) || !validName($parent_name)) {
        jsonResponse(false, 'Names must contain only letters and be at least 3 characters.');
    }
    if (!validDob($date_of_birth)) {
        jsonResponse(false, 'Please enter a valid date of birth.');
    }
    if (!validPhone($phone)) {
        jsonResponse(false, 'Please enter a valid 10-digit phone number.');
    }
    if (!validEmail($email)) {
        jsonResponse(false, 'Please enter a valid email address.');
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO admission_applications
        (school, student_name, date_of_birth, class_applied, gender, parent_name, phone, email, address)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    mysqli_stmt_bind_param(
        $stmt,
        'sssssssss',
        $school,
        $student_name,
        $date_of_birth,
        $class_applied,
        $gender,
        $parent_name,
        $phone,
        $email,
        $address
    );

    if (!mysqli_stmt_execute($stmt)) {
        jsonResponse(false, 'Could not save your application. Please try again.');
    }

    jsonResponse(true, 'Admission inquiry submitted successfully! Our team will contact you soon.');
}

// Teacher application
$full_name = clean('full_name');
$date_of_birth = clean('date_of_birth');
$qualification = clean('qualification');
$years_experience = (int) ($_POST['years_experience'] ?? 0);
$subjects = clean('subjects');
$gender = clean('gender');
$phone = clean('phone');
$email = clean('email');
$address = clean('address');

if (!$full_name || !$date_of_birth || !$qualification || !$subjects || !$gender || !$phone || !$email || !$address) {
    jsonResponse(false, 'Please fill in all required fields.');
}
if (!validName($full_name)) {
    jsonResponse(false, 'Please enter a valid full name (letters only, min 3 characters).');
}
if (!validDob($date_of_birth)) {
    jsonResponse(false, 'Please enter a valid date of birth.');
}
if ($years_experience < 0 || $years_experience > 60) {
    jsonResponse(false, 'Please enter valid years of experience.');
}
if (!validPhone($phone)) {
    jsonResponse(false, 'Please enter a valid 10-digit phone number.');
}
if (!validEmail($email)) {
    jsonResponse(false, 'Please enter a valid email address.');
}

$resume_path = null;
if (!empty($_FILES['resume']['name'])) {
    $allowed = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['resume']['tmp_name']);
    finfo_close($finfo);

    $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
    if (!in_array($mime, $allowed, true) && !in_array($ext, ['pdf', 'doc', 'docx'], true)) {
        jsonResponse(false, 'Resume must be a PDF or Word document.');
    }
    if ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
        jsonResponse(false, 'Resume file must be under 5 MB.');
    }

    $dest_dir = __DIR__ . '/uploads/resumes/';
    if (!is_dir($dest_dir)) {
        mkdir($dest_dir, 0755, true);
    }

    $filename = 'resume_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $dest_dir . $filename;

    if (!move_uploaded_file($_FILES['resume']['tmp_name'], $dest)) {
        jsonResponse(false, 'Failed to upload resume. Please try again.');
    }
    $resume_path = 'uploads/resumes/' . $filename;
} else {
    jsonResponse(false, 'Please upload your resume.');
}

$stmt = mysqli_prepare($conn, "INSERT INTO teacher_applications
    (school, full_name, date_of_birth, qualification, years_experience, subjects, gender, phone, email, resume_path, address)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

mysqli_stmt_bind_param(
    $stmt,
    'ssssissssss',
    $school,
    $full_name,
    $date_of_birth,
    $qualification,
    $years_experience,
    $subjects,
    $gender,
    $phone,
    $email,
    $resume_path,
    $address
);

if (!mysqli_stmt_execute($stmt)) {
    jsonResponse(false, 'Could not save your application. Please try again.');
}

jsonResponse(true, 'Teacher application submitted successfully! Thank you for your interest.');
