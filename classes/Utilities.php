<?php
declare(strict_types=1);

class Utilities {
	/*private const AUTH_OPTIONS = [
		'cost' => 12, // 計算負荷を上げる（将来的に数値を増やす）
	];*/
  //private string $key = "";

  private function __construct() {}

	public static function exist($value): bool {
		// =========================================================
		// スペース、0を含め、$valueに何かしら値がセットされてればtrueを返す。それ以外はfalse
		// =========================================================
		return $value !== null && $value !== "";
	}

	public static function send_line(string $to,string $body):bool{
		log_writer2("Util::send_line - \$to",$to,"lv3");
		log_writer2("Util::send_line - \$body",$body,"lv3");
		if(EXEC_MODE==="Local"){
			return true;
		}

		$url = ROOT_URL.'line_push_msg.php';

		$data = array(
			'LINE_USER_ID' => $to,
			'MSG' => $body,
		);

		$context = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => implode("\r\n", array('Content-Type: application/x-www-form-urlencoded',)),
				'content' => http_build_query($data)
			)
		);

		$return = file_get_contents($url, false, stream_context_create($context));
		if($return !== false){
			$return = true;
		}

		return $return;
	}

}
?>