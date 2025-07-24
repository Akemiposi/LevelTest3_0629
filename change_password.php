<?php
session_start();
require_once('funcs.php');
check_login();

$pdo = db_conn();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $id = $_SESSION['user_id'];

    // ユーザー情報を取得
    $stmt = $pdo->prepare("SELECT * FROM admin_teacher WHERE id = :id");
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current, $user['password_hash'])) {
        $message = '現在のパスワードが正しくありません。';
    } elseif ($new !== $confirm) {
        $message = '新しいパスワードと確認用パスワードが一致しません。';
    } elseif ($current === $new) {
        $message = '新しいパスワードは現在のパスワードと異なる必要があります。';
    } else {
        // 更新処理
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin_teacher SET password_hash = :hash WHERE id = :id");
        $stmt->bindValue(':hash', $new_hash);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $message = 'パスワードを変更しました。';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>パスワード変更 | nihongonote</title>
  <link rel="stylesheet" href="index.css">
</head>
<body>

<h2>パスワード変更</h2>

<div class="login-container">
  <?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <form method="post" action="change_password.php">
    <label for="current_password">現在のパスワード</label>
    <input type="password" name="current_password" id="current_password" required>

    <label for="new_password">新しいパスワード</label>
    <input type="password" name="new_password" id="new_password" required>

    <label for="confirm_password">確認用パスワード</label>
    <input type="password" name="confirm_password" id="confirm_password" required>

    <input type="submit" value="パスワードを変更する">
  </form>

  <div class="register-link">
    <a href="<?= ($_SESSION['role'] === 'admin') ? 'admin.php' : 'teacher_dashboard.php' ?>">ダッシュボードに戻る</a>
  </div>
</div>

</body>
</html>
