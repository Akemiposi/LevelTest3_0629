<?php
require_once('./funcs.php'); // h(), db_conn()

$pdo = db_conn(); // DB接続

// データ取得
$sql = "SELECT * FROM gs_leveltest3_01 ORDER BY date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>スコア一覧</title>
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
    }

    th,
    td {
      padding: 8px;
      border: 1px solid #ccc;
      text-align: center;
    }

    th {
      background-color: #f2f2f2;
    }
  </style>
  <link rel="icon" type="image/png" href="img/favicon2.png">

  <link rel="stylesheet" href="css/reset.css" />
  <link rel="stylesheet" href="css/style.css" />
</head>

<body>

  <!-- ヘッダー -->
   <header style="background-color:#2c3e50; padding:20px; text-align:center;">
    <h1 style="color:white; font-size:2rem; margin:0;">結果一覧</h1>
  </header>

  <!-- ナビゲーション -->
  <nav class="nav-bar">
    <a href="index.php">レベル０</a>
    <a href="level1.php">レベル１</a>
    <a href="level2.php">レベル２</a>
    <a href="score.php">結果一覧</a>
    <a href="curriculum.php">カリキュラム一覧</a>
    <a href="plan.php">指導計画書発行</a>
  </nav>
  <h1>テスト結果一覧</h1>
  <table>
    <tr>
      <th>日付</th>
      <th>学校</th>
      <th>年</th>
      <th>組</th>
      <th>名前</th>
      <th>性別</th>
      <th>言語</th>
      <th>L0詳細</th>
      <th>L1詳細</th>
      <th>L2詳細</th>
    </tr>

    <?php foreach ($results as $row): ?>
      <tr>
        <td><?= h($row['date']) ?></td>
        <td><?= h($row['school']) ?></td>
        <td><?= h($row['year']) ?></td>
        <td><?= h($row['class']) ?></td>
        <td><?= h($row['name']) ?></td>
        <td><?= h($row['gender']) ?></td>
        <td><?= h($row['language']) ?></td>
        <td><a href="detail.php?id=<?= h($row['id']) ?>">詳細</a></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><a href="index.php">← 戻る</a></p>

 <footer>@nihongo-note all right reserved.</footer>

  <script src="js/index.js"></script>
</body>

</html>