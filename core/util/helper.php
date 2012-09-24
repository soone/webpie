<?php
class Webpie_Helper
{
	public static function getClientIp()
	{
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			$onlineIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
		elseif(!empty($_SERVER['HTTP_CLIENT_IP']))
			$onlineIp = $_SERVER['HTTP_CLIENT_IP'];
		else
			$onlineIp = $_SERVER['REMOTE_ADDR'];

		return $onlineIp;
	}

	/**
	 * @name randomStr 获取随机字符串
	 *
	 * @param $len
	 *
	 * @return 
	 */
	public static function randomStr($len = 31)
	{
		$seed = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '2', '3', '4', '5', '6', '7', '8', '9');
		shuffle($seed);
		if($len > count($seed))
			return implode('', $seed);
		else
			return implode('', array_slice($seed, 0, $len));
	}
}
