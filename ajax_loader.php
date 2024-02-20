<?php
require "php_header.php";

log_writer("\$_FILES",$_FILES);

$i=0;
while($i < count($_FILES)){
    $tempfile = $_FILES['user_file_name_'.$i]['tmp_name'];
    $filename = 'upload/' . $_FILES['user_file_name_'.$i]['name'];
    $stats = "false";
    
    if (is_uploaded_file($tempfile)) {
        if ( move_uploaded_file($tempfile , $filename )) {
            $msg = $filename . "をアップロードしました。";
            $filelist[] = $filename;
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