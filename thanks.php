<?php
require_once('funcs.php');

$name = $_GET['name'] ?? '生徒';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>記録完了</title>
  <style>
    body {
      font-family: 'Helvetica Neue', sans-serif;
      text-align: center;
      padding: 100px 20px;
      background-color: #f5f5f5;
    }
    .message-box {
      background-color: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
      display: inline-block;
    }
    .message-box h1 {
      font-size: 24px;
      margin-bottom: 20px;
      color: #333;
    }
    .message-box a {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 6px;
    }
    .message-box a:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <div class="message-box">
    <h1><?= h($name) ?>さんの記録を保存しました。</h1>
    <a href="teacher.php">講師ページへ戻る</a>
  </div>
</body>
</html>
