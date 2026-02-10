<?php
require "php_header.php";

log_writer("\$_FILES",$_FILES);
log_writer("\$_POST",$_POST);
$rtn = csrf_checker(["shouhinMS.php"],["P","C","S"]);
if($rtn !== true){
	$msg=$rtn;
	$alert_status = "alert-warning";
	$reseve_status = true;
}else{
    $i=0;
    $sort_i=$_POST["next_i"];
    while($i < count($_FILES)){
        $tempfile = $_FILES['user_file_name_'.$i]['tmp_name'];
        $filename = 'upload/temp/' .$_SESSION["user_id"]."_".$_POST["filesubname"]."_".date('Ymd-His')."_".$_FILES['user_file_name_'.$i]['name'];
        $stats = "false";

        if (is_uploaded_file($tempfile)) {
            if ( move_uploaded_file($tempfile , $filename )) {
                $msg = $filename . "をアップロードしました。";

                $filename = backupAndOptimizeImage($filename);  //画像圧縮(avif以外をwebpに変換)

                if($filename===false){
                    $msg = "アップロードに失敗しました。(ファイルの変換に失敗)";
                    break;
                }
                $filelist[] = array(
                    "sort" => $sort_i + $i
                    ,"filename" => $filename
                    ,"delete_flg" => false
                );
                $stats = "success";
            } else {
                $msg = "ファイルをアップロードできません。";
            }
        } else {
            $msg = "ファイルが選択されていません。";
        }

        $i = $i+1;
    }
}
$token = csrf_create();
$return = array(
    "filename" => $filelist
    ,"msg" => $msg
    ,"status" => $stats
    ,"csrf_create" => $token
);

//jsonとして出力
header('Content-type: application/json');
echo json_encode($return, JSON_UNESCAPED_UNICODE);
?>