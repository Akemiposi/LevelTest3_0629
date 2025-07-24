<?php
session_start();
require_once('funcs.php'); // DB接続・共通関数ファイル

// POSTでログイン情報が送られたとき
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        exit('メールアドレスとパスワードを入力してください');
    }

    // DB接続
    $pdo = db_conn();

    // 該当ユーザーを検索
    $stmt = $pdo->prepare("SELECT * FROM admin_teacher WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // パスワードチェック
    if ($user && password_verify($password, $user['password_hash'])) {
        // セッションにユーザー情報を保存
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['teacher_id'] = $user['teacher_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // ロールによって画面を振り分け
        if ($user['role'] === 'admin') {
            header('Location: score.php');
        } else {
            header('Location: teacher.php');
        }
        exit;
    } else {
        $error = "ログインに失敗しました。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ログイン | nihongonote</title>
    <link rel="icon" type="image/png" href="img/favicon2.png">
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>


    <!-- ロゴ -->
    <div class="logo-container">
        <img src="img/nihongonote_logo.png" alt="nihongonote ロゴ" class="logo">
    </div>

    <h2>nihongonote ログイン</h2>

    <div class="login-container">
        <?php if (!empty($error)): ?>
            <p class="error"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" required>

            <label for="password">パスワード</label>
            <input type="password" name="password" id="password" required>

            <input type="submit" value="ログイン">
        </form>

        <div class="register-link">
            <!-- パスワードをお忘れですか？ <a href="forget_password.php">再設定はこちら</a> -->
            <br><br>
            まだ登録していませんか？ <a href="register.php">新規登録はこちら</a>
        </div>