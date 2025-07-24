<?php
require_once('funcs.php');
session_start();

// ログインしていなければログインページへ
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}

$teacher_id = $_SESSION['teacher_id'] ?? '';
$teacher_name = $_SESSION['name'] ?? '講師';

$pdo = db_conn();

// 担当している生徒を取得
$stmt = $pdo->prepare('SELECT * FROM students WHERE teacher_id = :teacher_id ORDER BY name ASC');
$stmt->bindValue(':teacher_id', $teacher_id, PDO::PARAM_STR);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 生徒のIDを配列で収集
$student_ids = array_column($students, 'student_id');

// テスト結果を student_id ごとにまとめて取得
$results_by_student = [];
if (!empty($student_ids)) {
    // IN句用のプレースホルダ生成
    $placeholders = implode(',', array_fill(0, count($student_ids), '?'));

    $sql = "SELECT * FROM gs_leveltest3_01 WHERE student_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($student_ids);
    $all_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // student_idごとに結果をまとめる
    foreach ($all_results as $row) {
        $sid = $row['student_id'];
        if (!isset($results_by_student[$sid])) {
            $results_by_student[$sid] = [];
        }
        $results_by_student[$sid][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>にほんごノート（講師用ページ）</title>
    <link rel="icon" type="image/png" href="img/favicon2.png">
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/style.css">
    <!-- <link rel="stylesheet" href="css/login.css"> -->
</head>

<body>
    <!-- ヘッダー -->
    <header style="background-color:#2c3e50; padding:20px; text-align:center;">
        <h1 style="color:white; font-size:2rem; margin:0;">にほんごノート　講師用ページ</h1>
    </header>

    <!-- ナビゲーション -->
    <nav class="nav-bar">
        <a href="level0.php">レベル０</a>
        <a href="level1.php">レベル１</a>
        <a href="level2.php">レベル２</a>
        <a href="teacher.php">講師用ページ</a>
        <a href="curriculum.php">カリキュラム一覧</a>
        <a href="plan.php">指導計画書発行</a>
    </nav>
    <main>

        <body>
            <div class="form-container">
                <h2><?= h($teacher_name) ?> 講師の担当児童・生徒</h2>

                <?php if (count($students) === 0): ?>
                    <p>担当している生徒が登録されていません。</p>
                <?php else: ?>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>児童・生徒番号</th>
                        <th>学校</th>
                        <th>年</th>
                        <th>組</th>
                        <th>名前</th>
                        <th>性別</th>
                        <th>言語</th>
                        <th>レベル０</th>
                        <th>レベル１</th>
                        <th>レベル２</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= h($row['date']) ?></td>
                            <td><?= h($student['student_id']) ?></td>
                            <td><?= h($student['school']) ?></td>
                            <td><?= h($student['grade']) ?></td>
                            <td><?= h($student['class']) ?></td>
                            <td><?= h($student['name']) ?></td>
                            <td><?= h($student['gender']) ?></td>
                            <td><?= h($student['language_code']) ?></td>

                            <?php
                            $sid = $student['student_id'];
                            $result = $results_by_student[$sid][0] ?? null; // 最新データ1件だけ表示
                            ?>
                            <td>
                                <?php if ($result): ?>
                                    <a href="detail.php?id=<?= h($result['id']) ?>"><?= h($result['total_score']) ?>点</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($result): ?>
                                    <a href="detail_q1.php?id=<?= h($result['id']) ?>"><?= h($result['q1_total_score']) ?>点</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($result): ?>
                                    <a href="detail_q2.php?id=<?= h($result['id']) ?>"><?= h($result['q2_total_score']) ?>点</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p><a href="logout.php">ログアウト</a></p>
        </div>
        </body>

</html>