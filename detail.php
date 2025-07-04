<?php
require_once('funcs.php'); // h() と db_conn()

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

// Q0-1: 質問と回答
$q0_1_questions = [
  '1. あなたの名前は？',
  '2. どこから来ましたか？',
  '3. 何歳ですか？',
  '4. 今日は、何曜日ですか？',
  '5. 明日は、何日ですか？',
  '6. 何時ですか？',
  '7. これは何ですか？'
];
$q0_1_answers = [
  $row['q0_1_1'],
  $row['q0_1_2'],
  $row['q0_1_3'],
  $row['q0_1_4'],
  $row['q0_1_5'],
  $row['q0_1_6'],
  $row['q0_1_7'],
];

// Q0-2: 選択式（10問）
$q0_2_answers = [
  $row['q0_2_1'],
  $row['q0_2_2'],
  $row['q0_2_3'],
  $row['q0_2_4'],
  $row['q0_2_5'],
  $row['q0_2_6'],
  $row['q0_2_7'],
  $row['q0_2_8'],
  $row['q0_2_9'],
  $row['q0_2_10'],
];

// Q0-3: ひらがな
$q0_3_answers = [
  $row['q0_3_1'],
  $row['q0_3_2'],
  $row['q0_3_3'],
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
    table { border-collapse: collapse; margin: 20px 0; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 8px 16px; text-align: center; }
    .left-align { text-align: left; }
    th { background: #f0f0f0; }
  </style>
   <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <!-- ヘッダー -->
  <header style="background-color:#2c3e50; padding:20px; text-align:center;">
  <h1 style="color:white; font-size:2rem; margin:0;">にほんごノート　レベルチェック</h1>
</header>

  <!-- ナビゲーション -->
<nav class="nav-bar">
  <a href="index.php">レベル０</a>
  <a href="level1.php">レベル１</a>
  <a href="level2.php">レベル２</a>
  <a href="curriculum.php">カリキュラム一覧</a>
  <a href="plan.php">指導計画書発行</a>
</nav>

  <h1><?= h($row['name']) ?> さんの詳細結果</h1>
  <p>
    日付：<?= h($row['date']) ?>　
    学校：<?= h($row['school']) ?>　
    学年：<?= h($row['year']) ?>　
    組：<?= h($row['class']) ?>　
    性別：<?= h($row['gender']) ?>　
    言語：<?= h($row['language']) ?>
  </p>

  <h2>Q0-1：聞く問題</h2>
  <table>
    <tr><th>質問</th><th>回答</th></tr>
    <?php foreach ($q0_1_questions as $i => $question): ?>
      <tr>
        <td class="left-align"><?= h($question) ?></td>
        <td><?= mark($q0_1_answers[$i]) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <p>Q0-1 スコア：<?= h($row['q0_1_score']) ?> 点</p>

  <h2>Q0-2：読む問題</h2>
  <table>
    <tr><th>番号</th><th>正誤</th></tr>
    <?php foreach ($q0_2_answers as $i => $val): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= mark($val) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <p>Q0-2 スコア：<?= h($row['q0_2_score']) ?> 点</p>

  <h2>Q0-3：書く問題１ー自己紹介</h2>
  <table>
    <tr><th>番号</th><th>正誤</th></tr>
    <?php foreach ($q0_3_answers as $i => $val): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= mark($val) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <p>Q0-3 スコア：<?= h($row['q0_3_score']) ?> 点</p>
  
  <h2>Q0-4：カタカナを書く</h2>
  <p>ひらがなマス数：<?= h($row['hiragana_score']) ?> 点</p>
  <p>カタカナマス数：<?= h($row['katakana_score']) ?> 点</p>
  <p>Q0-4 スコア：<?= h($row['q0_4_score']) ?> 点</p>

  <h2>総合スコア</h2>
  <p><?= h($row['total_score']) ?> 点</p>

  <p><a href="score.php">← スコア一覧に戻る</a></p>
</body>
</html>
