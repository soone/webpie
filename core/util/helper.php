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
			$onlineIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

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

	/**
	 * @name getSession 
	 *
	 * @return 
	 */
	public static function getSession()
	{
		session_start();
		return $_SESSION;
	}

	/**
	 * @name isSSL 判断是否使用443端口
	 *
	 * @return 
	 */
	public static function isSSL()
	{
		return ($_SERVER['SERVER_PORT'] == 443) ? TRUE : FALSE;
	}

	/**
	 * @name generateSn 
	 *
	 * @param $params
	 * @param $secrect
	 * @param $type
	 *
	 * @return 
	 */
	public static function generateSn($seed, $secrect, $type = 'sha1')
	{
		ksort($seed);
		$sign = '';
		foreach($seed as $k => $v)
		{
			if(is_array($v))
				$sign .= implode($k . '[]', $v);
			else
				$sign .= $k . $v;
		}

		unset($k, $v);
		return base64_encode(hash_hmac($type, $sign, $secrect));
	}
}
