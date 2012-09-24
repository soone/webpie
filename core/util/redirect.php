<?php
class Webpie_Redirect
{
	private static $codes = array(
		'404' => '404 Not Found',
		'200' => '200 OK',
		'50x' => '503 Service Unavailable',
		'30x' => '303 See Other',
		'302' => '302 Moved Temporarily',
	);
	
	/**
	* @name see 
	*
	* @param $status
	* @param $retUrl
	* @param $msg
	*
	* @returns   
	*/
	public static function see($status, $retUrl = NULL, $msg = NULL)
	{
		header('HTTP/1.1 ' . self::$codes[$status]);
		header('Status: ' . self::$codes[$status]);
		if(is_array($msg))
			$msg = implode($_ENV['envConf']->get('logStage'), $msg);

		if($_ENV['envConf']->getReqWith())
			echo json_encode(array('retUrl' => $retUrl, 'msg' => $msg, 'status' => $status));
		else if($retUrl)
			header('Location:' . $retUrl);

		exit();
	}

	/**
	* @name __callStatic 
	*
	* @param $method
	* @param $args
	*
	* @returns   
	*/
	public static function __callStatic($method, $args)
	{
		$code = substr($method, -3, 3);
		if(array_key_exists($code, self::$codes))
		{
			self::see($code, empty($args[0]) ? NULL : $args[0], empty($args[1]) ? NULL : $args[1]);
		}
		else
			throw new Webpie_Util_Exception('未定义的状态码');
	}
}
