<?php
require_once __DIR__ . '/../funcs.php'; // db_conn() と sql_error() を使用

// POSTでIDを受け取る
$id = isset($_POST['id']) ? intval($_POST['id']) : -1;
if ($id <= 0) {
  exit('不正なIDです');
}

// DB接続
$pdo = db_conn();

// SQL実行（DELETE）
$sql = "DELETE FROM gs_leveltest3_01 WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);

// 実行とエラーハンドリング
$status = $stmt->execute();
if ($status === false) {
  sql_error($stmt);
} else {
  // 削除後の遷移先（一覧ページなど）
  header("Location: score.php");
  exit();
}
