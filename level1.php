<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once('./funcs.php');

$student_id = $_GET['student'] ?? ($_POST['student_id'] ?? '');
$student_data = [];

// 生徒データを取得
if ($student_id !== '') {
    $pdo = db_conn();
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :student_id");
    $stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($student) {
        $student_data = $student;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTデータ取得（生徒情報）
    $student_id = $_POST['student_id'] ?? '';
    $school = $_POST['school'] ?? '';
    $year = $_POST['year'] ?? '';
    $class = $_POST['class'] ?? '';
    $name = $_POST['name'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $date = $_POST['date'] ?? '';
    $language_code = $_POST['language_code'] ?? '';

    $teacher_id = $_SESSION['teacher_id'] ?? null;
    if (!$teacher_id) {
        exit('ログインしていません。');
    }

    // 小問とスコア計算
    $sections = [
        'q1_1' => ['q1_1_1', 'q1_1_2', 'q1_1_3', 'q1_1_4', 'q1_1_5', 'q1_1_6'],
        'q1_2' => ['q1_2_1', 'q1_2_2', 'q1_2_3', 'q1_2_4', 'q1_2_5_1', 'q1_2_5_2', 'q1_2_5_3', 'q1_2_5_4', 'q1_2_5_5'],
        'q1_3' => ['q1_3_1', 'q1_3_2', 'q1_3_3', 'q1_3_4', 'q1_3_5'],
    ];

    $scores = [];
    foreach ($sections as $section => $questions) {
        $sectionTotal = 0;
        foreach ($questions as $q) {
            $value = isset($_POST[$q]) ? (int)$_POST[$q] : 0;
            $scores[$q] = $value;
            $sectionTotal += $value;
        }
        $scores[$section . '_score'] = $sectionTotal;
    }

    $scores['q1_3_ondoku'] = isset($_POST['q1_3_ondoku']) ? (int)$_POST['q1_3_ondoku'] : 0;
    $scores['q1_3_score'] += $scores['q1_3_ondoku'];
    $scores['q1_total_score'] = $scores['q1_1_score'] + $scores['q1_2_score'] + $scores['q1_3_score'];

    $pdo = db_conn();

    // 既存チェック
    $check_stmt = $pdo->prepare("SELECT id FROM leveltest_1 WHERE student_id = :student_id AND teacher_id = :teacher_id");
    $check_stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
    $check_stmt->bindValue(':teacher_id', $teacher_id, PDO::PARAM_STR);
    $check_stmt->execute();
    $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // UPDATE
        $sql = "UPDATE leveltest_1 SET
            date = :date, school = :school, year = :year, class = :class,
            name = :name, gender = :gender, language_code = :language_code, teacher_id = :teacher_id,
            q1_1_1 = :q1_1_1, q1_1_2 = :q1_1_2, q1_1_3 = :q1_1_3, q1_1_4 = :q1_1_4, q1_1_5 = :q1_1_5, q1_1_6 = :q1_1_6,
            q1_1_score = :q1_1_score,
            q1_2_1 = :q1_2_1, q1_2_2 = :q1_2_2, q1_2_3 = :q1_2_3, q1_2_4 = :q1_2_4,
            q1_2_5_1 = :q1_2_5_1, q1_2_5_2 = :q1_2_5_2, q1_2_5_3 = :q1_2_5_3, q1_2_5_4 = :q1_2_5_4, q1_2_5_5 = :q1_2_5_5,
            q1_2_score = :q1_2_score,
            q1_3_ondoku = :q1_3_ondoku, q1_3_1 = :q1_3_1, q1_3_2 = :q1_3_2, q1_3_3 = :q1_3_3, q1_3_4 = :q1_3_4, q1_3_5 = :q1_3_5,
            q1_3_score = :q1_3_score,
            q1_total_score = :q1_total_score
            WHERE student_id = :student_id AND teacher_id = :teacher_id";
    } else {
        // INSERT
        $sql = "INSERT INTO leveltest_1 (
            student_id, teacher_id, date, school, year, class, name, gender, language_code,
            q1_1_1, q1_1_2, q1_1_3, q1_1_4, q1_1_5, q1_1_6, q1_1_score,
            q1_2_1, q1_2_2, q1_2_3, q1_2_4, q1_2_5_1, q1_2_5_2, q1_2_5_3, q1_2_5_4, q1_2_5_5, q1_2_score,
            q1_3_ondoku, q1_3_1, q1_3_2, q1_3_3, q1_3_4, q1_3_5, q1_3_score,
            q1_total_score
        ) VALUES (
            :student_id, :teacher_id, :date, :school, :year, :class, :name, :gender, :language_code,
            :q1_1_1, :q1_1_2, :q1_1_3, :q1_1_4, :q1_1_5, :q1_1_6, :q1_1_score,
            :q1_2_1, :q1_2_2, :q1_2_3, :q1_2_4, :q1_2_5_1, :q1_2_5_2, :q1_2_5_3, :q1_2_5_4, :q1_2_5_5, :q1_2_score,
            :q1_3_ondoku, :q1_3_1, :q1_3_2, :q1_3_3, :q1_3_4, :q1_3_5, :q1_3_score,
            :q1_total_score
        )";
    }

    $stmt = $pdo->prepare($sql);

    // 基本項目
    $stmt->bindValue(':date', $date, PDO::PARAM_STR);
    $stmt->bindValue(':school', $school, PDO::PARAM_STR);
    $stmt->bindValue(':year', $year, PDO::PARAM_STR);
    $stmt->bindValue(':class', $class, PDO::PARAM_STR);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':gender', $gender, PDO::PARAM_STR);
    $stmt->bindValue(':language_code', $language_code, PDO::PARAM_STR);
    $stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
    $stmt->bindValue(':teacher_id', $teacher_id, PDO::PARAM_STR);

    // スコア類
    foreach ($scores as $key => $val) {
        $stmt->bindValue(":$key", $val, PDO::PARAM_INT);
    }

    $status = $stmt->execute();

    if ($status === false) {
        sql_error($stmt);
    } else {
        header("Location: thanks.php?name=" . urlencode($name));
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>にほんごノート（レベルチェックーレベル1）</title>
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
        <a href="teacher.php">結果一覧</a>
        <a href="curriculum.php">カリキュラム一覧</a>
        <a href="score.php">管理者用</a>
    </nav>
    <main>

        <h2>レベルチェックーレベル1</h2>

        <form action="level1.php" method="GET" onsubmit="return false;">
            <fieldset>
                <legend>実施児童・生徒番号を入力してください</legend>
                <label for="student">児童・生徒番号（例：STU001）：</label>
                <input type="text" name="student" id="student" required>
                <button type="submit" onclick="location.href='level1.php?student=' + document.getElementById('student').value;">検索</button>
                <input type="hidden" name="student_id" value="<?= h($student_id) ?>">
            </fieldset>
        </form>

        <form action="level1.php" method="POST" onsubmit="return handleSubmit()">
            <input type="hidden" name="student_id" value="<?= h($student_id) ?>">
            <fieldset>
                <legend>基本情報</legend>

                <div class="inline-field">
                    <input type="text" name="school" id="school" class="long"
                        value="<?= h($student_data['school'] ?? '') ?>">
                    <input type="hidden" name="school" value="<?= h($student_data['school'] ?? '') ?>">

                    <input type="text" name="year" id="year" class="short"
                        value="<?= h($student_data['year'] ?? '') ?>">
                    <input type="hidden" name="year" value="<?= h($student_data['year'] ?? '') ?>">

                    <input type="text" name="class" id="class" class="short"
                        value="<?= h($student_data['class'] ?? '') ?>">
                    <input type="hidden" name="class" value="<?= h($student_data['class'] ?? '') ?>">

                    <input type="text" name="name" id="name" class="long"
                        value="<?= h($student_data['name'] ?? '') ?>">
                    <input type="hidden" name="name" value="<?= h($student_data['name'] ?? '') ?>">

                    <input type="text" name="gender" id="gender" class="short"
                        value="<?= h($student_data['gender'] ?? '') ?>">
                    <input type="hidden" name="gender" value="<?= h($student_data['gender'] ?? '') ?>">

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label for="date">実施日：</label>
                        <input type="date" id="date" name="date" class="medium" style="padding: 6px; font-size: 16px;" required>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend id=q1_1>1. 聞いて答える問題</legend>

                <label for="languageSelect">母語を選んでください（
                    select your language）：</label>
                <select id="languageSelect" name="language_code">
                    <option value="ja">日本語</option>
                    <option value="en">English</option>
                    <option value="zh">中文 (Chinese)</option>
                    <option value="tl">Filipino (Tagalog)</option>
                </select>

                <p class="translated-instruction">
                    これから日本語で質問をします。聞いて答えてください。
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
                            <td>あなたの たんじょうびは いつですか？ <button type="button" onclick="speakWithGoogle('あなたの誕生日はいつですか？')">🔊</button></td>
                            <td colspan="2">
                                <div class="buttonGroup" data-question="q1_1_1">
                                    <button type="button" class="answerBtn" data-value="3">言える</button>
                                    <button type="button" class="answerBtn" data-value="0">言えない</button>
                                </div>
                                <input type="hidden" name="q1_1_1" id="q1_1_1" value="" required />
                            </td>
                        </tr>
                        <tr>
                            <td>なんねんせいですか？ <button type="button" onclick="speakWithGoogle('なんねんせいですか？')">🔊</button></td>
                            <td colspan="2">
                                <div class="buttonGroup" data-question="q1_1_2">
                                    <button type="button" class="answerBtn" data-value="3">言える</button>
                                    <button type="button" class="answerBtn" data-value="0">言えない</button>
                                </div>
                                <input type="hidden" name="q1_1_2" id="q1_1_2" value="" required />
                            </td>
                        </tr>
                        <tr>
                            <td>がっこうの なまえは なんですか？ <button type="button" onclick="speakWithGoogle('がっこうの なまえは なんですか？')">🔊</button></td>
                            <td colspan="2">
                                <div class="buttonGroup" data-question="q1_1_3">
                                    <button type="button" class="answerBtn" data-value="3">言える</button>
                                    <button type="button" class="answerBtn" data-value="0">言えない</button>
                                </div>
                                <input type="hidden" name="q1_1_3" id="q1_1_3" value="" required />
                            </td>
                        </tr>
                        <tr>
                            <td>すきな いろは なんですか？ <button type="button" onclick="speakWithGoogle('すきな いろは なんですか？')">🔊</button></td>
                            <td colspan="2">
                                <div class="buttonGroup" data-question="q1_1_4">
                                    <button type="button" class="answerBtn" data-value="3">言える</button>
                                    <button type="button" class="answerBtn" data-value="0">言えない</button>
                                </div>
                                <input type="hidden" name="q1_1_4" id="q1_1_4" value="" required />
                            </td>
                        </tr>
                        <tr>
                            <td>すきな たべものは なんですか？ <button type="button" onclick="speakWithGoogle('すきな たべものは なんですか？')">🔊</button></td>
                            <td colspan="2">
                                <div class="buttonGroup" data-question="q1_1_5">
                                    <button type="button" class="answerBtn" data-value="3">言える</button>
                                    <button type="button" class="answerBtn" data-value="0">言えない</button>
                                </div>
                                <input type="hidden" name="q1_1_5" id="q1_1_5" value="" required />
                            </td>
                        </tr>
                        <tr>
                            <td>（えんぴつを 見せて）これは だれの えんぴつですか？ <button type="button" onclick="speakWithGoogle('これは だれの えんぴつですか？')">🔊</button></td>
                            <td colspan="2">
                                <div class="buttonGroup" data-question="q1_1_6">
                                    <button type="button" class="answerBtn" data-value="3">言える</button>
                                    <button type="button" class="answerBtn" data-value="0">言えない</button>
                                </div>
                                <input type="hidden" name="q1_1_6" id="q1_1_6" value="" required />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- スコア計算 -->
                <p>Q1-1スコア: <span id="q1_1_score_display">0</span>点</p>
                <input type="hidden" id="q1_1_score" name="q1_1_score">

            </fieldset>

            <fieldset>
                <legend id=q1_2>2. 書く問題</legend>

                <p class="translated-instruction">
                    えをみて、こたえをノートにかいてください。
                </p>

                <table border="1">
                    <thead>
                        <tr>
                            <th>書く問題</th>
                            <th colspan="2">回答</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td>
                                <p> <img src="img/q1_2_1.png" alt="wallclock" width="80">
                                    1. なんじですか。
                                </p>
                            </td>
                            <td colspan="2">
                                <div class="buttonGroup" data-question="q1_2_1">
                                    <button type="button" class="answerBtn" data-value="5">書ける</button>
                                    <button type="button" class="answerBtn" data-value="0">書けない</button>
                                </div>
                                <input type="hidden" name="q1_2_1" id="q1_2_1" value="" required />
                            </td>
                        </tr>
                        <tr>
                        <tr>
                            <td>
                                <p> <img src="img/q1_2_2.png" alt="elaser" width="80">
                                    2. けしごむは　いくらですか。
                                </p>
                            </td>
                            <td colspan="2">
                                <div class="buttonGroup" data-question="q1_2_2">
                                    <button type="button" class="answerBtn" data-value="5">書ける</button>
                                    <button type="button" class="answerBtn" data-value="0">書けない</button>
                                </div>
                                <input type="hidden" name="q1_2_2" id="q1_2_2" value="" required />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><img src="img/q1_2_3.png" alt="school" width="80">
                                    3. ひろこさんは　どこで　べんきょうしますか。
                                </p>
                            </td>
                            <td colspan="2">
                                <div class="buttonGroup" data-question="q1_2_3">
                                    <button type="button" class="answerBtn" data-value="5">書ける</button>
                                    <button type="button" class="answerBtn" data-value="0">書けない</button>
                                </div>
                                <input type="hidden" name="q1_2_3" id="q1_2_3" value="" required />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>4. たべものの　なまえを　たくさん　かいてください。</p>
                                <p>（３つ以上）</p>
                            </td>
                            <td colspan="2">
                                <div class="buttonGroup" data-question="q1_2_4">
                                    <button type="button" class="answerBtn" data-value="5">書ける</button>
                                    <button type="button" class="answerBtn" data-value="0">書けない</button>
                                </div>
                                <input type="hidden" name="q1_2_4" id="q1_2_4" value="" required />
                            </td>
                        </tr>

                        <tr>
                            <td colspan="3">
                                <p>5、したの　えは　なんですか。</p>
                                <div style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
                                    <!-- 1 -->
                                    <div class="itemBlock" style="text-align: center;">
                                        <img src="img/q1_2_5_1.png" alt="りす" width="60" height="57"><br>
                                        <div class="buttonGroup" data-question="q1_2_5_1">
                                            <button type="button" class="circleBtn answerBtn" data-value="3">○</button>
                                            <button type="button" class="crossBtn answerBtn" data-value="0">×</button>
                                        </div>
                                        <input type="hidden" name="q1_2_5_1" id="q1_2_5_1" value="" required />
                                    </div>
                                    <div style="font-size: 20px;">→</div>
                                    <!-- 2 -->
                                    <div class="itemBlock" style="text-align: center;">
                                        <img src="img/q1_2_5_2.png" alt="すいか" width="60" height="57"><br>
                                        <div class="buttonGroup" data-question="q1_2_5_2">
                                            <button type="button" class="circleBtn answerBtn" data-value="3">○</button>
                                            <button type="button" class="crossBtn answerBtn" data-value="0">×</button>
                                        </div>
                                        <input type="hidden" name="q1_2_5_2" id="q1_2_5_2" value="" required />
                                    </div>
                                    <div style="font-size: 20px;">→</div>
                                    <!-- 3 -->
                                    <div class="itemBlock" style="text-align: center;">
                                        <img src="img/q1_2_5_3.png" alt="かめ" width="60" height="57"><br>
                                        <div class="buttonGroup" data-question="q1_2_5_3">
                                            <button type="button" class="circleBtn answerBtn" data-value="3">○</button>
                                            <button type="button" class="crossBtn answerBtn" data-value="0">×</button>
                                        </div>
                                        <input type="hidden" name="q1_2_5_3" id="q1_2_5_3" value="" required />
                                    </div>
                                    <div style="font-size: 20px;">→</div>
                                    <!-- 4 -->
                                    <div class="itemBlock" style="text-align: center;">
                                        <img src="img/q1_2_5_4.png" alt="めがね" width="60" height="57"><br>
                                        <div class="buttonGroup" data-question="q1_2_5_4">
                                            <button type="button" class="circleBtn answerBtn" data-value="3">○</button>
                                            <button type="button" class="crossBtn answerBtn" data-value="0">×</button>
                                        </div>
                                        <input type="hidden" name="q1_2_5_4" id="q1_2_5_4" value="" required />
                                    </div>
                                    <div style="font-size: 20px; font-weight: bold;">→</div>
                                    <!-- 5 -->
                                    <div class="itemBlock" style="text-align: center;">
                                        <img src="img/q1_2_5_5.png" alt="ねこ" width="60" height="57"><br>
                                        <div class="buttonGroup" data-question="q1_2_5_5">
                                            <button type="button" class="circleBtn answerBtn" data-value="3">○</button>
                                            <button type="button" class="crossBtn answerBtn" data-value="0">×</button>
                                        </div>
                                        <input type="hidden" name="q1_2_5_5" id="q1_2_5_5" value="" required />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- スコア計算 -->
                <p>Q1-2スコア: <span id="q1_2_score_display">0</span>点</p>
                <input type="hidden" id="q1_2_score" name="q1_2_score">

            </fieldset>

            <fieldset>
                <legend id="q1_3">3. 読んで答える問題</legend>

                <p class="translated-instruction">
                    したの ぶんを こえに だして　よんでください。
                </p>

                <div class="readingBox" style="border: 1px solid #aaa; padding: 10px; margin: 10px 0;">
                    <p>
                        わたしの　なまえは　えみこです。　６さいです。<br>
                        さくらやま　しょうがっこうに　いっています。　しょうがく　いちねんせいです。<br>
                        やすみじかんに　ともだちと　おにごっこを　してあそびます。<br>
                        さんすうが　すきです。　なわとびが　とくいです。<br>
                        かぞくは　おとうさんと　おかあさんと　おとうとが　います。<br>
                        しろい　ねこと　くろい　いぬも　います。
                    </p>
                </div>

                <!-- 音読評価 -->
                <div class="section">
                    <p>＊音読評価：</p>
                    <div class="buttonGroup" data-question="q1_3_ondoku">
                        <label><input type="radio" name="q1_3_ondoku" value="17"> 正しく音読することができる（17点）</label><br>
                        <label><input type="radio" name="q1_3_ondoku" value="13"> 流暢ではないが読めている（13点）</label><br>
                        <label><input type="radio" name="q1_3_ondoku" value="11"> 少し読める（11点）</label><br>
                        <label><input type="radio" name="q1_3_ondoku" value="0"> 読めない（0点）</label>
                    </div>
                    <input type="hidden" name="q1_3_ondoku" id="q1_3_ondoku" value="">
                </div>

                <!-- 選択問題 -->
                <div class="section">
                    <p class="translated-instruction">
                        うえの　ぶんを　よんで　ただしい　こたえに　まる（○）を　つけましょう。</p>

                    <div class="questionBlock">
                        <p>もんだい1、えみこさんは　なんさい　ですか。</p>
                        <label><input type="radio" name="q1_3_1" value="0"> ① ５さい</label><br>
                        <label><input type="radio" name="q1_3_1" value="6"> ② ６さい</label><br>
                        <label><input type="radio" name="q1_3_1" value="0"> ③ ７さい</label>
                        <input type="hidden" name="q1_3_1" id="q1_3_1" value="">
                    </div>



                    <div class="questionBlock">
                        <p>もんだい2、えみこさんは　やすみじかんに　なにを　して　あそびますか。</p>
                        <label><input type="radio" name="q1_3_2" value="0"> ① かくれんぼ</label><br>
                        <label><input type="radio" name="q1_3_2" value="0"> ② なわとび</label><br>
                        <label><input type="radio" name="q1_3_2" value="6"> ③ おにごっこ</label>
                        <input type="hidden" name="q1_3_2" id="q1_3_2" value="">
                    </div>


                    <div class="questionBlock">
                        <p>もんだい3、えみこさんは　なにが　とくい　ですか。</p>
                        <label><input type="radio" name="q1_3_3" value="6"> ① なわとび</label><br>
                        <label><input type="radio" name="q1_3_3" value="0"> ② さんすう</label><br>
                        <label><input type="radio" name="q1_3_3" value="0"> ③ おにごっこ</label>
                        <input type="hidden" name="q1_3_3" id="q1_3_3" value="">
                    </div>


                    <div class="questionBlock">
                        <p>もんだい4、えみこさんの　いえの　いぬは　なにいろ　ですか。</p>
                        <label><input type="radio" name="q1_3_4" value="0"> ① しろ</label><br>
                        <label><input type="radio" name="q1_3_4" value="6"> ② くろ</label><br>
                        <label><input type="radio" name="q1_3_4" value="0"> ③ ちゃいろ</label>
                        <input type="hidden" name="q1_3_4" id="q1_3_4" value="">
                    </div>


                    <div class="questionBlock">
                        <p>もんだい5、えみこさんの　かぞくは　なんにん　かぞく　ですか。</p>
                        <label><input type="radio" name="q1_3_5" value="6"> ① ４にん</label><br>
                        <label><input type="radio" name="q1_3_5" value="0"> ② ５にん</label><br>
                        <label><input type="radio" name="q1_3_5" value="0"> ③ ６にん</label>
                        <input type="hidden" name="q1_3_5" id="q1_3_5" value="">
                    </div>


                </div>

                <!-- スコア -->
                <p>Q1-3スコア: <span id="q1_3_score_display">0</span>点</p>
                <input type="hidden" id="q1_3_score" name="q1_3_score">
            </fieldset>

            <p>総合スコア: <span id="q1_total_score_display">0</span>点</p>
            <input type="hidden" id="q1_total_score" name="q1_total_score">

            <br>
            <button type="submit">採点する</button>
        </form>
    </main>

    <footer>@nihongo-note all right reserved.</footer>


</body>
<!-- <script src="js/index.js"></script> -->
<script src="js/level1.js"></script>

</html>