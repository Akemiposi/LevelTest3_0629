<?php
session_start();
require_once('funcs.php');
check_login(); // ログインしていない場合はログインページへ

$pdo = db_conn();

// 管理者なら全件、先生なら自分の担当だけ
if ($_SESSION['role'] === 'admin') {
  $stmt = $pdo->prepare("SELECT * FROM students ORDER BY id DESC");
} else {
  $stmt = $pdo->prepare("SELECT * FROM students WHERE teacher_id = :teacher_id ORDER BY id DESC");
  $stmt->bindValue(':teacher_id', $_SESSION['teacher_id']);
}
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>にほんごノート（管理者用ページ）</title>
  <link rel="icon" type="image/png" href="img/favicon2.png">
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/style.css">
  <!-- <link rel="stylesheet" href="css/login.css"> -->
</head>

<body>
  <!-- ヘッダー -->
  <header style="background-color:#2c3e50; padding:20px; text-align:center;">
    <h1 style="color:white; font-size:2rem; margin:0;">にほんごノート　管理者用ページ</h1>
  </header>

  <!-- ナビゲーション -->
  <nav class="nav-bar">
    <a href="level0.php">レベル０</a>
    <a href="level1.php">レベル１</a>
    <a href="level2.php">レベル２</a>
    <a href="score.php">結果一覧</a>
    <a href="student_list.php">生徒一覧</a>
    <a href="teacher.php">講師用ページ</a>
    <a href="curriculum.php">カリキュラム一覧</a>
    <a href="plan.php">指導計画書発行</a>
  </nav>

  <body>
    <h2>ログインユーザー：<?= h($_SESSION['name']) ?>（<?= h($_SESSION['role']) ?>）</h2>

    <table border="1">
      <tr>
        <th>ID</th>
        <th>名前</th>
        <th>学年</th>
        <th>担当</th>
      </tr>
      <?php foreach ($students as $student): ?>
        <tr>
          <td><?= h($student['id']) ?></td>
          <td><?= h($student['name']) ?></td>
          <td><?= h($student['grade']) ?></td>
          <td><?= h($student['teacher_id']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </body>

  </html>