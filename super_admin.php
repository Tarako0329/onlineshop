<?php
require "php_header.php";

$new_user_cd = 1234;

echo rot13encrypt2($new_user_cd)."<br><br>";


$sql = "select * from Users_online";
$stmt = $pdo_h->prepare($sql);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($data as $row){
  echo $row["yagou"]."　　　<a href=admin_menu.php?key=".rot13encrypt2($row["uid"]).">管理サイトへ</a><br>";
}

//新規出店者登録SQL
$sql="INSERT INTO Users_online(uid,logo) values('1234','upload/logo_sample.png') ";
$sql="INSERT INTO Users(uid,mail,password,question,answer) values('1234','-','-','-','-') ";
?>
