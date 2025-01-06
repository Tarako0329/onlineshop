<?php
	//毎月１日にcornジョブから実行する
	require "php_header.php";

	
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

	$sql = "select 
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

	$stmt = $pdo_h->prepare($sql);
  $stmt->bindValue("getudo", $getudo, PDO::PARAM_STR);
  $stmt->execute();
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

	try{
			$pdo_h->beginTransaction();
			$sqllog .= rtn_sqllog("START TRANSACTION",[]);

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

			$sql_upd = "INSERT INTO online_seikyu (getudo,uid,jisseki,seikyu,seikyu2,seikyu3,kurikoshi) VALUES(:getudo1,:uid1,:jissekiA,:seikyuA,:seikyuB,:seikyuC,:kurikoshiA) ON DUPLICATE KEY UPDATE	jisseki = :jissekiD ,seikyu = :seikyuE,seikyu2 = :seikyuF,seikyu3 = :seikyuG,kurikoshi = :kurikoshiB";

			$sqllog .= rtn_sqllog($sql_upd,$params);

			$stmt2 = $pdo_h->prepare($sql_upd);
			$stmt2->bindValue("getudo1", $params['getudo1'], PDO::PARAM_INT);
			$stmt2->bindValue("uid1", $params['uid1'], PDO::PARAM_INT);
			$stmt2->bindValue("jissekiA", $params['jissekiA'], PDO::PARAM_INT);
			$stmt2->bindValue("seikyuA", $params['seikyuA'], PDO::PARAM_INT);
			$stmt2->bindValue("seikyuB", $params['seikyuB'], PDO::PARAM_INT);
			$stmt2->bindValue("seikyuC", $params['seikyuC'], PDO::PARAM_INT);
			$stmt2->bindValue("kurikoshiA", $params['kurikoshiA'], PDO::PARAM_INT);

			$stmt2->bindValue("jissekiD", $params['jissekiD'], PDO::PARAM_INT);
			$stmt2->bindValue("seikyuE", $params['seikyuE'], PDO::PARAM_INT);
			$stmt2->bindValue("seikyuF", $params['seikyuF'], PDO::PARAM_INT);
			$stmt2->bindValue("seikyuG", $params['seikyuG'], PDO::PARAM_INT);
			$stmt2->bindValue("kurikoshiB", $params['kurikoshiB'], PDO::PARAM_INT);
			$stmt2->execute();
			
			$sqllog .= rtn_sqllog("--execute():正常終了",[]);

			//次月のデータ作成
			$params['getudo1'] = $yokugetu;
			$sql_ins = 'INSERT INTO online_seikyu (getudo,uid,zenkuri) VALUES(:getudo1,:uid1,:kurikoshiA)';
			$stmt3 = $pdo_h->prepare($sql_ins);
			$sqllog .= rtn_sqllog($sql_ins,$params);
		
			$stmt3->bindValue("getudo1", $params['getudo1'], PDO::PARAM_INT);
			$stmt3->bindValue("uid1", $params['uid1'], PDO::PARAM_INT);
			$stmt3->bindValue("kurikoshiA", $params['kurikoshiA'], PDO::PARAM_INT);

			$stmt3->execute();
			
			$sqllog .= rtn_sqllog("--execute():正常終了",[]);


		}
		$pdo_h->commit();
		$sqllog .= rtn_sqllog("commit",[]);
		sqllogger($sqllog,0);

		$to="green.green.midori@gmail.com";
		$subject="【".EXEC_MODE."】ONLINESHOP_請求データ作成完了-".$getudo;
		$body=$getudo."月度の請求データを作成しました。";
		$fromname=TITLE."@".EXEC_MODE;
		$bcc="";

		send_mail($to,$subject,$body,$fromname,$bcc);
		exit();
	}catch(Exception $e){
      $pdo_h->rollBack();
      $sqllog .= rtn_sqllog("rollBack",[]);
      sqllogger($sqllog,$e);
      log_writer2("\$e",$e,"lv0");
      $msg .= "システムエラーによる更新失敗。管理者へ通知しました。";
      $alert_status = "alert-danger";
      $reseve_status=true;
			echo $msg."<br>".$e;
  }


?>
