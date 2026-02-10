<?php
//shouhinMS_online_picの全件を取得する
require_once('../php_header_admin.php.php');

try {
    $sql = "SELECT * FROM shouhinMS_online_pic";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pictures = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// ../upload/　の中にあるファイル と テーブルshouhinMS_online_picのpicを比較し、一致しないものをupload配下のremovedフォルダに移動する
//picの中には　upload/ から始まるファイルパスが格納されている
$uploadDir = '../upload/';
$removedDir = '../upload/removed/';

// Create removed directory if it doesn't exist
if (!is_dir($removedDir)) {
    mkdir($removedDir, 0777, true);
}

// Get all filenames from the database and normalize them
$dbFiles = array_column($pictures, 'pic');

// Get all files currently in the upload directory
$localFiles = glob($uploadDir . '*');

foreach ($localFiles as $file) {
    // Skip if it's a directory
    if (is_dir($file)) {
        continue;
    }

    // Check if the local file path exists in the database array
    // We use ltrim or similar if the DB stores paths starting with 'upload/' vs '../upload/'
    // Based on the comment, we check against the exact string stored
    if (!in_array($file, $dbFiles)) {
        $filename = basename($file);
        $destination = $removedDir . $filename;
        
        if (rename($file, $destination)) {
            echo "Moved: " . $filename . " to removed folder.<br>";
        } else {
            echo "Failed to move: " . $filename . "<br>";
        }
    }
}

?>