<?php
class test_handlerPro
{
	public function __construct()
	{
		$this->cObj = Webpie_Config::getInstance();
	}

	public function setup($val = NULL)
	{
		if($val)
			$this->cObj->set('testHook', $val);
		else
			$this->cObj->set('testHook', 'no val');
	}
}
