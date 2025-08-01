<?php
require_once(__DIR__ . '/auth_teacher.php');

// ログインチェック（未ログインなら login.php に飛ばす）
check_login();

// // 管理者以外はアクセス不可
// if ($_SESSION['role'] !== 'admin') {
//   session_destroy();
//   header('Location: login.php');
//   exit;
// }

$pdo = db_conn();

// データ取得（生徒ごとの最新結果を1件ずつ取得）
$sql = "
    SELECT 
        t.*, 
        t.total_score, 
        l.q1_total_score AS level1_score 
    FROM gs_leveltest3_01 t
    INNER JOIN (
        SELECT student_id, MAX(date) AS max_date
        FROM gs_leveltest3_01
        GROUP BY student_id
    ) latest
        ON t.student_id = latest.student_id 
       AND t.date = latest.max_date
    LEFT JOIN leveltest_1 l
        ON t.student_id = l.student_id
    ORDER BY t.name ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>スコア一覧</title>
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
    }

    th,
    td {
      padding: 8px;
      border: 1px solid #ccc;
      text-align: center;
    }

    th {
      background-color: #f2f2f2;
    }
  </style>
  <link rel="icon" type="image/png" href="img/favicon2.png">

  <link rel="stylesheet" href="css/reset.css" />
  <link rel="stylesheet" href="css/style.css" />
</head>

<body>

  <!-- ヘッダー -->
  <header style="background-color:#2c3e50; padding:20px; text-align:center;">
    <h1 style="color:white; font-size:2rem; margin:0;">管理者用結果一覧</h1>
  </header>

  <!-- ナビゲーション -->
  <nav class="nav-bar">
    <?php if ($is_teacher): ?>
      <a href="level0.php?teacher_id=<?= urlencode($_SESSION['teacher_id'] ?? '') ?>">レベル０</a>
      <a href="level1.php?teacher_id=<?= urlencode($_SESSION['teacher_id'] ?? '') ?>">レベル１</a>
      <!-- <a href="level2.php?teacher_id=<?= urlencode($_SESSION['teacher_id'] ?? '') ?>">レベル２</a> -->
      <a href="teacher.php?teacher_id=<?= urlencode($_SESSION['teacher_id'] ?? '') ?>">結果一覧</a>
      <a href="curriculum.php?teacher_id=<?= urlencode($_SESSION['teacher_id'] ?? '') ?>">カリキュラム</a>
    <?php elseif ($is_admin): ?>
      <a href="level0.php?admin_id=<?= urlencode($_SESSION['admin_id'] ?? '') ?>">レベル０</a>
      <a href="level1.php?admin_id=<?= urlencode($_SESSION['admin_id'] ?? '') ?>">レベル１</a>
      <a href="score.php?admin_id=<?= urlencode($_SESSION['admin_id'] ?? '') ?>">管理一覧</a>
      <a href="curriculum.php?teacher_id=<?= urlencode($_SESSION['admin_id'] ?? '') ?>">カリキュラム</a>
    <?php endif; ?>
  </nav>

  <h1>テスト結果一覧</h1>

  <div class="logout">
    <a href="logout.php">ログアウト</a>
  </div>

  <table>

    <tr>
      <th>日付</th>
      <th>番号</th>
      <th>学校</th>
      <th>年</th>
      <th>組</th>
      <th>名前</th>
      <th>性別</th>
      <th>言語</th>
      <th>講師</th>
      <th>L0詳細</th>
      <th>L1詳細</th>
      <th>L2詳細</th>
      <th>計画書</th>
    </tr>

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
        <td><?= h($row['teacher_id']) ?></td>
        <td>
          <?php if (!empty($row['total_score'])): ?>
            <a href="detail.php?id=<?= h($row['id']) ?>"><?= h($row['total_score']) ?>点</a>
          <?php else: ?> - <?php endif; ?>
        </td>
        <td>
          <?php if (!empty($row['level1_score'])): ?>
            <a href="detail1.php?student_id=<?= h($row['student_id']) ?>"><?= h($row['level1_score']) ?>点</a>
          <?php else: ?> - <?php endif; ?>
        </td>
        <td>
          <?php if (!empty($row['q2_total_score'])): ?>
            <a href="detail2.php?id=<?= h($row['id']) ?>"><?= h($row['q2_total_score']) ?>点</a>
          <?php else: ?> - <?php endif; ?>
        </td>
        <td>
          <a href="plan.php?student_id=<?= h($row['student_id']) ?>" class="plan-button">発行</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><a href="level0.php">← 戻る</a></p>

  <footer>@nihongo-note all right reserved.</footer>

  <script src="js/index.js"></script>
</body>

</html>