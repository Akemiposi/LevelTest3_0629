<?php
require_once('./funcs.php'); // db_conn() と sql_error() を使用
session_start(); // ユーザー情報を使うため

// POSTデータ取得
$id = isset($_POST['id']) ? intval($_POST['id']) : -1;
$level = isset($_POST['level']) ? intval($_POST['level']) : -1;

if ($id <= 0 || !in_array($level, [0, 1])) {
  exit('不正なリクエストです');
}

// テーブル名を決定
$table = $level === 0 ? 'leveltest_0' : 'leveltest_1';

// DB接続
$pdo = db_conn();

// SQL実行（DELETE）
$sql = "DELETE FROM {$table} WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);

$status = $stmt->execute();
if ($status === false) {
  sql_error($stmt);
} else {
  // リダイレクト先を判定
  if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header("Location: score.php");
  } elseif (isset($_SESSION['teacher_id'])) {
    header("Location: teacher.php");
  } else {
    header("Location: login.php");
  }
  exit();
}
