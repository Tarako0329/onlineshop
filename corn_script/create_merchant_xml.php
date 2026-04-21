<?php
  date_default_timezone_set('Asia/Tokyo'); 
  $mypath = dirname(__FILE__);
  $mypath = dirname(__DIR__);
  chdir($mypath);
  require "php_header_admin.php";

 $sql = "SELECT 
  online.*
  ,pic.pic as filename
  ,ums_inline.yagou
  from shouhinMS_online online 
  left join shouhinMS_online_pic pic 
  on online.uid = pic.uid 
  and online.shouhinCD = pic.shouhinCD 
  inner join Users_online ums_inline
  on online.uid = ums_inline.uid
  where status<>'stop'
  order by online.uid,online.shouhinCD,pic.sort" ;

  $dataset = $db->SELECT($sql,[]);

  $fp = fopen("$mypath/product_list.xml", "w");

  fwrite($fp, "<?xml version='1.0' encoding='UTF-8'?>\r\n");
  fwrite($fp, "<rss xmlns:g='http://base.google.com/ns/1.0' version='2.0'>\r\n");
  fwrite($fp, "<channel>\r\n");
  fwrite($fp, "<title>".APP_NAME."</title>\r\n");
  fwrite($fp, "<link>https://cafe-present.greeen-sys.com</link>\r\n");
  fwrite($fp, "<description>アレルギーっ子にもおいしいお菓子をお届けしたい。小麦・卵・乳・白砂糖を使わない食べ物を販売します。グルテンフリー,アレルギー対応（小麦・卵・乳 不使用）</description>\r\n");
  
  $shori_id = "";//xml作成中の商品ID
  foreach($dataset as $key => $row){
    if($shori_id<>$row["uid"]."-".$row["shouhinCD"]){//前レコードと処理IDが異なる場合、基本情報を書き込む
      $shori_id=$row["uid"]."-".$row["shouhinCD"];
      fwrite($fp, "<item>\r\n");
      fwrite($fp, "\t<g:id>presentJP".$row["uid"]."-".$row["shouhinCD"]."</g:id>\r\n");
      fwrite($fp, "\t<g:title>".str_replace("&","＆",$row["shouhinNM"])."</g:title>\r\n");
      fwrite($fp, "\t<g:description>".str_replace("&","＆",$row["short_info"])."\r\n\r\n".str_replace("&","＆",$row["infomation"])."</g:description>\r\n");
      fwrite($fp, "\t<g:link>".ROOT_URL."product.php?id=".$row["uid"]."-".$row["shouhinCD"]."</g:link>\r\n");
      fwrite($fp, "\t<g:image_link>".ROOT_URL."".$row["filename"]."</g:image_link> \r\n");//メイン画像
      fwrite($fp, "\t<g:condition>new</g:condition> \r\n");//新品or中古
      if($row["status"]==="show"){
        fwrite($fp, "\t<g:availability>in stock</g:availability> \r\n");//在庫状態あり
      }else{
        fwrite($fp, "\t<g:availability>out of stock</g:availability> \r\n");//在庫状態なし
      }
      fwrite($fp, "\t<g:price>".$row["tanka"]." JPY</g:price> \r\n");
      fwrite($fp, "\t<g:shipping> \r\n");//送料
      fwrite($fp, "\t\t<g:country>JP</g:country> \r\n");
      fwrite($fp, "\t\t<g:service>標準</g:service> \r\n");
      fwrite($fp, "\t\t<g:price>0 JPY</g:price> \r\n");
      fwrite($fp, "\t</g:shipping> \r\n");//送料
      fwrite($fp, "\t<g:shipping_label>注文内容確定後、販売元より連絡</g:shipping_label> \r\n");//送料
      fwrite($fp, "\t<g:gtin></g:gtin> \r\n");//JANコード事業者登録が必要。あると有利
      fwrite($fp, "\t<g:mpn>presentJP".$row["uid"]."-".$row["shouhinCD"]."</g:mpn> \r\n");//商品アイテムの製造者が定める製品番号
      fwrite($fp, "\t<g:brand>".str_replace("&","＆",$row["yagou"])."</g:brand> \r\n");
    }else{
      fwrite($fp, "\t<g:additional_image_link>".ROOT_URL."".$row["filename"]."</g:additional_image_link> \r\n");//追加画像
    }

    // $dataset[$key+1] が存在するかまず確認し、存在すれば結合、なければ空文字を代入
    $next_item = $dataset[$key + 1] ?? null;
    $next_shori_id = $next_item 
      ? ($next_item["uid"] . "-" . $next_item["shouhinCD"]) 
      : "";
    //if($shori_id<>$dataset[$key+1]["uid"]."-".$dataset[$key+1]["shouhinCD"]){//次の処理IDが異なる場合、itemタグを閉じる
    if($shori_id !== $next_shori_id){//次の処理IDが異なる場合、itemタグを閉じる
      fwrite($fp, "</item>\r\n");
    }else{
    }
    //fwrite($fp, "\t \r\n");
  }
  fwrite($fp, "</channel>\r\n");
  fwrite($fp, "</rss>\r\n");
  fclose($fp);
  exit();

?>