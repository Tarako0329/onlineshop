<?php
	//毎月１日にcornジョブから実行する
	if (php_sapi_name() != 'cli') {
		exit('このスクリプトはCLIからのみ実行可能です。');
	}
	$mypath = dirname(__DIR__);
	chdir($mypath);
	require "php_header_admin.php";
	
	$date = new DateTime(date('Y-m-d'));
	//$date = new DateTime('2024-12-01');
	//echo $date->format('Ym')."<br>";
	$yokugetu=$date->format('Ym');
	
	$date->modify('-10 day');
	$getudo=$date->format('Ym');
	//echo $date->format('Y-m-d');
	//echo $date->format('Ym');
	
	//echo $getudo."<br>".$yokugetu;
	//$getudo="202403";
	//$yokugetu="202403";

	$sql = "SELECT 
		jisseki.uid
		,jisseki.getudo as 月度
		,ifnull(seikyu.zenkuri,0) as 前月繰越
		,jisseki.juchu_jisseki
		,if(ifnull(seikyu.zenkuri,0)=0,`get_seikyuu`(jisseki.juchu_jisseki),0) as seikyu
		,if(ifnull(seikyu.zenkuri,0)<>0 && ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki>=100000,1000,0) as seikyu2
		,if(ifnull(seikyu.zenkuri,0)<>0 && ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki-100000>=100000,`get_seikyuu`(ifnull(seikyu.zenkuri,0)+jisseki.juchu_jisseki-100000),0) as seikyu3
		,case 
			when ifnull(seikyu.zenkuri,0) = 0 and juchu_jisseki >= 100000 then 0
			when ifnull(seikyu.zenkuri,0) + juchu_jisseki < 100000 then ifnull(seikyu.zenkuri,0) + juchu_jisseki
			when ifnull(seikyu.zenkuri,0) + juchu_jisseki >= 100000 and ifnull(seikyu.zenkuri,0) + juchu_jisseki - 100000 < 100000 then ifnull(seikyu.zenkuri,0) + juchu_jisseki - 100000
			else 0
		end as kurikoshi
		from
		(
			SELECT 
				ym.uid,ym.getudo
				,sum(if(DATE_FORMAT(juchuu_date, '%Y%m') = ym.getudo,ifnull(jm.goukeitanka,0),0)) as juchu_jisseki1
				,sum(if(DATE_FORMAT(cancel, '%Y%m') = ym.getudo,(ifnull(jm.goukeitanka,0) * (-1)),0)) as cancel_jisseki 
				,sum(if(DATE_FORMAT(juchuu_date, '%Y%m') = ym.getudo,ifnull(jm.goukeitanka,0),0) + if(DATE_FORMAT(cancel, '%Y%m') = ym.getudo,(ifnull(jm.goukeitanka,0) * (-1)),0)) as juchu_jisseki
			FROM 
			(select *,:getudo as getudo from Users_online) ym
			left join `juchuu_head` jh 
			on ym.uid = jh.uid
			and (
				DATE_FORMAT(juchuu_date, '%Y%m') = ym.getudo
				or
				DATE_FORMAT(cancel, '%Y%m') = ym.getudo
				)
			left join juchuu_meisai jm 
			on jh.orderNO = jm.orderNO 
			group by ym.uid,DATE_FORMAT(juchuu_date, '%Y%m')
		) as jisseki
		left join online_seikyu seikyu
		on jisseki.uid = seikyu.uid
		and jisseki.getudo = seikyu.getudo
		order by jisseki.uid,jisseki.getudo;";

	//$stmt = $pdo_h->prepare($sql);
  //$stmt->bindValue("getudo", $getudo, PDO::PARAM_STR);
  //$stmt->execute();
  //$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$data = $db->SELECT($sql,["getudo" => $getudo]);

	try{
		//$pdo_h->beginTransaction();
		//$sqllog .= rtn_sqllog("START TRANSACTION",[]);
		$db->begin_tran();

		foreach($data as $row){
			log_writer2("row",$row,"lv3");
			$params["getudo1"] = $getudo;
			$params["uid1"] = $row["uid"];
			$params["jissekiA"] = $row["juchu_jisseki"];
			$params["seikyuA"] = $row["seikyu"];
			$params["seikyuB"] = $row["seikyu2"];
			$params["seikyuC"] = $row["seikyu3"];
			$params["kurikoshiA"] = $row["kurikoshi"];

			$params["jissekiD"] = $row["juchu_jisseki"];
			$params["seikyuE"] = $row["seikyu"];
			$params["seikyuF"] = $row["seikyu2"];
			$params["seikyuG"] = $row["seikyu3"];
			$params["kurikoshiB"] = $row["kurikoshi"];

			$sql_upd = "INSERT INTO online_seikyu (getudo,`uid`,jisseki,seikyu,seikyu2,seikyu3,kurikoshi) VALUES(:getudo1,:uid1,:jissekiA,:seikyuA,:seikyuB,:seikyuC,:kurikoshiA) 
			ON DUPLICATE KEY UPDATE jisseki = :jissekiD ,seikyu = :seikyuE,seikyu2 = :seikyuF,seikyu3 = :seikyuG,kurikoshi = :kurikoshiB";
			$db->UP_DEL_EXEC($sql_upd,$params);

			//次月のデータ作成
			$params['getudo1'] = $yokugetu;
			//$sql_ins = 'INSERT INTO online_seikyu (getudo,uid,zenkuri) VALUES(:getudo1,:uid1,:kurikoshiA)';
			$sql_ins = 'INSERT INTO online_seikyu (getudo,`uid`,zenkuri) VALUES(:getudo1,:uid1,:kurikoshiA)
			ON DUPLICATE KEY UPDATE zenkuri = :kurikoshiB';
			$db->UP_DEL_EXEC($sql_ins,["getudo1" => $params['getudo1'],"uid1" => $params['uid1'],"kurikoshiA" => $params['kurikoshiA'],"kurikoshiB" => $params['kurikoshiA']]);

		}
		$db->commit_tran();

		$to="green.green.midori@gmail.com";
		$subject="【".EXEC_MODE."】ONLINESHOP_請求データ作成完了-".$getudo;
		$body=$getudo."月度の請求データを作成しました。";
		$fromname=APP_NAME."@".EXEC_MODE;
		$bcc="";

		U::send_mail($to,$subject,$body,$fromname,$bcc);
		exit();
	}catch(Exception $e){
      $db->rollBack_tran($e->getMessage());
      log_writer2("\$e",$e,"lv0");
      $msg .= "システムエラーによる更新失敗。管理者へ通知しました。";
      $alert_status = "alert-danger";
      $reseve_status=true;
			echo $msg."\r\n".$e;
  }
  exit();
  

?>
