<?php
require_once('./funcs.php'); // ← DB接続関数など
$pdo = db_conn();

$student_id = $_GET['id'] ?? null;
if (!$student_id) {
  exit("生徒IDが指定されていません。");
}

// 生徒のテスト結果を取得（横持ち）
$sql = "SELECT * FROM gs_leveltest3_01 WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $student_id, PDO::PARAM_INT);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
  exit("生徒データが見つかりません。");
}

// カリキュラムを取得、正解率を算出
$sql = "SELECT * FROM curriculum ORDER BY id";
$curricula = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$curriculum_results = [];
$start_point = null;

foreach ($curricula as $row) {
  $test_keys = array_map('trim', explode(',', $row['test_compare']));
  $correct = 0;
  $total = 0;

  foreach ($test_keys as $key) {
    if (!isset($student[$key])) continue;
    $total++;
    if ($student[$key] == '1') $correct++;
  }

  $rate = ($total > 0) ? round($correct / $total * 100, 1) : null;
  $row['correct'] = $correct;
  $row['total'] = $total;
  $row['correct_rate'] = $rate;

  if ($rate !== null && $rate < 50 && !$start_point) {
    $start_point = $row;
  }

  $curriculum_results[] = $row;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>指導計画書</title>
  <link rel="icon" type="image/png" href="img/favicon2.png">
  <link rel="stylesheet" href="css/reset.css" />
  <link rel="stylesheet" href="css/style.css" />
  <style>
    body { font-family: sans-serif; padding: 2em; }
    h2 { margin-top: 2em; }
    table { border-collapse: collapse; width: 100%; margin-top: 1em; }
    th, td { border: 1px solid #ccc; padding: 6px; }
    th { background-color: #f0f0f0; }
    @media print {
      button { display: none; }
    }
  </style>
</head>
<body>
    <body>
  <header style="background-color:#2c3e50; padding:20px; text-align:center;">
    <h1 style="color:white; font-size:2rem; margin:0;">指導計画書</h1>
  </header>

  <nav class="nav-bar">
    <a href="index.php">レベル０</a>
    <a href="level1.php">レベル１</a>
    <a href="level2.php">レベル２</a>
    <a href="score.php">結果一覧</a>
    <a href="curriculum.php">カリキュラム一覧</a>
    <a href="plan.php">指導計画書発行</a>
  </nav>

<p><strong>氏名：</strong><?= htmlspecialchars($student['name']) ?></p>
<p><strong>学校：</strong><?= htmlspecialchars($student['school']) ?>　学年：<?= $student['year'] ?>年<?= $student['class'] ?>組</p>
<p><strong>受験日：</strong><?= htmlspecialchars($student['date']) ?></p>

<h2>指導開始コマの提案</h2>
<?php if ($start_point): ?>
  <p>以下のコマからの指導をおすすめします：</p>
  <ul>
    <li>コマNo：<?= $start_point['no'] ?>（ID: <?= $start_point['id'] ?>）</li>
    <li>主な内容：<?= $start_point['instruction'] ?></li>
    <li>対応問題：<?= $start_point['test_compare'] ?></li>
    <li>正答率：<?= $start_point['correct_rate'] ?>%</li>
  </ul>
<?php else: ?>
  <p>すべてのコマにおいて高得点でした。応用的な内容から指導可能です。</p>
<?php endif; ?>

<h2>コマ別正答率一覧</h2>
<table>
  <tr>
    <th>コマID</th><th>No</th><th>指導内容</th><th>対応問題</th><th>正解数</th><th>正答率</th>
  </tr>
  <?php foreach ($curriculum_results as $row): ?>
    <tr>
      <td><?= $row['id'] ?></td>
      <td><?= $row['no'] ?></td>
      <td><?= $row['instruction'] ?></td>
      <td><?= $row['test_compare'] ?></td>
      <td><?= "{$row['correct']} / {$row['total']}" ?></td>
      <td><?= $row['correct_rate'] !== null ? "{$row['correct_rate']}%" : '-' ?></td>
    </tr>
  <?php endforeach; ?>
</table>

<br>
<button onclick="window.print()">📄 印刷／PDF保存</button>

</body>
</html>
