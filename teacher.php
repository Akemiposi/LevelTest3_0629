<?php
require_once('funcs.php');
session_start();

// ログインチェック
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}

$teacher_id = $_SESSION['teacher_id'] ?? '';
$teacher_name = $_SESSION['name'] ?? '講師';

$pdo = db_conn();

// 生徒ごとの最新結果を1件ずつ取得
$sql = "
    SELECT 
        t.*, 
        l.q1_total_score AS level1_score
    FROM gs_leveltest3_01 t
    INNER JOIN (
        SELECT student_id, MAX(date) AS max_date
        FROM gs_leveltest3_01
        WHERE teacher_id = ?
        GROUP BY student_id
    ) latest
        ON t.student_id = latest.student_id AND t.date = latest.max_date
    LEFT JOIN leveltest_1 l
        ON t.student_id = l.student_id AND l.teacher_id = ?
    ORDER BY t.name ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$teacher_id, $teacher_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <style>
        main {
            background: none !important;
            box-shadow: none !important;
            padding: 40px 20px !important;
            margin: 0 auto !important;
            width: 100%;
        }

        .form-container {
            background: none !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 auto !important;
            width: 100% !important;
            max-width: none !important;
        }

        h2 {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto !important;
            font-size: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px 12px;
            text-align: center;
            white-space: nowrap;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .logout-container {
            text-align: center;
            margin-top: 20px;
        }

        .logout-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #d32f2f;
        }

        .plan-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4275aaff;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .plan-button:hover {
            background-color: #194a7eff;
        }
    </style>


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
        <a href="teacher.php">結果一覧</a>
        <a href="curriculum.php">カリキュラム一覧</a>
    </nav>

    <!-- メイン -->
    <main>
        <h2><?= h($teacher_name) ?> 講師の担当児童・生徒</h2>

        <?php if (count($results) === 0): ?>
            <p style="text-align: center;">担当している生徒の記録がまだありません。</p>
        <?php else: ?>
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
                        <th>計画書</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?= h($row['date']) ?></td>
                            <td><?= h($row['student_id']) ?></td>
                            <td><?= h($row['school']) ?></td>
                            <td><?= h($row['year']) ?></td>
                            <td><?= h($row['class']) ?></td>
                            <td><?= h($row['name']) ?></td>
                            <td><?= h($row['gender']) ?></td>
                            <td><?= h($row['language_code']) ?></td>

                            <td>
                                <?php if (isset($row['total_score'])): ?>
                                    <a href="detail.php?id=<?= h($row['id']) ?>"><?= h($row['total_score']) ?>点</a>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($row['total_score'])): ?>
                                    <a href="detail1.php?student_id=<?= h($row['student_id']) ?>"><?= h($row['level1_score']) ?>点
                                    </a>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($row['q2_total_score'])): ?>
                                    <a href="detail2.php?id=<?= h($row['id']) ?>"><?= h($row['q2_total_score']) ?>点</a>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td>
                                <span class="logout-container">
                                    <a href="plan.php?student_id=<?= h($row['student_id']) ?>" class="plan-button">発行</a>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p class="logout-container">
            <a href="logout.php" class="logout-button">ログアウト</a>
        </p>
    </main>
</body>

</html>