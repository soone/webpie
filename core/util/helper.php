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

	/**
		* @name authCode 
		*
		* @param $str
		* @param $operation
		* @param $key
		* @param $expiry
		*
		* @return 
	 */
	public static function authCode($str, $operation = 'DECODE', $key, $expiry = 0)
	{
		$ckeyLen = 4;
		$key = md5($key);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckeyLen ? ($operation == 'DECODE' ? substr($str, 0, $ckeyLen):                   substr(md5(microtime()), -$ckeyLen)) : '';
		
		$cryptkey = $keya.md5($keya.$keyc);
		$keyLen = strlen($cryptkey);
		
		$str = $operation == 'DECODE' ? base64_decode(substr($str, $ckeyLen)) : sprintf('%010d',   $expiry ? $expiry + time() : 0).substr(md5($str.$keyb), 0, 16).$str;
		$strLen = strlen($str);
		
		$result = '';
		$box = range(0, 255);
		
		$rndkey = array();
		for($i = 0; $i <= 255; $i++)
		{
			$rndkey[$i] = ord($cryptkey[$i % $keyLen]);
		}
		
		for($j = $i = 0; $i < 256; $i++)
		{
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		
		for($a = $j = $i = 0; $i < $strLen; $i++)
		{
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($str[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		
		if($operation == 'DECODE')
		{
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10,   16) == substr(md5(substr($result, 26).$keyb), 0, 16))
				return substr($result, 26);
			else
				return '';
		}
		else
			return $keyc.str_replace('=', '', base64_encode($result));
	}
}
