<?php
//共通に使う関数を記述
//XSS対応（ echoする場所で使用！それ以外はNG ）
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES);
}

//db_conn
function db_conn() {
  //2. DB接続します
  try {
     //ID:'root', Password: xamppは 空白 ''
  // $pdo = new PDO('mysql:dbname=gs_db_leveltest3;charset=utf8;host=localhost','root','');
    $pdo = new PDO(
      'mysql:host=localhost;dbname=glassposi_leveltest3;charset=utf8mb4',
      'glassposi_akemi',
      'Akemi00rei' // パスワード
    );
    return $pdo;
  } catch (PDOException $e) {
    exit('DBConnectError:'.$e->getMessage());
  }
}
