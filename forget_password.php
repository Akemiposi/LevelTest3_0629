<?php
require_once('funcs.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. 入力値の取得（空なら空文字）
    $name       = $_POST['name'] ?? '';
    $email      = $_POST['email'] ?? '';
    $password   = $_POST['password'] ?? '';
    $teacher_id = $_POST['teacher_id'] ?? '';
    $role       = $_POST['role'] ?? '';

    // 2. 入力チェック
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error_message = "すべての必須項目を入力してください。";
    } else {
        // 3. パスワードをハッシュ化
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // 4. DB接続
        $pdo = db_conn();

        // 5. SQL実行（プレースホルダ）
        $stmt = $pdo->prepare("INSERT INTO admin_teacher (name, email, password, teacher_id, role)
                               VALUES (:name, :email, :password, :teacher_id, :role)");
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashed, PDO::PARAM_STR);
        $stmt->bindValue(':teacher_id', $teacher_id ?: null, PDO::PARAM_STR);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);

        $status = $stmt->execute();

        if ($status) {
            header('Location: login.php');
            exit;
        } else {
            $error = $stmt->errorInfo();
            $error_message = '登録失敗: ' . htmlspecialchars($error[2]);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規登録 | nihongonote</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="form-container">
        <!-- ロゴ -->
    <div class="logo-container">
        <img src="img/nihongonote_logo.png" alt="nihongonote ロゴ" class="logo">
    </div>

        <h2>新規登録</h2>

        <!-- エラーメッセージ表示 -->
        <?php if (!empty($error_message)): ?>
            <p class="error"><?= $error_message ?></p>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <label>名前</label>
            <input type="text" name="name" required>

            <label>メールアドレス</label>
            <input type="email" name="email" required>

            <label>パスワード</label>
            <input type="password" name="password" required>

            <label>指導員NO（例：TCH001）<span class="small">※管理者は空でもOK</span></label>
            <input type="text" name="teacher_id">

            <label>管理者・講師を選択してください</label>
            <select name="role" required>
                <option value="">選択してください</option>
                <option value="admin">管理者</option>
                <option value="teacher">講師</option>
            </select>

            <button type="submit">登録する</button>
        </form>

        <p class="small">アカウントをお持ちの方は <a href="login.php">ログインはこちら</a></p>
    </div>
</body>
</html>