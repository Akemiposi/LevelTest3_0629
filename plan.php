<?php
require_once('./auth_teacher.php');

// ロールに応じて戻り先を決定
if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $back_url = 'score.php';
} else {
    $back_url = 'teacher.php';
}

// -----1,生徒ID指定 → 最新テスト結果の取得-----

$student_id = $_GET['student_id'] ?? '';
if ($student_id === '') {
  die('student_idが指定されていません');
}

$pdo = db_conn();


// レベル0 最新記録取得（生徒情報付き）
$sql0 = "SELECT * FROM gs_leveltest3_01 WHERE student_id = :student_id ORDER BY date DESC LIMIT 1";
$stmt0 = $pdo->prepare($sql0);
$stmt0->bindValue(':student_id', $student_id);
$stmt0->execute();
$level0 = $stmt0->fetch(PDO::FETCH_ASSOC);

// レベル1 最新記録取得
$sql1 = "SELECT * FROM leveltest_1 WHERE student_id = :student_id ORDER BY date DESC LIMIT 1";
$stmt1 = $pdo->prepare($sql1);
$stmt1->bindValue(':student_id', $student_id);
$stmt1->execute();
$level1 = $stmt1->fetch(PDO::FETCH_ASSOC);

// 講師名取得
$sql2 = "SELECT name FROM admin_teacher WHERE teacher_id = :teacher_id";
$stmt2 = $pdo->prepare($sql2);
$stmt2->bindValue(':teacher_id', $level0['teacher_id']);
$stmt2->execute();
$teacher = $stmt2->fetch(PDO::FETCH_ASSOC);


// -----2,正解率計算と90%以上のセクション抽出-----

$full_score = 200;
$section_scores = [
  'q0_1' => $level0['q0_1_score'] ?? 0,
  'q0_2' => $level0['q0_2_score'] ?? 0,
  'q0_3' => $level0['q0_3_score'] ?? 0,
  'q0_4' => $level0['q0_4_score'] ?? 0,
  'q1_1' => $level1['q1_1_score'] ?? 0,
  'q1_2' => $level1['q1_2_score'] ?? 0,
  'q1_3' => $level1['q1_3_score'] ?? 0,
];

$qualified_sections = [];
foreach ($section_scores as $key => $score) {
    $rate = ($score / $full_score) * 100;
    if ($rate >= 90) {
        $qualified_sections[] = $key;
    }
}

// curriculum テーブルから test_compare 順に取得
$stmt = $pdo->prepare("SELECT test_compare, instruction FROM curriculum ORDER BY id ASC");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 次に進む位置を探す
$start_index = 0;
foreach ($rows as $i => $row) {
    if (in_array($row['test_compare'], $qualified_sections)) {
        $start_index = $i + 1; // 次の項目から開始
    }
}

// 指導項目10個（前期5＋後期5）
$selected = array_slice(array_column($rows, 'instruction'), $start_index, 10);
$content1 = implode("\n", array_slice($selected, 0, 5)); // 前期
$content2 = implode("\n", array_slice($selected, 5, 5)); // 後期

// -----3,curriculumから該当セクションの学習項目を取得-----

// $content1 = '';
// $content2 = '';

// if (!empty($qualified_sections)) {
//   $placeholders = implode(',', array_fill(0, count($qualified_sections), '?'));
//   $sql = "SELECT test_compare, instruction FROM curriculum WHERE test_compare IN ($placeholders) ORDER BY test_compare, id ASC";
//   $stmt = $pdo->prepare($sql);
//   $stmt->execute($qualified_sections);
//   $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

//   // 指導項目だけ取り出す
//   $all_items = array_column($results, 'instruction');
//   $content1 = implode("\n", array_slice($all_items, 0, 5)); // 前期
//   $content2 = implode("\n", array_slice($all_items, 5, 5)); // 後期
// }





// -----4,「令和形式」の日付を生成-----
function formatReiwaDate($timestamp = null)
{
  $timestamp = $timestamp ?? time();
  $year = (int)date('Y', $timestamp);
  $month = (int)date('n', $timestamp);
  $day = (int)date('j', $timestamp);

  // 令和開始日：2019年5月1日以降
  if ($year > 2019 || ($year == 2019 && $month >= 5)) {
    $reiwaYear = $year - 2018;
    $reiwaYearStr = $reiwaYear === 1 ? '元' : $reiwaYear;
    return "令和{$reiwaYearStr}年{$month}月{$day}日";
  } else {
    // 平成以前（必要に応じて拡張可）
    return "{$year}年{$month}月{$day}日";
  }
}

// -----5,年間指導計画の○自動入力-----

function generateFullYearPlanMarks($createdMonth = null)
{
  $createdMonth = $createdMonth ?? (int)date('n'); // 例: 7月 → 7
  $allMonths = [4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2, 3]; // 学年順
  $marks = [
    'this_year' => array_fill(0, 12, ''),
    'next_year' => array_fill(0, 12, ''),
  ];

  $startIndex = array_search($createdMonth, $allMonths);
  if ($startIndex === false) return $marks;

  for ($i = 0; $i < 12; $i++) {
    $symbol = ($i < 6) ? '◎' : '○';
    $monthIndex = $startIndex + $i;
    if ($monthIndex < 12) {
      $marks['this_year'][$monthIndex] = $symbol;
    } else {
      $marks['next_year'][$monthIndex - 12] = $symbol;
    }
  }

  return $marks;
}

// 実行
$planMarks = generateFullYearPlanMarks();  // 現在の月から◎○をセット


// -----レベル０判定------

// 総合得点（点数）
$level0_total_score = $level0['total_score'] ?? 0;

// 判定（任意基準）
if ($level0_total_score >= 70) {
  $level0_judge = '◎';
} elseif ($level0_total_score >= 50) {
  $level0_judge = '○';
} else {
  $level0_judge = '△';
}




// -----レベル1判定------

// 総合点
$level1_total_score = $level1['q1_total_score'] ?? 0;;

// 判定（自由にカスタマイズ可能）
if ($level1_total_score >= 54) {
  $level1_judge = '◎'; // 90%〜（60点満点中54点〜）
} elseif ($level1_total_score >= 40) {
  $level1_judge = '○';
} else {
  $level1_judge = '△';
}

// -----3,curriculumから該当セクションの学習項目を取得（改訂）-----

$full_score = 20;
$section_scores = [
  'q0_1' => $level0['q0_1_score'] ?? 0,
  'q0_2' => $level0['q0_2_score'] ?? 0,
  'q0_3' => $level0['q0_3_score'] ?? 0,
  'q0_4' => $level0['q0_4_score'] ?? 0,
  'q1_1' => $level1['q1_1_score'] ?? 0,
  'q1_2' => $level1['q1_2_score'] ?? 0,
  'q1_3' => $level1['q1_3_score'] ?? 0,
];

$qualified_sections = [];
foreach ($section_scores as $key => $score) {
    $rate = ($score / $full_score) * 100;
    if ($rate >= 90) {
        $qualified_sections[] = $key;
    }
}

// curriculum テーブルから test_compare 順に取得
$stmt = $pdo->prepare("SELECT test_compare, instruction FROM curriculum ORDER BY id ASC");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 次に進む位置を探す
$start_index = 0;
foreach ($rows as $i => $row) {
    if (in_array($row['test_compare'], $qualified_sections)) {
        $start_index = $i + 1; // 次の項目から開始
    }
}

// 指導項目10個（前期5＋後期5）
$selected = array_slice(array_column($rows, 'instruction'), $start_index, 10);
$content1 = implode("\n", array_slice($selected, 0, 3)); // 前期
$content2 = implode("\n", array_slice($selected, 4, 3)); // 後期

?>



<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>個別の指導計画 入力フォーム</title>
  <link rel="stylesheet" href="css/plan.css">
</head>

<body>
  <section class="level-results">
    <div class="level-box">
      <h2>【レベル０ 結果】</h2>
      <ul>
        <li>Q0-1 聞く：<span id="q0_1"><?= h($level0['q0_1_score'] ?? '') ?> / 35点</span></li>
        <li>Q0-2 読む：<span id="q0_2"><?= h($level0['q0_2_score'] ?? '') ?> / 20点</span></li>
        <li>Q0-3 書く：<span id="q0_3"><?= h($level0['q0_3_score'] ?? '') ?> / 15点</span></li>
        <li>Q0-4 かな：<span id="q0_4"><?= h($level0['q0_4_score'] ?? '') ?> / 30点</span></li>
        <li><strong>レベル０ 総合得点：<span id="total_score"><?= h($level0_total_score) ?>点</span></strong></li>
        <li><strong>レベル０ 判定：<span id="level0_judge"><?= h($level0_judge) ?></span></strong></li>
      </ul>
    </div>
    <div class="level-box">
      <h2>【レベル１ 結果】</h2>
      <ul>
        <li>Q1-1 聞く：<span id="q1_1"><?= h($level1['q1_1_score'] ?? '') ?> / 18点</span></li>
        <li>Q1-2 書く：<span id="q1_2"><?= h($level1['q1_2_score'] ?? '') ?> / 35点</span></li>
        <li>Q1-3 読解：<span id="q1_3"><?= h($level1['q1_3_score'] ?? '') ?> / 47点</span></li><br>
        <li><strong>レベル１ 総合得点：<span id="level1_total_score"><?= h($level1_total_score) ?>点</span></strong></li>
        <li><strong>レベル１ 判定：<span id="level1_judge"><?= h($level1_judge) ?></span></strong></li>
      </ul>
    </div>
  </section>
  <div class="print-controls">
 <button onclick="window.location.href='<?= $back_url ?>'">一覧に戻る</button>
  <button onclick="window.print()">印刷する</button>
</div>

  <main>
    <h1>指導計画書</h1>

    <table>
      <tr>
        <th>学校名</th>
        <td>
          <input type="text" name="school" value="<?= h($level0['school']) ?>">
        </td>
        <th>年組</th>
        <td style="text-align: center;">
          <input type="text" name="year" value="<?= h($level0['year'] ?? '') ?>" style="width: 40px; text-align: center;">年
          <input type="text" name="class" value="<?= h($level0['class'] ?? '') ?>" style="width: 40px; text-align: center;">組
        </td>
        <th>作成日</th>
        <td><input type="text" name="created_date" value="<?= formatReiwaDate() ?>"></td>
      </tr>
    </table>
    <table>
      <tr>
        <th>児童・生徒ID</th>
        <td colspan="2"><input type="text" name="student_id" value="<?= h($level0['student_id']) ?>"></td>
        <th>指導員ID</th>
        <td colspan="2"><input type="text" name="teacher_id" value="<?= h($level0['teacher_id']) ?>"></td>
      </tr>
      <tr>
        <th>児童・生徒名</th>
        <td colspan="2">
          <input type="text" name="student_name" value="<?= h($level0['name']) ?>">
        </td>
        <th>指導員名</th>
        <td colspan="2"><input type="text" name="teacher_name" value="<?= h($teacher['name'] ?? '') ?>"></td>
      </tr>
    </table>

    <table>
      <tr>
        <th>基本方針</th>
        <td colspan="5">
          <p name="policy" style="font-size: 13px">
            １、個別指導計画を作成し、日本語指導を行う。<br>
            ２、担任をはじめ、在籍校教員と講師とで連携し、個別指導計画の作成・評価・改善を行う。<br>
            ３、学校での児童・生徒の実態や取り出し指導の情報共有を行い、より効果的な指導に努める。<br>
            ４、基本的には、「聞く」「話す」「読む」「書く」の４技能の指導を行う。<br>
            ５、児童・生徒の実態に合わせ、生活適応指導・日本語基礎等の指導を行う。</p>
        </td>
      </tr>
      <tr>
        <th>日本語力</th>
        <td colspan="5">
          <select name="japanese_skill" style="border: none; outline: none; width: 100%;">
            <option value="">選択してください</option>
            <option value="1">日本語はほとんどわからない。家庭での会話も母語のみ。保護者も日本語がわからない。</option>
            <option value="2">自分の名前が書ける。あいさつができる。ひらがな・カタカナは読めるが、書けない。</option>
            <option value="3">ひらがなは書けるが、カタカナは書けない。二語文で話す。助詞が使えない。</option>
            <option value="4">ひらがな・カタカナは読み書きできる。ゆっくり話すと理解できる。</option>
            <option value="5">日常会話ができる。指示も理解できる。文章も読める。</option>
          </select>
        </td>
      </tr>
      <tr>
        <th>指導目標</th>
        <td colspan="5">
          <select name="japanese_skill" style="border: none; outline: none; width: 100%;">
            <option value="">選択してください</option>
            <option value="1">ひらがな・カタカナが読み書きできるようになる。（初級Aレベル）</option>
            <option value="2">小１漢字の読み書きができ漢字の自宅学習ができる（初級Bレベル）</option>
            <option value="3">動詞・形容詞が使えるようになる。いる・いない、ある・ないがわかる。（中級Aレベル）</option>
            <option value="4">動詞・形容詞の疑問形・肯定形・否定形が言えるようになる。（中級Bレベル）</option>
            <option value="5">方法・手段が言える。動詞・形容詞の過去表現（肯定・否定）がわかる。（中上級Aレベル）。</option>
          </select>
      </tr>
    </table>

    <h2>年間指導計画</h2>
    <table class="year-plan">
      <tr>
        <th>段階</th>
        <th>4月</th>
        <th>5月</th>
        <th>6月</th>
        <th>7月</th>
        <th>8月</th>
        <th>9月</th>
        <th>10月</th>
        <th>11月</th>
        <th>12月</th>
        <th>1月</th>
        <th>2月</th>
        <th>3月</th>
      </tr>
      <tr>
        <td>今年度</td>
        <?php
        $month_keys = ['apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec', 'jan', 'feb', 'mar'];
        foreach ($month_keys as $i => $key):
        ?>
          <td><input type="text" name="<?= $key ?>_mark" value="<?= h($planMarks['this_year'][$i]) ?>"></td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td>来年度</td>
        <?php foreach ($month_keys as $i => $key): ?>
          <td><input type="text" name="<?= $key ?>_mark" value="<?= h($planMarks['next_year'][$i]) ?>"></td>
        <?php endforeach; ?>
      </tr>
    </table>

    <h2>日本語指導内容</h2>
    <table style="table-layout: fixed; width: 100%;">
      <colgroup>
        <col style="width: 8%;">
        <col style="width: 46%;">
        <col style="width: 46%;">
      </colgroup>
      <tr>
        <th></th>
        <td>最初の6ヶ月</td>
        <td>その次の6ヶ月</td>
      </tr>
      <tr>
        <th>指導<br>項目</th>
        <td class="double-height"><textarea name="content1"><?= h($content1) ?></textarea></td> <!-- 前期 -->
        <td class="double-height"><textarea name="content2"><?= h($content2) ?></textarea></td> <!-- 後期 -->
      </tr>
    </table>

    <h2>学習状況評価</h2>
    <table style="table-layout: fixed; width: 100%;">
      <colgroup>
        <col style="width: 8%;">
        <col style="width: 46%;">
        <col style="width: 46%;">
      </colgroup>
      <tr>
        <th></th>
        <td>前期</td>
        <td>後期</td>
      </tr>
      <tr>
        <th>半期毎<br>手記入</th>
        <td class="double-height"><textarea name="evaluation1"></textarea></td>
        <td class="double-height"><textarea name="evaluation2"></textarea></td>
      </tr>
    </table>

  </main>
</body>

</html>