<?php
require_once('./funcs.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : -1;
if ($id < 0) die('不正なIDです');

$pdo = db_conn();
$sql = "SELECT * FROM gs_leveltest3_01 WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) die('データが見つかりませんでした');

$edit_mode = isset($_GET['edit']) && $_GET['edit'] === '1';

function mark($v)
{
  return intval($v) > 0 ? '⚪︎' : '×';
}

$q0_1_questions = ['1. あなたの名前は？', '2. どこから来ましたか？', '3. 何歳ですか？', '4. 今日は、何曜日ですか？', '5. 明日は、何日ですか？', '6. 何時ですか？', '7. これは何ですか？'];
$q0_2_questions = ['1. あさ', '2. おはよう', '3. がっこう', '4. ねこ', '5. テレビ', '6. ピーマン', '7. バスケットボール', '8. おおきい', '9. これは、かさです。', '10. パンを　たべます。'];
$q0_1_answers = array_map(fn($i) => $row["q0_1_$i"], range(1, 7));
$q0_2_answers = array_map(fn($i) => $row["q0_2_$i"], range(1, 10));
$q0_3_answers = array_map(fn($i) => $row["q0_3_$i"], range(1, 3));
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>詳細結果</title>
  <link rel="icon" type="image/png" href="img/favicon2.png">
  <link rel="stylesheet" href="css/reset.css" />
  <link rel="stylesheet" href="css/style.css" />

  <style>
    table {
      border-collapse: collapse;
      margin: 20px 0;
      width: 100%;
    }

    th,
    td {
      border: 1px solid #ccc;
      padding: 8px 16px;
      text-align: center;
    }

    .left-align {
      text-align: left;
    }

    th {
      background: #f0f0f0;
    }
  </style>
</head>

<body>
  <header style="background-color:#2c3e50; padding:20px; text-align:center;">
    <h1 style="color:white; font-size:2rem; margin:0;">結果詳細</h1>
  </header>


     <nav class="nav-bar">
        <a href="level0.php">レベル０</a>
        <a href="level1.php">レベル１</a>
        <a href="level2.php">レベル２</a>
        <a href="teacher.php">講師用ページ</a>
        <a href="curriculum.php">カリキュラム一覧</a>
        <a href="plan.php">指導計画書発行</a>
         <a href="score.php">管理用</a>
  </nav>

  <h1><?= h($row['name']) ?> さんの詳細結果</h1>

  <!-- ＊＊＊＊＊＊＊＊編集モード ここから＊＊＊＊＊＊＊＊ -->
  <?php if ($edit_mode): ?>
    <form method="POST" action="update.php" onsubmit="return handleSubmit()">
      <input type="hidden" name="id" value="<?= h($row['id']) ?>">

      <p>
        日付：<?= h($row['date']) ?>　
        学校：<?= h($row['school']) ?>　
        学年：<?= h($row['year']) ?>　
        組：<?= h($row['class']) ?>　
        性別：<?= h($row['gender']) ?>　
        言語：<?= h($row['language']) ?>
      </p>
      <!-- 修正不可だが送信される hidden input -->
      <input type="hidden" name="date" value="<?= h($row['date']) ?>">
      <input type="hidden" name="school" value="<?= h($row['school']) ?>">
      <input type="hidden" name="year" value="<?= h($row['year']) ?>">
      <input type="hidden" name="class" value="<?= h($row['class']) ?>">
      <input type="hidden" name="name" value="<?= h($row['name']) ?>">
      <input type="hidden" name="gender" value="<?= h($row['gender']) ?>">
      <input type="hidden" name="language" value="<?= h($row['language']) ?>">

      <h2>Q0-1：聞く問題</h2>
      <table>
        <tr>
          <th>質問</th>
          <th>正誤（5=⚪︎, 0=×）</th>
        </tr>
        <?php foreach ($q0_1_questions as $i => $q): ?>
          <tr>
            <td class="left-align"><?= h($q) ?></td>
            <td><input type="number" name="q0_1_<?= $i + 1 ?>" id="q0_1_<?= $i + 1 ?>" value="<?= h($q0_1_answers[$i]) ?>" min="0" max="5"></td>
          </tr>
        <?php endforeach; ?>
      </table>

      <h2>Q0-2：読む問題</h2>
      <table>
        <tr>
          <th>番号</th>
          <th>正誤（2=⚪︎, 0=×）</th>
        </tr>
        <?php foreach ($q0_2_answers as $i => $val): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><input type="number" name="q0_2_<?= $i + 1 ?>" id="q0_2_<?= $i + 1 ?>" value="<?= h($val) ?>" min="0" max="2"></td>
          </tr>
        <?php endforeach; ?>
      </table>

      <h2>Q0-3：書く問題１ー自己紹介</h2>
      <table>
        <tr>
          <th>番号</th>
          <th>正誤（5=⚪︎, 0=×）</th>
        </tr>
        <?php foreach ($q0_3_answers as $i => $val): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><input type="number" name="q0_3_<?= $i + 1 ?>" id="q0_3_<?= $i + 1 ?>" value="<?= h($val) ?>" min="0" max="5"></td>
          </tr>
        <?php endforeach; ?>
      </table>

      <h2>Q0-4：書く問題２ーひらがな・カタカナ</h2>
      <p>選ばれたひらがな：
        <input type="text" name="selected_hiragana" id="selected_hiragana"
          value="<?= h($row['selected_hiragana']) ?>" size="100">
      </p>

      <p>選ばれなかったひらがな：
        <input type="text" name="unselected_hiragana" id="unselected_hiragana"
          value="<?= h($row['unselected_hiragana']) ?>" size="100">
      </p>

      <p>選ばれたカタカナ：
        <input type="text" name="selected_katakana" id="selected_katakana"
          value="<?= h($row['selected_katakana']) ?>" size="100">
      </p>

      <p>選ばれなかったカタカナ：
        <input type="text" name="unselected_katakana" id="unselected_katakana"
          value="<?= h($row['unselected_katakana']) ?>" size="100">
      </p>

      <p>ひらがなスコア：<input type="text" name="hiragana_score" id="hiragana_score" value="<?= h($row['hiragana_score']) ?>"></p>
      <p>カタカナスコア：<input type="text" name="katakana_score" id="katakana_score" value="<?= h($row['katakana_score']) ?>"></p>

      <br>

      <p>Q0-1スコア：<input type="text" name="q0_1_score" id="q0_1_score" value="<?= h($row['q0_1_score']) ?>" onchange="updateScore()"></p>
      <p>Q0-2スコア：<input type="text" name="q0_2_score" id="q0_2_score" value="<?= h($row['q0_2_score']) ?>" onchange="updateScore()"></p>
      <p>Q0-3スコア：<input type="text" name="q0_3_score" id="q0_3_score" value="<?= h($row['q0_3_score']) ?>" onchange="updateScore()"></p>
      <p>Q0-4スコア：<input type="text" name="q0_4_score" id="q0_4_score" value="<?= h($row['q0_4_score']) ?>"></p>
      <p>総合スコア：<input type="text" name="total_score" id="total_score" value="<?= h($row['total_score']) ?>"></p>

      <p><button type="button" onclick="updateScore()">再計算</button></p>


      <p>
        <button type="submit">保存する</button>
        <a href="detail.php?id=<?= h($row['id']) ?>">キャンセル</a>
      </p>
    </form>

    <!-- ＊＊＊＊＊＊＊＊編集モード ここまで＊＊＊＊＊＊＊＊ -->

  <?php else: ?>

    <!-- ＊＊＊＊＊＊＊＊通常表示 ここから＊＊＊＊＊＊＊＊ -->
    <p>
      日付：<?= h($row['date']) ?>　
      学校：<?= h($row['school']) ?>　
      学年：<?= h($row['year']) ?>　
      組：<?= h($row['class']) ?>　
      性別：<?= h($row['gender']) ?>　
      言語：<?= h($row['language']) ?>
    </p>

    <p>レベル０スコア<?= h($row['total_score']) ?> 点</p>

    <h2>Q0-1：聞く問題</h2>
    <table>
      <tr>
        <th>質問</th>
        <th>正誤</th>
      </tr>
      <?php foreach ($q0_1_questions as $i => $q): ?>
        <tr>
          <td class="left-align"><?= h($q) ?></td>
          <td><?= mark($q0_1_answers[$i]) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <p>Q0-1 スコア：<?= h($row['q0_1_score']) ?> 点</p>

    <h2>Q0-2：読む問題</h2>
    <table>
      <tr>
        <th>番号</th>
        <th>正誤</th>
      </tr>
      <?php foreach ($q0_2_answers as $i => $val): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= mark($val) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <p>Q0-2 スコア：<?= h($row['q0_2_score']) ?> 点</p>

    <h2>Q0-3：書く問題１</h2>
    <table>
      <tr>
        <th>番号</th>
        <th>正誤</th>
      </tr>
      <?php foreach ($q0_3_answers as $i => $val): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= mark($val) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <p>Q0-3 スコア：<?= h($row['q0_3_score']) ?> 点</p>

    <h2>Q0-4：ひらがな・カタカナの選択結果</h2>
    <p>選ばれたひらがな：<?= h($row['selected_hiragana'] ?? 'なし') ?></p>
    <p>選ばれなかったひらがな：<?= h($row['unselected_hiragana'] ?? 'なし') ?></p>
    <p>選ばれたカタカナ：<?= h($row['selected_katakana'] ?? 'なし') ?></p>
    <p>選ばれなかったカタカナ：<?= h($row['unselected_katakana'] ?? 'なし') ?></p>
    <p>ひらがなスコア：<?= h($row['hiragana_score']) ?> 点</p>
    <p>カタカナスコア：<?= h($row['katakana_score']) ?> 点</p>
    <p>Q0-4 スコア：<?= h($row['q0_4_score']) ?> 点</p>

    <h2>総合スコア</h2>
    <p><?= h($row['total_score']) ?> 点</p>

    <p>
      <a href="detail.php?id=<?= h($row['id']) ?>&edit=1">修正</a>
    </p>

    <form action="delete.php" method="POST" style="display:inline;" onsubmit="return confirm('本当に削除しますか？');">
      <input type="hidden" name="id" value="<?= h($row['id']) ?>">
      <button type="submit">削除</button>
    </form>

  <?php endif; ?>
  <!-- ＊＊＊＊＊＊＊＊通常表示 ここまで＊＊＊＊＊＊＊＊ -->

  <p><a href="score.php">← スコア一覧に戻る</a></p>
  <footer>@nihongo-note all right reserved.</footer>

  <script src="js/index.js"></script>
</body>

</html>