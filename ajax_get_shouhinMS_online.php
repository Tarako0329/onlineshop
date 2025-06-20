<?php
  require "php_header.php";
	$hinmei = (($_GET["f"])!=="undefined")?$_GET["f"]:"%";
	//log_writer2("",$hinmei,"lv3");
	//$_SESSION["user_id"] = "%";
	$rtn = true;//csrf_checker(["xxx.php","xxx.php"],["P","C","S"]);
	if($rtn !== true){
	  $msg=$rtn;
	  $alert_status = "false";
	  $reseve_status = true;
	}else{
		$sql = "SELECT DISTINCT
				online.uid
				,online.shouhinCD
				,online.shouhinNM
				,online.status
				,online.limited_cd
				,'' as limited_cd_nyuryoku
				,online.short_info
				,online.infomation
				,online.haisou
				,online.customer_bikou
				,online.tanka
				,online.zeikbn
				,online.shouhizei
				,ifnull(online.hash_tag,'') as hash_tag
				,NULL as rezCD
				,online.tanka + online.shouhizei as zeikomikakaku
				,'0' as ordered
				,'0' as goukeikingaku
				,ums_inline.*
				,IFNULL(r.cnt,0) as review_cnt
				,IFNULL(r.score,0) as review_score
			from shouhinMS_online online 
			inner join (select uid,yagou,name,shacho,logo,cancel_rule from Users_online ) ums_inline
			on online.uid = ums_inline.uid
			left join (select shop_id,shouhinCD,count(*) as cnt,avg(score) as score from review_online group by shop_id,shouhinCD) as r
			on online.uid = r.shop_id
			and online.shouhinCD = r.shouhinCD
			where 
				online.uid like :uid 
				and online.shouhinNM like :hinmei 
				and online.status <> 'del' 
			order by 
				online.uid
				,case 
					when online.status = 'soon' then 0
					when online.status = 'show' then 1
					when online.status = 'limited' then 2
					when online.status = 'soldout' then 3
					when online.status = 'stop' then 4
				end
				,online.shouhinCD";

		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_INT);
		$stmt->bindValue("hinmei", $hinmei, PDO::PARAM_STR);
		$stmt->execute();
		$count = $stmt->rowCount();
		$dataset = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$i=0;
		foreach($dataset as $row){
			$dataset[$i]["line_url"] = rawurlencode(ROOT_URL."product.php?id=".$row["uid"]."-".$row["shouhinCD"]."&z=ln");
			$dataset[$i]["key"] = rot13encrypt2($row["shouhinCD"]);
			$dataset[$i]["key2"] = rot13encrypt2($row["uid"]);
			$i++;
		}
		
		
		$sql = "select 
				online.uid
				,online.shouhinCD
				,online.shouhinNM
				,pic.sort
				,pic.pic as filename
			from shouhinMS_online online 
			left join shouhinMS_online_pic pic 
			on online.uid = pic.uid 
			and online.shouhinCD = pic.shouhinCD
			where online.uid like :uid and online.shouhinNM like :hinmei and online.status <> 'del'
			order by online.uid,online.shouhinCD,pic.sort";
		$stmt = $pdo_h->prepare($sql);
		$stmt->bindValue("uid", $_SESSION["user_id"], PDO::PARAM_STR);
		$stmt->bindValue("hinmei", $hinmei, PDO::PARAM_STR);
		$stmt->execute();
		$pic_set = $stmt->fetchAll(PDO::FETCH_ASSOC);


		
		if($count!==0){
			$alert_status = "success";
		}
		

		
		$return = array(
	    "alert" => $alert_status,
	    "dataset" => $dataset,
			"pic_set" => $pic_set
	  );
	}
  header('Content-type: application/json');  
  echo json_encode($return, JSON_UNESCAPED_UNICODE);
  exit();
?>
