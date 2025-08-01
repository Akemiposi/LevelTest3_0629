<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('./funcs.php');

// GETから student_id を取得（優先）
$student_id = $_GET['student_id'] ?? '';

$pdo = db_conn(); // ← 最初に接続しておく（これで常に$pdoが使える）

// もし student_id が空で id パラメータがあるなら旧形式を変換
if ($student_id === '' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT student_id FROM leveltest_1 WHERE id = :id");
    $stmt->bindValue(':id', intval($_GET['id']), PDO::PARAM_INT);
    $stmt->execute();
    $found = $stmt->fetchColumn();
    if ($found) {
        $student_id = $found;
    }
}

if ($student_id === '') {
    die('student_id不正なIDです');
}

if ($student_id === '') {
    die('student_id不正なIDです');
}

$sql = "SELECT * FROM leveltest_1 WHERE student_id = :student_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch();

if (!$row) die('データが見つかりませんでした');

$id = $row['id'];


$edit_mode = isset($_GET['edit']) && $_GET['edit'] === '1';

$q1_1_questions = [
    'あなたのたんじょうびはいつですか？',
    'なんねんせいですか？',
    'がっこうのなまえはなんですか？',
    'すきないろはなんですか？',
    'すきなたべものはなんですか？',
    'これはだれのえんぴつですか？'
];
$q1_2_questions = [
    'なんじですか？',
    'けしごむはいくらですか',
    'ひろこさんはどこでべんきょうしますか',
    'たべもののなまえをかいてください（３つ以上）',
    'しりとり（リス）',
    'しりとり（スイカ）',
    'しりとり（かめ）',
    'しりとり（メガネ）',
    'しりとり（ねこ）'
];
$q1_3_questions = [
    '音読評価',
    '質問１',
    '質問2',
    '質問3',
    '質問4',
    '質問5'
];
$q1_1_answers = array_map(fn($i) => $row["q1_1_$i"] ?? '', range(1, 6));
$q1_2_answers = array_map(fn($i) => $row["q1_2_$i"] ?? '', range(1, 9));
$q1_3_answers = array_map(fn($i) => $row["q1_3_$i"] ?? '', range(1, 6));

function mark($v)
{
    return intval($v) > 0 ? '⚪︎' : '×';
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>詳細結果</title>
    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/style.css" />
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>

    <header style="background-color:#2c3e50; padding:20px; text-align:center;">
        <h1 style="color:white;">レベル1 詳細結果</h1>
    </header>

    <nav class="nav-bar">
        <a href="level0.php">レベル０</a>
        <a href="level1.php">レベル１</a>
        <a href="level2.php">レベル２</a>
        <a href="teacher.php">結果一覧</a>
        <a href="curriculum.php">カリキュラム</a>
    </nav>

    <div class="login" style="text-align: right; margin: 20px, 0;">
        <a href="login.php">管理用ログイン</a>
    </div>

    <h2><?= h($row['name']) ?> さんの記録</h2>
    <p>日付：<?= h($row['date']) ?>　学校：<?= h($row['school']) ?>　学年：<?= h($row['year']) ?>　組：<?= h($row['class']) ?>　性別：<?= h($row['gender']) ?>　言語：<?= h($row['language_code']) ?></p>

    <!-- ＊＊＊＊＊＊＊＊編集モード ここから＊＊＊＊＊＊＊＊ -->
    <?php if ($edit_mode): ?>
        <form method="POST" action="update1.php">
            <input type="hidden" name="student_id" value="<?= h($row['student_id']) ?>">

            <h3>Q1-1 聞いて答える問題</h3>
            <table>
                <tr>
                    <th>質問</th>
                    <th>正誤</th>
                </tr>
                <?php foreach ($q1_1_questions as $i => $q): ?>
                    <tr>
                        <td><?= h($q) ?></td>
                        <td><input type="number" name="q1_1_<?= $i + 1 ?>" value="<?= h($q1_1_answers[$i]) ?>" min="0" max="3"></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <h3>Q1-2 書く問題</h3>
            <table>
                <tr>
                    <th>問題</th>
                    <th>正誤</th>
                </tr>
                <!-- Q1-2（通常4問） -->
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <tr>
                        <td><?= h($q1_2_questions[$i]) ?></td>
                        <td>
                            <input type="number"
                                name="q1_2_<?= $i + 1 ?>"
                                value="<?= h($q1_2_answers[$i] ?? 0) ?>"
                                min="0" max="5">
                        </td>
                    </tr>
                <?php endfor; ?>

                <!-- Q1-2（しりとり5問） -->
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <tr>
                        <td><?= h($q1_2_questions[4 + $i - 1]) ?></td>
                        <td>
                            <input type="number"
                                name="q1_2_5_<?= $i ?>"
                                value="<?= h($row["q1_2_5_$i"] ?? 0) ?>"
                                min="0" max="3">
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>

            <h3>Q1-3 読んで答える問題</h3>
            <table>
                <tr>
                    <th>問題</th>
                    <th>評価</th>
                </tr>
                <tr>
                    <td>音読評価</td>
                    <td>
                        <input type="number" name="q1_3_ondoku"
                            value="<?= h($row['q1_3_ondoku'] ?? 0) ?>" min="0" max="17">
                    </td>
                </tr>

                <!-- 問題1〜5をループで表示 -->
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <tr>
                        <td>問題<?= $i + 1 ?></td>
                        <td>
                            <input type="number" name="q1_3_<?= $i + 1 ?>"
                                value="<?= h($q1_3_answers[$i] ?? '') ?>"
                                min="0" max="6">
                        </td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>

            <!-- Q1-1 -->
            <p>Q1-1スコア: <span id="q1_1_score_display"><?= h($row['q1_1_score'] ?? 0) ?></span>点</p>
            <input type="hidden" id="q1_1_score" name="q1_1_score" value="<?= h($row['q1_1_score'] ?? 0) ?>">

            <!-- Q1-2 -->
            <p>Q1-2スコア: <span id="q1_2_score_display"><?= h($row['q1_2_score'] ?? 0) ?></span>点</p>
            <input type="hidden" id="q1_2_score" name="q1_2_score" value="<?= h($row['q1_2_score'] ?? 0) ?>">

            <!-- Q1-3 -->
            <p>Q1-3スコア: <span id="q1_3_score_display"><?= h($row['q1_3_score'] ?? 0) ?></span>点</p>
            <input type="hidden" id="q1_3_score" name="q1_3_score" value="<?= h($row['q1_3_score'] ?? 0) ?>">

            <!-- 合計 -->
            <p>総合スコア: <span id="q1_total_score_display"><?= h($row['q1_total_score'] ?? 0) ?></span>点</p>
            <input type="hidden" id="q1_total_score" name="q1_total_score" value="<?= h($row['q1_total_score'] ?? 0) ?>">

            <!-- 再計算ボタン -->
            <!-- <p><button type="button" onclick="updateScore()">再計算</button></p> -->

            <button type="submit">保存</button>
            <a href="detail1.php?id=<?= h($row['id']) ?>">キャンセル</a>
        </form>
        <!-- ＊＊＊＊＊＊＊＊通常表示 ここから＊＊＊＊＊＊＊＊ -->
    <?php else: ?>
        <h3>Q1-1 聞いて答える</h3>
        <table>
            <tr>
                <th>設問</th>
                <th>評価</th>
            </tr>
            <?php foreach ($q1_1_questions as $i => $q): ?>
                <tr>
                    <td><?= h($q) ?></td>
                    <td><?= mark($q1_1_answers[$i]) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Q1-2 書く</h3>
        <table>
            <tr>
                <th>ことば</th>
                <th>評価</th>
            </tr>
            <?php foreach ($q1_2_questions as $i => $q): ?>
                <tr>
                    <td><?= h($q) ?></td>
                    <td><?= mark($q1_2_answers[$i]) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Q1-3 読んで答える</h3>
        <table>
            <tr>
                <th>文</th>
                <th>評価</th>
            </tr>
            <?php foreach ($q1_3_questions as $i => $q): ?>
                <tr>
                    <td><?= h($q) ?></td>
                    <td>
                        <?php if ($i === 0): ?>
                            <?= h($q1_3_ondoku ?? '0') ?>点
                        <?php else: ?>
                            <?= mark($q1_3_answers[$i - 1]) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <p>Q1-1：<?= h($row['q1_1_score']) ?>点　Q1-2：<?= h($row['q1_2_score']) ?>点　Q1-3：<?= h($row['q1_3_score']) ?>点　合計：<?= h($row['q1_total_score']) ?>点</p>


        <a href="detail1.php?id=<?= h($row['id']) ?>&edit=1">修正</a>
        <form action="delete.php" method="POST" onsubmit="return confirm('本当に削除しますか？');">
            <input type="hidden" name="id" value="<?= h($row['id']) ?>">
            <button type="submit">削除</button>
        </form>
    <?php endif; ?>

    <a href="score.php">← スコア一覧に戻る</a>
    <script src="js/level1.js"></script>
</body>

</html>