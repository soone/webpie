<?php
class test_handlerPro1
{
	public function __construct()
	{
		$this->cObj = Webpie_Config::getInstance();
	}

	public function setup($val = NULL)
	{
		if($val)
			$this->cObj->set('testHook1', $val);
		else
			$this->cObj->set('testHook1', 'no val');
	}
}
