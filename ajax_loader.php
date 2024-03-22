<?php
require "php_header.php";

//log_writer("\$_FILES",$_FILES);
//log_writer("\$_POST",$_POST);

$i=0;
while($i < count($_FILES)){
    $tempfile = $_FILES['user_file_name_'.$i]['tmp_name'];
    $filename = 'upload/temp/' .$_SESSION["user_id"]."_".$_POST["filesubname"]."_".date('Ymd-His')."_".$_FILES['user_file_name_'.$i]['name'];
    $stats = "false";
    
    if (is_uploaded_file($tempfile)) {
        if ( move_uploaded_file($tempfile , $filename )) {
            $msg = $filename . "をアップロードしました。";
            $filelist[] = array("sort" => $i+1,"filename" => $filename);
            $stats = "success";
        } else {
            $msg = "ファイルをアップロードできません。";
        }
    } else {
        $msg = "ファイルが選択されていません。";
    }
    
    $i = $i+1;
}

$return = array(
    "filename" => $filelist
    ,"msg" => $msg
    ,"status" => $stats
);

//jsonとして出力
header('Content-type: application/json');
echo json_encode($return, JSON_UNESCAPED_UNICODE);
?>