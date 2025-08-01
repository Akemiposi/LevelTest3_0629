<?php
require_once('./funcs.php');


$student_id = $_POST['student_id'] ?? '';
if ($student_id === '') exit('student_id不正なID');

$pdo = db_conn();
if ($student_id === '' && isset($_GET['id'])) {
    // id から student_id を取得（古いURL互換）
    $stmt = $pdo->prepare("SELECT student_id FROM leveltest_1 WHERE id = :id");
    $stmt->bindValue(':id', intval($_GET['id']), PDO::PARAM_INT);
    $stmt->execute();
    $found = $stmt->fetchColumn();
    if ($found) {
        $student_id = $found;
    }
}

if ($student_id === '') {
    die('student_id不正なIDです');
}

// 入力データ取得
$student_id = $_POST['student_id'] ?? '';
$date = $_POST['date'] ?? '';
$school = $_POST['school'] ?? '';
$year = $_POST['year'] ?? '';
$class = $_POST['class'] ?? '';
$gender = $_POST['gender'] ?? '';
$language = $_POST['language_code'] ?? '';
$q1_1_score = $_POST['q1_1_score'];
$q1_2_score = $_POST['q1_2_score'];
$q1_3_score = $_POST['q1_3_score'];
$q1_total_score = $_POST['q1_total_score'];

// Q1-1
$q1_1 = [];
for ($i = 1; $i <= 6; $i++) {
    $q1_1["q1_1_$i"] = $_POST["q1_1_$i"] ?? 0;
}

// Q1-2（特別構成）
$q1_2 = [];
for ($i = 1; $i <= 4; $i++) {
    $q1_2["q1_2_$i"] = $_POST["q1_2_$i"] ?? 0;
}
for ($j = 1; $j <= 4; $j++) {
    $q1_2["q1_2_5_$j"] = $_POST["q1_2_5_$j"] ?? 0;
}

// Q1-3（+音読）
$q1_3 = [];
for ($i = 1; $i <= 5; $i++) {
    $q1_3["q1_3_$i"] = $_POST["q1_3_$i"] ?? 0;
}
$q1_3_ondoku = $_POST['q1_3_ondoku'] ?? 0;

// SQL
$sql = "UPDATE leveltest_1 SET 
    q1_1_1 = :q1_1_1, q1_1_2 = :q1_1_2, q1_1_3 = :q1_1_3, 
    q1_1_4 = :q1_1_4, q1_1_5 = :q1_1_5, q1_1_6 = :q1_1_6,
    q1_2_1 = :q1_2_1, q1_2_2 = :q1_2_2, q1_2_3 = :q1_2_3, 
    q1_2_4 = :q1_2_4, 
    q1_2_5_1 = :q1_2_5_1, q1_2_5_2 = :q1_2_5_2, 
    q1_2_5_3 = :q1_2_5_3, q1_2_5_4 = :q1_2_5_4,
    q1_3_1 = :q1_3_1, q1_3_2 = :q1_3_2, q1_3_3 = :q1_3_3, 
    q1_3_4 = :q1_3_4, q1_3_5 = :q1_3_5, q1_3_ondoku = :q1_3_ondoku,
    q1_1_score = :q1_1_score, q1_2_score = :q1_2_score, 
    q1_3_score = :q1_3_score, q1_total_score = :q1_total_score
WHERE id = :id";

$stmt = $pdo->prepare($sql);

// バインド
foreach ($q1_1 as $key => $val) {
    $stmt->bindValue(":$key", $val, PDO::PARAM_INT);
}
foreach ($q1_2 as $key => $val) {
    $stmt->bindValue(":$key", $val, PDO::PARAM_INT);
}
foreach ($q1_3 as $key => $val) {
    $stmt->bindValue(":$key", $val, PDO::PARAM_INT);
}
$stmt->bindValue(":q1_3_ondoku", $q1_3_ondoku, PDO::PARAM_INT);

$stmt->bindValue(':q1_1_score', $q1_1_score, PDO::PARAM_INT);
$stmt->bindValue(':q1_2_score', $q1_2_score, PDO::PARAM_INT);
$stmt->bindValue(':q1_3_score', $q1_3_score, PDO::PARAM_INT);
$stmt->bindValue(':q1_total_score', $q1_total_score, PDO::PARAM_INT);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);

// 実行
$status = $stmt->execute();

if ($status) {
    header("Location: detail1.php?id=$id");
    exit();
} else {
    sql_error($stmt);
}
