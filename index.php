<?php
require_once('funcs.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//1. POSTデータ取得
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $school = $_POST['school'];
  $name = $_POST['name'];
  $year = $_POST['year'];
  $class = $_POST['class'];
  $gender = $_POST['gender'];
  $date = $_POST['date'];
  $lang = $_POST['language'];

// スコア項目
  $questions = ['q0_1_1','q0_1_2','q0_1_3','q0_1_4','q0_1_5','q0_1_6','q0_1_7'];
  $scores = [];
  $totalScore = 0;


  foreach ($questions as $q) {
    $scores[$q] = (int)$_POST[$q];
    $totalScore += $scores[$q];
  }

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
$sql = "INSERT INTO
          gs_leveltest3_01(id, date, school, name, year, class, gender, language,
          q0_1_1, q0_1_2, q0_1_3, q0_1_4, q0_1_5, q0_1_6, q0_1_7, total_score)
        VALUES (NULL, :date, :school, :name, :year, :class, :gender, :language,
          :q0_1_1, :q0_1_2, :q0_1_3, :q0_1_4, :q0_1_5, :q0_1_6, :q0_1_7, :total_score)";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':date', $date, PDO::PARAM_STR);
$stmt->bindValue(':school', $school, PDO::PARAM_STR);
$stmt->bindValue(':name', $name, PDO::PARAM_STR);
$stmt->bindValue(':year', $year, PDO::PARAM_STR);
$stmt->bindValue(':class', $class, PDO::PARAM_STR);
$stmt->bindValue(':gender', $gender, PDO::PARAM_STR);
$stmt->bindValue(':language', $lang, PDO::PARAM_STR);
$stmt->bindValue(':q0_1_1', $scores['q0_1_1'], PDO::PARAM_INT);
$stmt->bindValue(':q0_1_2', $scores['q0_1_2'], PDO::PARAM_INT);
$stmt->bindValue(':q0_1_3', $scores['q0_1_3'], PDO::PARAM_INT);
$stmt->bindValue(':q0_1_4', $scores['q0_1_4'], PDO::PARAM_INT);
$stmt->bindValue(':q0_1_5', $scores['q0_1_5'], PDO::PARAM_INT);
$stmt->bindValue(':q0_1_6', $scores['q0_1_6'], PDO::PARAM_INT);
$stmt->bindValue(':q0_1_7', $scores['q0_1_7'], PDO::PARAM_INT);
$stmt->bindValue(':total_score', $totalScore, PDO::PARAM_INT);

//  3. 実行
$status = $stmt->execute();


//４．データ登録処理後
if($status === false){
  //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
  $error = $stmt->errorInfo();
  exit('ErrorMessage:'.$error[2]);
}else{
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
    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body>
    <header></header>

    <main>
     
      <h1>レベルチェック（レベル０）</h1>

      <form action="index.php" method="POST">
        <fieldset>
          <legend>基本情報</legend>
          <div class="inline-field">
            <label for="name">学校名：</label>
            <input type="text" name="school" id="name" class="long" required />

            <label for="year"></label>
            <input type="text" name="year" id="year" class="short" required />年

            <label for="class"></label>
            <input
              type="text"
              name="class"
              id="class"
              class="short"
              required
            />組
          </div>

          <div class="inline-field">
            <label for="name">名　前：</label>
            <input
              type="text"
              name="name"
              id="name"
              class="long"
              required
            />

            <label for="gender">性別：</label>
            <input
              type="text"
              name="gender"
              id="gender"
              class="short"
              required
            />

            <label for="date">実施日：</label>
            <input type="date" name="date" id="date" class="medium" required />
          </div>
        </fieldset>

        <fieldset>
          <legend id=q0_1>聞いて答える問題</legend>

          <label for="languageSelect">母語を選んでください（
            select your language）：</label>
          <select id="languageSelect" name="language">
            <option value="ja">日本語</option>
        <option value="en">English</option>
        <option value="zh">中文 (Chinese)</option>
        <option value="vi">Tiếng Việt (Vietnamese)</option>
        <option value="tl">Filipino (Tagalog)</option>
        <option value="ne">नेपाली (Nepali)</option>
        <option value="my">မြန်မာစာ (Burmese)</option>
        <option value="mn">Монгол (Mongolian)</option>
        <option value="uz">O‘zbekcha (Uzbek)</option>
          </select>

         <p id="translated-instruction">
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
        <input type="hidden" name="q0_1_1" value="" required />
      </td>
    </tr>

    <tr>
      <td>2. どこから来ましたか？ <button type="button" onclick="speakWithGoogle('どこから来ましたか')">🔊</button></td>
      <td colspan="2">
        <div class="buttonGroup" data-question="q0_1_2">
          <button type="button" class="answerBtn" data-value="5">言える</button>
          <button type="button" class="answerBtn" data-value="0">言えない</button>
        </div>
        <input type="hidden" name="q0_1_2" value="" required />
      </td>
    </tr>

    <tr>
      <td>3. 何歳ですか？ <button type="button" onclick="speakWithGoogle('何歳ですか')">🔊</button></td>
      <td colspan="2">
        <div class="buttonGroup" data-question="q0_1_3">
          <button type="button" class="answerBtn" data-value="5">言える</button>
          <button type="button" class="answerBtn" data-value="0">言えない</button>
        </div>
        <input type="hidden" name="q0_1_3" value="" required />
      </td>
    </tr>

    <tr>
      <td>4. 今日は、何曜日ですか？ <button type="button" onclick="speakWithGoogle('今日は、何曜日ですか')">🔊</button></td>
      <td colspan="2">
        <div class="buttonGroup" data-question="q0_1_4">
          <button type="button" class="answerBtn" data-value="5">言える</button>
          <button type="button" class="answerBtn" data-value="0">言えない</button>
        </div>
        <input type="hidden" name="q0_1_4" value="" required />
      </td>
    </tr>

    <tr>
      <td>5. 明日は、何日ですか？ <button type="button" onclick="speakWithGoogle('明日は、何日ですか')">🔊</button></td>
      <td colspan="2">
        <div class="buttonGroup" data-question="q0_1_5">
          <button type="button" class="answerBtn" data-value="5">言える</button>
          <button type="button" class="answerBtn" data-value="0">言えない</button>
        </div>
        <input type="hidden" name="q0_1_5" value="" required />
      </td>
    </tr>

    <tr>
      <td>6. 何時ですか？ <a href="img/watch.png">絵を表示</a> <button type="button" onclick="speakWithGoogle('何時ですか')">🔊</button></td>
      <td colspan="2">
        <div class="buttonGroup" data-question="q0_1_6">
          <button type="button" class="answerBtn" data-value="5">言える</button>
          <button type="button" class="answerBtn" data-value="0">言えない</button>
        </div>
        <input type="hidden" name="q0_1_6" value="" required />
      </td>
    </tr>

    <tr>
      <td>7. これは何ですか？ <a href="img/pencil.png">絵を表示</a> <button type="button" onclick="speakWithGoogle('これは何ですか')">🔊</button></td>
      <td colspan="2">
        <div class="buttonGroup" data-question="q0_1_7">
          <button type="button" class="answerBtn" data-value="5">言える</button>
          <button type="button" class="answerBtn" data-value="0">言えない</button>
        </div>
        <input type="hidden" name="q0_1_7" value="" required />
      </td>
    </tr>
  </tbody>
</table>



        </fieldset>
<br>
        <button type="submit">採点する</button>
      </form>
    </main>

    <footer>@nihongo-note all right reserved.</footer>

    <script src="js/index.js"></script>
  </body>
</html>
