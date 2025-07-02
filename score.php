<?php
require_once('funcs.php'); // 共通関数（h() と db_conn()）

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
    table { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px; border: 1px solid #ccc; text-align: center; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>
  <h1>テスト結果一覧（MySQL）</h1>
  <table>
    <tr>
      <th>日付</th><th>学校</th><th>年</th><th>組</th><th>名前</th><th>性別</th><th>言語</th><th>合計</th><th>詳細</th>
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
        <td><?= h($row['total_score']) ?></td>
        <td><a href="detail.php?id=<?= h($row['id']) ?>">詳細</a></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><a href="index.php">← 戻る</a></p>
</body>
</html>