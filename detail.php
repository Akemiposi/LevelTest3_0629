<?php
require_once('funcs.php');

// ID取得（GETで渡された行番号）
$id = isset($_GET['id']) ? intval($_GET['id']) : -1;
if ($id < 0) {
  die('不正なIDです');
}

// DB接続
$pdo = db_conn();

// SQLで1件取得
$sql = "SELECT * FROM gs_leveltest3_01 WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  die('データが見つかりませんでした');
}
// 質問文の配列を追加
$questions = [
  '1. あなたの名前は？',
  '2. どこから来ましたか？',
  '3. 何歳ですか？',
  '4. 今日は、何曜日ですか？',
  '5. 明日は、何日ですか？',
  '6. 何時ですか？',
  '7. これは何ですか？'
];
// 回答配列（質問7つ）
$answers = [
  $row['q0_1_1'],
  $row['q0_1_2'],
  $row['q0_1_3'],
  $row['q0_1_4'],
  $row['q0_1_5'],
  $row['q0_1_6'],
  $row['q0_1_7'],
];

// 表示記号変換関数
function mark($v) {
  return intval($v) > 0 ? '⚪︎' : '×';
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>詳細結果</title>
  <style>
    table { border-collapse: collapse; margin: 20px 0; }
    th, td { border: 1px solid #ccc; padding: 8px 16px; text-align: center; }
    .left-align {text-align: left;}
    th { background: #f0f0f0; }
  </style>
</head>
<body>
  <h1><?= h($row['name']) ?> さんの詳細結果</h1>
  <p>
    日付：<?= h($row['date']) ?>　
    学校：<?= h($row['school']) ?>　
    学年：<?= h($row['year']) ?>　
    組：<?= h($row['class']) ?>　
    性別：<?= h($row['gender']) ?>
    言語：<?= h($row['language']) ?>
  </p>

  <table>
  <tr><th>質問</th><th>回答</th></tr>
  <?php for ($i = 0; $i < count($answers); $i++): ?>
    <tr>
      <td class="left-align"><?= h($questions[$i]) ?></td>
      <td><?= mark($answers[$i]) ?></td>
    </tr>
  <?php endfor; ?>
</table>

  <p><a href="score.php">← スコア一覧に戻る</a></p>
</body>
</html>
