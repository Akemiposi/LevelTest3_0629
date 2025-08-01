<?php
require_once(__DIR__ . '/auth_teacher.php'); // __DIR__で現在のディレクトリを取得して安全に読み込む
$pdo = db_conn();

// --- データ取得 ---
$sql = "SELECT no, level_main, level_middle, level_sub, instruction, details, textbook, characters FROM curriculum ORDER BY no ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>にほんごノート（カリキュラム）</title>
  <link rel="icon" type="image/png" href="img/favicon2.png">
  <link rel="stylesheet" href="css/reset.css" />
  <link rel="stylesheet" href="css/curriculum.css" />
</head>

<body>
  <!-- ヘッダー -->
  <header style="background-color:#2c3e50; padding:20px; text-align:center;">
    <h1 style="color:white; font-size:2rem; margin:0;">カリキュラム</h1>
  </header>

  <!-- ナビゲーション -->

  <?php if ($is_teacher): ?>
    <nav class="nav-bar">
      <a href="level0.php?teacher_id=<?= urlencode($_SESSION['teacher_id'] ?? '') ?>">レベル０</a>
      <a href="level1.php?teacher_id=<?= urlencode($_SESSION['teacher_id'] ?? '') ?>">レベル１</a>
      <!-- <a href="level2.php?teacher_id=<?= urlencode($_SESSION['teacher_id'] ?? '') ?>">レベル２</a> -->
      <a href="teacher.php?teacher_id=<?= urlencode($_SESSION['teacher_id'] ?? '') ?>">結果一覧</a>
      <a href="curriculum.php?teacher_id=<?= urlencode($_SESSION['teacher_id'] ?? '') ?>">カリキュラム</a>
    </nav>
    <div class="login" style="text-align: right; margin: 20px, 0;">
      <a href="login.php">管理用ログイン</a>
    </div>

  <?php elseif ($is_admin): ?>
    <nav class="nav-bar">
      <a href="level0.php?admin_id=<?= urlencode($_SESSION['admin_id'] ?? '') ?>">レベル０</a>
      <a href="level1.php?admin_id=<?= urlencode($_SESSION['admin_id'] ?? '') ?>">レベル１</a>
      <a href="score.php?admin_id=<?= urlencode($_SESSION['admin_id'] ?? '') ?>">管理一覧</a>
      <a href="curriculum.php?teacher_id=<?= urlencode($_SESSION['admin_id'] ?? '') ?>">カリキュラム</a>
    </nav>
    <div class="logout">
      <a href="logout.php">ログアウト</a>
    </div>
    
  <?php endif; ?>




  <table>
    <thead>
      <tr>
        <th>No.</th>
        <th>レベル</th>
        <th>ステージ</th>
        <th>ステップ</th>
        <th>指導内容</th>
        <th>主な表現・語彙</th>
        <th>教材</th>
        <th>文字・課</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['no']) ?></td>
          <td><?= htmlspecialchars($row['level_main']) ?></td>
          <td><?= htmlspecialchars($row['level_middle']) ?></td>
          <td><?= htmlspecialchars($row['level_sub']) ?></td>
          <td><?= nl2br(htmlspecialchars($row['instruction'])) ?></td>
          <td><?= nl2br(htmlspecialchars($row['details'])) ?></td>
          <td><?= htmlspecialchars($row['textbook']) ?></td>
          <td><?= htmlspecialchars($row['characters']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>

</html>