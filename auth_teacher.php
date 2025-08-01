<?php
// auth_teacher.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once(__DIR__ . '/funcs.php');

if (!empty($_GET['teacher_id'])) {
    $_SESSION['teacher_id'] = $_GET['teacher_id'];
}

$teacher_id = $_SESSION['teacher_id'] ?? '';

if ($teacher_id === '') {
    header('Location: login.php');
    exit;
}


// ===== 権限判定 =====
$is_teacher = false;
$is_admin   = false;

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'teacher') {
        $is_teacher = true;
    } elseif ($_SESSION['role'] === 'admin') {
        $is_admin = true;
    }
}