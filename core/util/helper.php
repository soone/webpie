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
}
