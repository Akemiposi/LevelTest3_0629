<?php
session_start();
require_once('funcs.php'); // db_conn(), check_login() を含む共通関数

// ログインチェック（未ログインなら login.php に飛ばす）
check_login();

// 管理者以外はアクセス不可
if ($_SESSION['role'] !== 'admin') {
    exit('アクセス権限がありません（admin専用ページ）');
}

$pdo = db_conn();

// 生徒一覧を取得
$stmt = $pdo->prepare("SELECT * FROM students ORDER BY id DESC");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>管理者ダッシュボード | nihongonote</title>
  <style>
    body { font-family: sans-serif; padding: 2em; background: #f9f9f9; }
    h1 { margin-bottom: 0.5em; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #eee; }
    .logout { margin-top: 20px; }
  </style>
</head>
<body>
  <h1>管理者ダッシュボード</h1>
  <p>ようこそ、<?= htmlspecialchars($_SESSION['name'], ENT_QUOTES) ?>さん</p>

  <div class="logout">
    <a href="logout.php">ログアウト</a>
  </div>

  <h2>生徒一覧（全件表示）</h2>
  <table>
    <tr>
      <th>ID</th>
      <th>名前</th>
      <th>学年</th>
      <th>担当教師ID</th>
    </tr>
    <?php foreach ($students as $student): ?>
      <tr>
        <td><?= htmlspecialchars($student['id']) ?></td>
        <td><?= htmlspecialchars($student['name']) ?></td>
        <td><?= htmlspecialchars($student['grade']) ?></td>
        <td><?= htmlspecialchars($student['teacher_id']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>

