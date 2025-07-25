<?php
require_once('./funcs.php');


$id = intval($_POST['id']);
if ($id <= 0) exit('不正なID');

$pdo = db_conn();

// 入力データ取得
$student_id = $_POST['student_id'] ?? '';
$date = $_POST['date'] ?? '';
$school = $_POST['school'] ?? '';
$year = $_POST['year'] ?? '';
$class = $_POST['class'] ?? '';
$gender = $_POST['gender'] ?? '';
$language = $_POST['language_code'] ?? '';

// 各セクションの値取得と合計計算
$q0_1 = [];
for ($i = 1; $i <= 7; $i++) $q0_1["q0_1_$i"] = intval($_POST["q0_1_$i"] ?? 0);
$q0_2 = [];
for ($i = 1; $i <= 10; $i++) $q0_2["q0_2_$i"] = intval($_POST["q0_2_$i"] ?? 0);
$q0_3 = [];
for ($i = 1; $i <= 3; $i++) $q0_3["q0_3_$i"] = intval($_POST["q0_3_$i"] ?? 0);

$q0_1_score = array_sum($q0_1);
$q0_2_score = array_sum($q0_2);
$q0_3_score = array_sum($q0_3);

$hiragana_score = intval($_POST['hiragana_score'] ?? 0);
$katakana_score = intval($_POST['katakana_score'] ?? 0);
$q0_4_score = $hiragana_score + $katakana_score;
$total_score = $q0_1_score + $q0_2_score + $q0_3_score + $q0_4_score;

$selected_hiragana = $_POST['selected_hiragana'] ?? '';
$unselected_hiragana = $_POST['unselected_hiragana'] ?? '';
$selected_katakana = $_POST['selected_katakana'] ?? '';
$unselected_katakana = $_POST['unselected_katakana'] ?? '';

// SQL（student_id を更新対象に含めるならここも）
$sql = "UPDATE gs_leveltest3_01 SET
  date = :date, school = :school, year = :year, class = :class,
  gender = :gender, language_code = :language_code,
  q0_1_1 = :q0_1_1, q0_1_2 = :q0_1_2, q0_1_3 = :q0_1_3, q0_1_4 = :q0_1_4,
  q0_1_5 = :q0_1_5, q0_1_6 = :q0_1_6, q0_1_7 = :q0_1_7,
  q0_2_1 = :q0_2_1, q0_2_2 = :q0_2_2, q0_2_3 = :q0_2_3, q0_2_4 = :q0_2_4,
  q0_2_5 = :q0_2_5, q0_2_6 = :q0_2_6, q0_2_7 = :q0_2_7, q0_2_8 = :q0_2_8,
  q0_2_9 = :q0_2_9, q0_2_10 = :q0_2_10,
  q0_3_1 = :q0_3_1, q0_3_2 = :q0_3_2, q0_3_3 = :q0_3_3,
  q0_1_score = :q0_1_score, q0_2_score = :q0_2_score, q0_3_score = :q0_3_score,
  q0_4_score = :q0_4_score, hiragana_score = :hiragana_score, katakana_score = :katakana_score,
  total_score = :total_score,
  selected_hiragana = :selected_hiragana, unselected_hiragana = :unselected_hiragana,
  selected_katakana = :selected_katakana, unselected_katakana = :unselected_katakana
WHERE id = :id";

$stmt = $pdo->prepare($sql);

// バインド
$stmt->bindValue(':date', $date, PDO::PARAM_STR);
$stmt->bindValue(':school', $school, PDO::PARAM_STR);
$stmt->bindValue(':year', $year, PDO::PARAM_STR);
$stmt->bindValue(':class', $class, PDO::PARAM_STR);
$stmt->bindValue(':gender', $gender, PDO::PARAM_STR);
$stmt->bindValue(':language_code', $language, PDO::PARAM_STR);

foreach ($q0_1 as $k => $v) $stmt->bindValue(":$k", $v, PDO::PARAM_INT);
foreach ($q0_2 as $k => $v) $stmt->bindValue(":$k", $v, PDO::PARAM_INT);
foreach ($q0_3 as $k => $v) $stmt->bindValue(":$k", $v, PDO::PARAM_INT);

$stmt->bindValue(':q0_1_score', $q0_1_score, PDO::PARAM_INT);
$stmt->bindValue(':q0_2_score', $q0_2_score, PDO::PARAM_INT);
$stmt->bindValue(':q0_3_score', $q0_3_score, PDO::PARAM_INT);
$stmt->bindValue(':q0_4_score', $q0_4_score, PDO::PARAM_INT);
$stmt->bindValue(':hiragana_score', $hiragana_score, PDO::PARAM_INT);
$stmt->bindValue(':katakana_score', $katakana_score, PDO::PARAM_INT);
$stmt->bindValue(':total_score', $total_score, PDO::PARAM_INT);

$stmt->bindValue(':selected_hiragana', $selected_hiragana, PDO::PARAM_STR);
$stmt->bindValue(':unselected_hiragana', $unselected_hiragana, PDO::PARAM_STR);
$stmt->bindValue(':selected_katakana', $selected_katakana, PDO::PARAM_STR);
$stmt->bindValue(':unselected_katakana', $unselected_katakana, PDO::PARAM_STR);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);

// 実行
$status = $stmt->execute();
if (!$status) {
    sql_error($stmt);
} else {
    header("Location: detail.php?id=" . $id);
    exit();
}
