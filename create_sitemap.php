<?php
  date_default_timezone_set('Asia/Tokyo'); 
  $mypath = dirname(__FILE__);
  require $mypath."/vendor/autoload.php";

  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
  define("MAIN_DOMAIN",$_ENV["MAIN_DOMAIN"]);
  define("EXEC_MODE",$_ENV["EXEC_MODE"]);

  if(EXEC_MODE<>"Product"){
  }else{
  }
  
  
  // DBとの接続
  define("DNS","mysql:host=".$_ENV["SV"].";dbname=".$_ENV["DBNAME"].";charset=utf8");
  define("USER_NAME", $_ENV["DBUSER"]);
  define("PASSWORD", $_ENV["PASS"]);
  
  $pdo_h = new PDO(DNS, USER_NAME, PASSWORD, get_pdo_options());
  
  $sql = "select * from shouhinMS_online online where status<>'stop'" ;
  $stmt = $pdo_h->prepare($sql);
  $stmt->execute();
  $dataset = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $fp = fopen("$mypath/sitemap.xml", "w");

  fwrite($fp, "<?xml version='1.0' encoding='UTF-8'?>\r\n");
  fwrite($fp, "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\r\n");
  fwrite($fp, "<url>\r\n");
  fwrite($fp, "\t<loc>https://cafe-present.greeen-sys.com/</loc>\r\n");
  fwrite($fp, "\t<lastmod>".date("Y-m-d")."</lastmod>\r\n");
  fwrite($fp, "</url>\r\n");
  foreach($dataset as $row){
    fwrite($fp, "<url>\r\n");
    fwrite($fp, "\t<loc>https://cafe-present.greeen-sys.com/product.php?id=".$row["uid"]."-".$row["shouhinCD"]."</loc>\r\n");
    fwrite($fp, "\t<lastmod>".date("Y-m-d")."</lastmod>\r\n");
    fwrite($fp, "</url>\r\n");

  }
  fwrite($fp, "</urlset>\r\n");
  fclose($fp);
  exit();

  function get_pdo_options() {
    return array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,   //sqlの複文禁止 "select * from hoge;delete from hoge"みたいなの
                 PDO::ATTR_EMULATE_PREPARES => false);        //同上
  }
?>