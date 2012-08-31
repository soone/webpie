<?php
class Webpie_Helper
{
	public static getClientIp()
	{
		if($_SERVER['HTTP_X_FORWARDED_FOR'])
			$onlineIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
		elseif($_SERVER['HTTP_CLIENT_IP'])
			$onlineIp = $_SERVER['HTTP_CLIENT_IP'];
		else
			$onlineIp = $_SERVER['REMOTE_ADDR'];

		return $onlineIp;
	}
}
