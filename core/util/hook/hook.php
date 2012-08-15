<?php
/**
* @file hook.php
* @name 钩子
* @author soone fengyue15@gmail.com
* @version 0.1
* @date 2012-08-16
*/
class Webpie_Hook_Exception extends Webpie_Exception{}
class Webpie_Hook implements Webpie_Hook_Interface
{
	public function __construct()
	{
		$this->xrsf = $_ENV['envConf']->get('xrsf', FALSE);
		$this->inputs = array();
	}

	public function setup($vars = NULL)
	{
		return NULL;
	}
}
