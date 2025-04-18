<?php
  require "php_header.php";
  register_shutdown_function('shutdown_ajax',basename(__FILE__));

  $alert_status = "alert_success";
  $rtn = csrf_checker(["review_management.php","xxx.php"],["P","S"]);
  if($rtn !== true){
    $msg=$rtn;
    $alert_status = "alert-warning";
    $reseve_status = true;
  }else{
		//reviewを取得
		$sql="SELECT 
	    u.yagou
	    ,u.logo
	    ,m.shouhinNM
	    ,r.*
	    ,p.pic 
			,IF(r.reply IS NULL,'返事する','修正する') as btn_name
	  FROM shouhinMS_online m
	    left join review_online r
	    on r.shop_id = m.uid
	    and r.shouhinCD = m.shouhinCD
	    inner join Users_online u
	    on m.uid = u.uid
	    inner join shouhinMS_online_pic p
	    on m.shouhinCD = p.shouhinCD
	    and m.uid = p.uid
	    and p.sort=1
	    where m.uid = :uid
	  order by r.insdatetime desc";

		$stmt = $pdo_h->prepare($sql);
	  $stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
	  $stmt->execute();
	  $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
		  //log_writer('\$talk',$talk);
	}

	$token = csrf_create();
	$return_sts = array(
    "MSG" => $msg
    ,"status" => $alert_status
    ,"csrf_create" => $token
    ,"reviews" => $reviews
  );

  header('Content-type: application/json');  
  echo json_encode($return_sts, JSON_UNESCAPED_UNICODE);
  exit();
?>
