<?php

require_once('./funcs.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$student_id = $_GET['student'] ?? '';
$student_data = [];

if ($student_id !== '') {
  $pdo = db_conn();
  $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :student_id");
  $stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
  $stmt->execute();
  $student_data = $stmt->fetch(PDO::FETCH_ASSOC);
}


// //1. POSTデータ取得
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $school = $_POST['school'];
  $name = $_POST['name'];
  $year = $_POST['year'];
  $class = $_POST['class'];
  $gender = $_POST['gender'];
  $date = $_POST['date'];
  $lang = $_POST['language'];

  //スコア計算
  $scores = [];

  // 各セクションごとに合計を計算
  $sections = [
    'q0_1' => ['q0_1_1', 'q0_1_2', 'q0_1_3', 'q0_1_4', 'q0_1_5', 'q0_1_6', 'q0_1_7'],
    'q0_2' => ['q0_2_1', 'q0_2_2', 'q0_2_3', 'q0_2_4', 'q0_2_5', 'q0_2_6', 'q0_2_7', 'q0_2_8', 'q0_2_9', 'q0_2_10'],
    'q0_3' => ['q0_3_1', 'q0_3_2', 'q0_3_3'],
  ];

  // 各小問の値を取り込み、スコアに変換
  foreach ($sections as $key => $questionList) {
    $sectionTotal = 0;
    foreach ($questionList as $q) {
      $value = isset($_POST[$q]) ? (int)$_POST[$q] : 0;
      $scores[$q] = $value;
      $sectionTotal += $value;
    }
    $scores[$key . '_score'] = $sectionTotal;
  }

  // JSから送信された値
  $scores['hiragana_score'] = isset($_POST['hiragana_score']) ? (int)$_POST['hiragana_score'] : 0;
  $scores['katakana_score'] = isset($_POST['katakana_score']) ? (int)$_POST['katakana_score'] : 0;

  // q0_4_score もフォームに送信されていれば取得
  $scores['q0_4_score'] = $scores['hiragana_score'] + $scores['katakana_score'];

  // 最終合計
  $totalScore =
    $scores['q0_1_score'] +
    $scores['q0_2_score'] +
    $scores['q0_3_score'] +
    $scores['q0_4_score'];
  $scores['total_score'] = $totalScore;

  //2. DB接続します
  // try {
  //     $pdo = new PDO('mysql:dbname=gs_db_leveltest3;charset=utf8;host=localhost','root','');
  //   } catch (PDOException $e) {
  //     exit('DBConnectError:'.$e->getMessage());
  //   }
  $pdo = db_conn();

  //３．データ登録SQL作成
  // 1. SQL文を用意

  // 1. SQL文を用意
  $sql = "INSERT INTO gs_leveltest3_01 (
    date, name, school, year, class, gender, language,
    q0_1_1, q0_1_2, q0_1_3, q0_1_4, q0_1_5, q0_1_6, q0_1_7, q0_1_score,
    q0_2_1, q0_2_2, q0_2_3, q0_2_4, q0_2_5, q0_2_6, q0_2_7, q0_2_8, q0_2_9, q0_2_10, q0_2_score,
    q0_3_1, q0_3_2, q0_3_3, q0_3_score,
    hiragana_score, katakana_score, q0_4_score, total_score, selected_hiragana, selected_katakana, unselected_hiragana, unselected_katakana
) VALUES (
    :date, :name, :school, :year, :class, :gender, :language,
    :q0_1_1, :q0_1_2, :q0_1_3, :q0_1_4, :q0_1_5, :q0_1_6, :q0_1_7, :q0_1_score,
    :q0_2_1, :q0_2_2, :q0_2_3, :q0_2_4, :q0_2_5, :q0_2_6, :q0_2_7, :q0_2_8, :q0_2_9, :q0_2_10, :q0_2_score,
    :q0_3_1, :q0_3_2, :q0_3_3, :q0_3_score,
    :hiragana_score, :katakana_score, :q0_4_score, :total_score, :selected_hiragana, :selected_katakana, :unselected_hiragana, :unselected_katakana
)";

  $stmt = $pdo->prepare($sql);

  // 基本情報
  $stmt->bindValue(':date', $_POST['date'], PDO::PARAM_STR);
  $stmt->bindValue(':name', $_POST['name'], PDO::PARAM_STR);
  $stmt->bindValue(':school', $_POST['school'], PDO::PARAM_STR);
  $stmt->bindValue(':year', $_POST['year'], PDO::PARAM_STR);
  $stmt->bindValue(':class', $_POST['class'], PDO::PARAM_STR);
  $stmt->bindValue(':gender', $_POST['gender'], PDO::PARAM_STR);
  $stmt->bindValue(':language', $_POST['language'], PDO::PARAM_STR);

  // q0_1
  for ($i = 1; $i <= 7; $i++) {
    $stmt->bindValue(":q0_1_{$i}", (int)$_POST["q0_1_{$i}"], PDO::PARAM_INT);
  }
  $stmt->bindValue(':q0_1_score', $scores['q0_1_score'], PDO::PARAM_INT);

  // q0_2
  for ($i = 1; $i <= 10; $i++) {
    $stmt->bindValue(":q0_2_{$i}", (int)$_POST["q0_2_{$i}"], PDO::PARAM_INT);
  }
  $stmt->bindValue(':q0_2_score', $scores['q0_2_score'], PDO::PARAM_INT);

  // q0_3
  for ($i = 1; $i <= 3; $i++) {
    $stmt->bindValue(":q0_3_{$i}", (int)$_POST["q0_3_{$i}"], PDO::PARAM_INT);
  }
  $stmt->bindValue(':q0_3_score', $scores['q0_3_score'], PDO::PARAM_INT);

  // 書く問題２スコア
  $stmt->bindValue(':hiragana_score', $scores['hiragana_score'], PDO::PARAM_INT);
  $stmt->bindValue(':katakana_score', $scores['katakana_score'], PDO::PARAM_INT);
  $stmt->bindValue(':selected_hiragana', $_POST['selected_hiragana'], PDO::PARAM_STR);
  $stmt->bindValue(':selected_katakana', $_POST['selected_katakana'], PDO::PARAM_STR);
  $stmt->bindValue(':unselected_hiragana', $_POST['unselected_hiragana'], PDO::PARAM_STR);
  $stmt->bindValue(':unselected_katakana', $_POST['unselected_katakana'], PDO::PARAM_STR);

  $stmt->bindValue(':q0_4_score', $scores['q0_4_score'], PDO::PARAM_INT);

  // 合計スコア
  $stmt->bindValue(':total_score', $scores['total_score'], PDO::PARAM_INT);

  // 実行
  $status = $stmt->execute();


  //４．データ登録処理後
  if ($status === false) {
    //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    exit('ErrorMessage:' . $error[2]);
  } else {
    header("Location: score.php");
    exit;
  }
}

?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>にほんごノート（レベルチェックーレベル０）</title>
  <link rel="icon" type="image/png" href="img/favicon2.png">
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
    <a href="level0.php">レベル０</a>
    <a href="level1.php">レベル１</a>
    <a href="level2.php">レベル２</a>
    <a href="score.php">結果一覧</a>
    <a href="curriculum.php">カリキュラム一覧</a>
    <a href="plan.php">指導計画書発行</a>
  </nav>
  <main>

    <h2>レベルチェックーレベル0</h2>



    <form action="level0.php" method="GET" onsubmit="return false;">
      <fieldset>
        <legend>実施児童・生徒番号を入力してください</legend>
        <label for="student">児童・生徒番号（例：STU001）：</label>
        <input type="text" name="student" id="student" required>
        <button type="submit" onclick="location.href='level0.php?student=' + document.getElementById('student').value;">検索</button>
      </fieldset>
    </form>
    <form action="level0.php" method="POST" onsubmit="return handleSubmit()">
      <fieldset>
        <legend>基本情報</legend>

        <div class="inline-field">
          <input type="text" name="school" id="school" class="long"
            value="<?= h($student_data['school'] ?? '') ?>">

          <input type="text" name="year" id="year" class="short"
            value="<?= h($student_data['grade'] ?? '') ?>">

          <input type="text" name="class" id="class" class="short"
            value="<?= h($student_data['class'] ?? '') ?>">

          <input type="text" name="name" id="name" class="long"
            value="<?= h($student_data['name'] ?? '') ?>">

          <input type="text" name="gender" id="gender" class="short"
            value="<?= h($student_data['gender'] ?? '') ?>">



          <!-- <label for="name">学校名：</label>
          <input type="text" name="school" id="name" class="long" required />

          <label for="year"></label>
          <input type="text" name="year" id="year" class="short" required />年

          <label for="class"></label>
          <input
            type="text"
            name="class"
            id="class"
            class="short"
            required />組
        </div>

        <div class="inline-field">
          <label for="name">名　前：</label>
          <input
            type="text"
            name="name"
            id="name"
            class="long"
            required />

          <label for="gender">性別：</label>
          <input
            type="text"
            name="gender"
            id="gender"
            class="short"
            required /> -->

          <label for="date">実施日：</label>
          <input type="date" name="date" id="date" class="medium" required />
        </div>
      </fieldset>

      <fieldset>
        <legend id=q0_1>1. 聞いて答える問題</legend>

        <label for="languageSelect">母語を選んでください（
          select your language）：</label>
        <select id="languageSelect" name="language">
          <option value="ja">日本語</option>
          <option value="en">English</option>
          <option value="zh">中文 (Chinese)</option>
          <option value="zh-TW">中文（台湾）(Traditional Chinese)</option>
          <option value="yue">廣東話 (Cantonese)</option>
          <option value="tl">Filipino (Tagalog)</option>
          <option value="vi">Tiếng Việt (Vietnamese)</option>
          <option value="pt">Português (Portuguese)</option>
          <option value="es">Español (Spanish)</option>
          <option value="ne">नेपाली (Nepali)</option>
          <option value="en">English</option>
          <option value="my">မြန်မာစာ (Burmese)</option>
          <option value="ko">한국어 (Korean)</option>
          <option value="mn">Монгол (Mongolian)</option>
          <option value="uz">O‘zbekcha (Uzbek)</option>
          <option value="th">ไทย (Thai)</option>
          <option value="id">Bahasa Indonesia (Indonesian)</option>
          <option value="fr">Français (French)</option>
          <option value="hi">हिन्दी (Hindi)</option>
          <option value="bn">বাংলা (Bengali)</option>
          <option value="ur">اردو (Urdu)</option>
          <option value="ar">العربية (Arabic)</option>
          <option value="fa">فارسی (Persian)</option>
          <option value="ms">Bahasa Melayu (Malay)</option>
          <option value="ru">Русский (Russian)</option>
          <option value="uk">Українська (Ukrainian)</option>
          <option value="tr">Türkçe (Turkish)</option>
          <option value="de">Deutsch (German)</option>
          <option value="ro">Română (Romanian)</option>
          <option value="pl">Polski (Polish)</option>
          <option value="it">Italiano (Italian)</option>
          <option value="sv">Svenska (Swedish)</option>
          <option value="si">සිංහල (Sinhala)</option>
          <option value="km">ភាសាខ្មែរ (Khmer)</option>
          <option value="ta">தமிழ் (Tamil)</option>
          <option value="tg">Тоҷикӣ (Tajik)</option>
          <option value="ceb">Cebuano (Cebuano)</option>
          <option value="ps">پښتو (Pashto)</option>
          <option value="el">Ελληνικά (Greek)</option>
          <option value="hu">Magyar (Hungarian)</option>
          <option value="bo">བོད་སྐད་ (Ladakhi / Tibetan)</option>
          <option value="ky">Кыргызча (Kyrgyz)</option>
          <option value="be">Беларуская (Belarusian)</option>
        </select>

        <p class="translated-instruction">
          これから日本語で質問をします。聞いて答えてください。もし聞かれている内容がわからない時は、「わからない」と言うか、首を横に振ってください。
        </p>
        <table border="1">
          <thead>
            <tr>
              <th>質問</th>
              <th colspan="2">回答</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1. あなたの名前は？ <button type="button" onclick="speakWithGoogle('あなたの名前は？')">🔊</button></td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_1_1">
                  <button type="button" class="answerBtn" data-value="5">言える</button>
                  <button type="button" class="answerBtn" data-value="0">言えない</button>
                </div>
                <input type="hidden" name="q0_1_1" id="q0_1_1" value="" required />
              </td>
            </tr>

            <tr>
              <td>2. どこから来ましたか？ <button type="button" onclick="speakWithGoogle('どこから来ましたか')">🔊</button></td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_1_2">
                  <button type="button" class="answerBtn" data-value="5">言える</button>
                  <button type="button" class="answerBtn" data-value="0">言えない</button>
                </div>
                <input type="hidden" name="q0_1_2" id="q0_1_2" value="" required />
              </td>
            </tr>

            <tr>
              <td>3. 何歳ですか？ <button type="button" onclick="speakWithGoogle('何歳ですか')">🔊</button></td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_1_3">
                  <button type="button" class="answerBtn" data-value="5">言える</button>
                  <button type="button" class="answerBtn" data-value="0">言えない</button>
                </div>
                <input type="hidden" name="q0_1_3" id="q0_1_3" value="" required />
              </td>
            </tr>

            <tr>
              <td>4. 今日は、何曜日ですか？ <button type="button" onclick="speakWithGoogle('今日は、何曜日ですか')">🔊</button></td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_1_4">
                  <button type="button" class="answerBtn" data-value="5">言える</button>
                  <button type="button" class="answerBtn" data-value="0">言えない</button>
                </div>
                <input type="hidden" name="q0_1_4" id="q0_1_4" value="" required />
              </td>
            </tr>

            <tr>
              <td>5. 明日は、何日ですか？ <button type="button" onclick="speakWithGoogle('明日は、何日ですか')">🔊</button></td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_1_5">
                  <button type="button" class="answerBtn" data-value="5">言える</button>
                  <button type="button" class="answerBtn" data-value="0">言えない</button>
                </div>
                <input type="hidden" name="q0_1_5" id="q0_1_5" value="" required />
              </td>
            </tr>

            <tr>
              <td>6. 何時ですか？ <a href="img/watch.png">絵を表示</a> <button type="button" onclick="speakWithGoogle('何時ですか')">🔊</button></td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_1_6">
                  <button type="button" class="answerBtn" data-value="5">言える</button>
                  <button type="button" class="answerBtn" data-value="0">言えない</button>
                </div>
                <input type="hidden" name="q0_1_6" id="q0_1_6" value="" required />
              </td>
            </tr>

            <tr>
              <td>7. これは何ですか？ <a href="img/pencil.png">絵を表示</a> <button type="button" onclick="speakWithGoogle('これは何ですか')">🔊</button></td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_1_7">
                  <button type="button" class="answerBtn" data-value="5">言える</button>
                  <button type="button" class="answerBtn" data-value="0">言えない</button>
                </div>
                <input type="hidden" name="q0_1_7" id="q0_1_7" value="" required />
              </td>
            </tr>
          </tbody>
        </table>

        <p>Q0-1スコア: <span id="q0_1_score_display">0</span>点</p>
        <input type="hidden" id="q0_1_score" name="q0_1_score" value="0">

      </fieldset>

      <fieldset>
        <legend id=q0_2>2. 読む問題</legend>

        <p class="translated-instruction">
          下の文字を読んでください。わからない時は、「わからない」と言うか、首を横に振ってください。
        </p>
        <table border="1">
          <thead>
            <tr>
              <th>問題</th>
              <th colspan="2">回答</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1. あさ</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_2_1">
                  <button type="button" class="answerBtn" data-value="2">読める</button>
                  <button type="button" class="answerBtn" data-value="0">読めない</button>
                </div>
                <input type="hidden" name="q0_2_1" id="q0_2_1" value="" required />
              </td>
            </tr>
            <tr>
              <td>2. おはよう</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_2_2">
                  <button type="button" class="answerBtn" data-value="2">読める</button>
                  <button type="button" class="answerBtn" data-value="0">読めない</button>
                </div>
                <input type="hidden" name="q0_2_2" id="q0_2_2" value="" required />
              </td>
            </tr>
            <tr>
              <td>3. がっこう</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_2_3">
                  <button type="button" class="answerBtn" data-value="2">読める</button>
                  <button type="button" class="answerBtn" data-value="0">読めない</button>
                </div>
                <input type="hidden" name="q0_2_3" id="q0_2_3" value="" required />
              </td>
            </tr>
            <tr>
              <td>4. ねこ</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_2_4">
                  <button type="button" class="answerBtn" data-value="2">読める</button>
                  <button type="button" class="answerBtn" data-value="0">読めない</button>
                </div>
                <input type="hidden" name="q0_2_4" id="q0_2_4" value="" required />
              </td>
            </tr>
            <tr>
              <td>5. テレビ</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_2_5">
                  <button type="button" class="answerBtn" data-value="2">読める</button>
                  <button type="button" class="answerBtn" data-value="0">読めない</button>
                </div>
                <input type="hidden" name="q0_2_5" id="q0_2_5" value="" required />
              </td>
            </tr>
            <tr>
              <td>6. ピーマン</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_2_6">
                  <button type="button" class="answerBtn" data-value="2">読める</button>
                  <button type="button" class="answerBtn" data-value="0">読めない</button>
                </div>
                <input type="hidden" name="q0_2_6" id="q0_2_6" value="" required />
              </td>
            </tr>
            <tr>
              <td>7. バスケットボール</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_2_7">
                  <button type="button" class="answerBtn" data-value="2">読める</button>
                  <button type="button" class="answerBtn" data-value="0">読めない</button>
                </div>
                <input type="hidden" name="q0_2_7" id="q0_2_7" value="" required />
              </td>
            </tr>
            <tr>
              <td>8. おおきい</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_2_8">
                  <button type="button" class="answerBtn" data-value="2">読める</button>
                  <button type="button" class="answerBtn" data-value="0">読めない</button>
                </div>
                <input type="hidden" name="q0_2_8" id="q0_2_8" value="" required />
              </td>
            </tr>
            <tr>
              <td>9. これは、かさです。</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_2_9">
                  <button type="button" class="answerBtn" data-value="2">読める</button>
                  <button type="button" class="answerBtn" data-value="0">読めない</button>
                </div>
                <input type="hidden" name="q0_2_9" id="q0_2_9" value="" required />
              </td>
            </tr>
            <tr>
              <td>10. パンを　たべます。</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_2_10">
                  <button type="button" class="answerBtn" data-value="2">読める</button>
                  <button type="button" class="answerBtn" data-value="0">読めない</button>
                </div>
                <input type="hidden" name="q0_2_10" id="q0_2_10" value="" required />
              </td>
            </tr>
          </tbody>
        </table>

        <p>Q0-2スコア: <span id="q0_2_score_display">0</span>点</p>
        <input type="hidden" id="q0_2_score" name="q0_2_score" value="0">
      </fieldset>

      <fieldset>
        <legend id=q0_3>3. 書く問題１</legend>

        <p class="translated-instruction">
          以下は自己紹介です。空欄に当てはまるこどばをノートに書いてください。わからない時は、「わからない」と言うか、首を横に振ってください。
        </p>
        <table border="1">
          <thead>
            <tr>
              <th>自己紹介 じこしょうかい</th>
              <th colspan="2">回答</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1. わたしは、_________________________です。</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_3_1">
                  <button type="button" class="answerBtn" data-value="5">書ける</button>
                  <button type="button" class="answerBtn" data-value="0">書けない</button>
                </div>
                <input type="hidden" name="q0_3_1" id="q0_3_1" value="" required />
              </td>
            </tr>
            <tr>
              <td>2. _________________________から　きました。</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_3_2">
                  <button type="button" class="answerBtn" data-value="5">書ける</button>
                  <button type="button" class="answerBtn" data-value="0">書けない</button>
                </div>
                <input type="hidden" name="q0_3_2" id="q0_3_2" value="" required />
              </td>
            </tr>
            <tr>
              <td>3. _____________________________がすきです。</td>
              <td colspan="2">
                <div class="buttonGroup" data-question="q0_3_3">
                  <button type="button" class="answerBtn" data-value="5">書ける</button>
                  <button type="button" class="answerBtn" data-value="0">書けない</button>
                </div>
                <input type="hidden" name="q0_3_3" id="q0_3_3" value="" required />
              </td>
            </tr>
          </tbody>
        </table>

        <p>Q0-3スコア: <span id="q0_3_score_display">0</span>点</p>
        <input type="hidden" id="q0_3_score" name="q0_3_score" value="0">

      </fieldset>

      <fieldset>
        <legend id=q0_3>4. 書く問題２</legend>

        <p class="translated-instruction">
          ひらがなで書きましょう。空欄に当てはまるひらがなを書いてください。わからない時は、先に進んでください。

        <table class="hiragana-table">
          <tbody>ひらがな表ーたて書き
            <tr>
              <td><span class="toggle-cell" data-char="ん" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="わ">わ</span></td>
              <td><span class="toggle-cell" data-char="ら" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="や" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="ま">ま</span></td>
              <td><span class="toggle-cell" data-char="は" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="な">な</span></td>
              <td><span class="toggle-cell" data-char="た">た</span></td>
              <td><span class="toggle-cell" data-char="さ" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="か">か</span></td>
              <td><span class="toggle-cell" data-char="あ">あ</span></td>
            </tr>
            <tr>
              <td class="gray"></td>
              <td class="gray"></td>
              <td><span class="toggle-cell" data-char="り">り</span></td>
              <td class="gray"></td>
              <td><span class="toggle-cell" data-char="み" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="ひ">ひ</span></td>
              <td><span class="toggle-cell" data-char="に">に</span></td>
              <td><span class="toggle-cell" data-char="ち" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="し">し</span></td>
              <td><span class="toggle-cell" data-char="き" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="い">い</span></td>
            </tr>
            <tr>
              <td class="gray"></td>
              <td class="gray"></td>
              <td><span class="toggle-cell" data-char="る">る</span></td>
              <td><span class="toggle-cell" data-char="ゆ">ゆ</span></td>
              <td><span class="toggle-cell" data-char="む" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="ふ" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="ぬ" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="つ">つ</span></td>
              <td><span class="toggle-cell" data-char="す">す</span></td>
              <td><span class="toggle-cell" data-char="く">く</span></td>
              <td><span class="toggle-cell" data-char="う" data-value="1">　</span></td>
            </tr>
            <tr>
              <td class="gray"></td>
              <td class="gray"></td>
              <td><span class="toggle-cell" data-char="れ" data-value="1">　</span></td>
              <td class="gray"></td>
              <td><span class="toggle-cell" data-char="め" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="へ">へ</span></td>
              <td><span class="toggle-cell" data-char="ね" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="て" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="せ">せ</span></td>
              <td><span class="toggle-cell" data-char="け" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="え">え</span></td>
            </tr>
            <tr>
              <td class="gray"></td>
              <td><span class="toggle-cell" data-char="を">を</span></td>
              <td><span class="toggle-cell" data-char="ろ">ろ</span></td>
              <td><span class="toggle-cell" data-char="よ" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="も">も</span></td>
              <td><span class="toggle-cell" data-char="ほ">ほ</span></td>
              <td><span class="toggle-cell" data-char="の">の</span></td>
              <td><span class="toggle-cell" data-char="と">と</span></td>
              <td><span class="toggle-cell" data-char="そ" data-value="1">　</span></td>
              <td><span class="toggle-cell" data-char="こ">こ</span></td>
              <td><span class="toggle-cell" data-char="お" data-value="1">　</span></td>
            </tr>
          </tbody>
        </table>

        <p>書けたひらがなの数：<span id="hiragana_count">0</span> 点</p>
        <input type="hidden" id="hiragana_score" name="hiragana_score" value="0">
        <input type="hidden" name="selected_hiragana" id="selected_hiragana">
        <input type="hidden" name="unselected_hiragana" id="unselected_hiragana">
        <br>
        <p class="translated-instruction">
          カタカナで書きましょう。空欄に当てはまるカタカナを書いてください。わからない時は、終わりにしてください。

        <table class="katakana-table">
          <tr>
            <td><span class="toggle-cell" data-char="ン" data-value="1">　</span></td>
            <td><span class="toggle-cell" data-char="ワ">ワ</span></td>
            <td><span class="toggle-cell" data-char="ラ">ラ</span></td>
            <td><span class="toggle-cell" data-char="ヤ">ヤ</span></td>
            <td><span class="toggle-cell" data-char="マ">マ</span></td>
            <td><span class="toggle-cell" data-char="ハ">ハ</span></td>
            <td><span class="toggle-cell" data-char="ナ">ナ</span></td>
            <td><span class="toggle-cell" data-char="タ">タ</span></td>
            <td><span class="toggle-cell" data-char="サ">サ</span></td>
            <td><span class="toggle-cell" data-char="カ">カ</span></td>
            <td><span class="toggle-cell" data-char="ア" data-value="1">　</span></td>
          </tr>
          <tr>
            <td class="gray"></td>
            <td class="gray"></td>
            <td><span class="toggle-cell" data-char="リ">リ</span></td>
            <td class="gray"></td>
            <td><span class="toggle-cell" data-char="ミ">ミ</span></td>
            <td><span class="toggle-cell" data-char="ヒ" data-value="1">　</span></td>
            <td><span class="toggle-cell" data-char="ニ">ニ</span></td>
            <td><span class="toggle-cell" data-char="チ" data-value="1">　</span></td>
            <td><span class="toggle-cell" data-char="シ" data-value="1">　</span></td>
            <td><span class="toggle-cell" data-char="キ">キ</span></td>
            <td><span class="toggle-cell" data-char="イ">イ</span></td>
          </tr>
          <tr>
            <td class="gray"></td>
            <td class="gray"></td>
            <td><span class="toggle-cell" data-char="ル">ル</span></td>
            <td><span class="toggle-cell" data-char="ユ">ユ</span></td>
            <td><span class="toggle-cell" data-char="ム">ム</span></td>
            <td><span class="toggle-cell" data-char="フ">フ</span></td>
            <td><span class="toggle-cell" data-char="ヌ">ヌ</span></td>
            <td><span class="toggle-cell" data-char="ツ" data-value="1">　</span></td>
            <td><span class="toggle-cell" data-char="ス">ス</span></td>
            <td><span class="toggle-cell" data-char="ク">ク</span></td>
            <td><span class="toggle-cell" data-char="ウ">ウ</span></td>
          </tr>
          <tr>
            <td class="gray"></td>
            <td class="gray"></td>
            <td><span class="toggle-cell" data-char="レ">ヘ</span></td>
            <td class="gray"></td>
            <td><span class="toggle-cell" data-char="メ">メ</span></td>
            <td><span class="toggle-cell" data-char="ヘ">ヘ</span></td>
            <td><span class="toggle-cell" data-char="ネ" data-value="1">　</span></td>
            <td><span class="toggle-cell" data-char="テ" data-value="1">　</span></td>
            <td><span class="toggle-cell" data-char="セ">セ</span></td>
            <td><span class="toggle-cell" data-char="ケ" data-value="1">　</span></td>
            <td><span class="toggle-cell" data-char="エ">エ</span></td>
          </tr>
          <tr>
            <td class="gray"></td>
            <td><span class="toggle-cell" data-char="ヲ">ヲ</span></td>
            <td><span class="toggle-cell" data-char="ロ">ロ</span></td>
            <td><span class="toggle-cell" data-char="ヨ" data-value="1">　</span></td>
            <td><span class="toggle-cell" data-char="モ">モ</span></td>
            <td><span class="toggle-cell" data-char="ホ">ホ</span></td>
            <td><span class="toggle-cell" data-char="ノ">ノ</span></td>
            <td><span class="toggle-cell" data-char="ト">ト</span></td>
            <td><span class="toggle-cell" data-char="ソ">ソ</span></td>
            <td><span class="toggle-cell" data-char="コ">コ</span></td>
            <td><span class="toggle-cell" data-char="オ">オ</span></td>
          </tr>
        </table>

        <p>書けたカタカナの数：<span id="katakana_count">0</span> 点</p>
        <input type="hidden" id="katakana_score" name="katakana_score" value="0">
        <input type="hidden" name="selected_katakana" id="selected_katakana">
        <input type="hidden" name="unselected_katakana" id="unselected_katakana">

        <p>Q0-4スコア: <span id="q0_4_score_display">0</span>点</p>
        <input type="hidden" id="q0_4_score" name="q0_4_score" value="0">

      </fieldset>

      <p>総合スコア: <span id="total_score_display">0</span>点</p>
      <input type="hidden" id="total_score" name="total_score" value="0">

      <br>
      <button type="submit">採点する</button>
    </form>
  </main>

  <footer>@nihongo-note all right reserved.</footer>

  <script src="js/index.js"></script>
</body>

</html>