<?php
  require "php_header.php";
  $sql = "select * from shouhinMS_online online where status<>'stop'" ;
  $stmt = $pdo_h->prepare($sql);
  $stmt->execute();
  $dataset = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $mypath = dirname(__FILE__);
  $fp = fopen("$mypath/sitemap.xml", "w");

  fwrite($fp, "<?xml version='1.0' encoding='UTF-8'?>\r\n");
  fwrite($fp, "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\r\n");
  fwrite($fp, "<url>\r\n");
  fwrite($fp, "\t<loc>https://cafe-present.greeen-sys.com/</loc>\r\n");
  fwrite($fp, "\t<lastmod>".date("Y-m-d")."</lastmod>\r\n");
  fwrite($fp, "</url>\r\n");
  foreach($dataset as $row){
    fwrite($fp, "<url>\r\n");
    fwrite($fp, "\t<loc>https://cafe-present.greeen-sys.com/product.php?id=".$row["uid"].$row["shouhinCD"]."</loc>\r\n");
    //fwrite($fp, "\t<loc>https://cafe-present.greeen-sys.com/product.php?shop_id=".$row["uid"]."</loc>\r\n");
    fwrite($fp, "\t<lastmod>".date("Y-m-d")."</lastmod>\r\n");
    fwrite($fp, "</url>\r\n");

  }
  fwrite($fp, "</urlset>\r\n");
  fclose($fp);
  exit()
?>