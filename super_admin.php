<?php
require "php_header.php";

$new_user_cd = 3;

echo rot13encrypt2($new_user_cd)."<br><br>";


$sql = "select * from Users_online";
$stmt = $pdo_h->prepare($sql);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($data as $row){
  echo $row["site_name"]."<a href=admin_menu.php?key=".rot13encrypt2($row["uid"]).">管理サイトへ</a><br>";
}
?>