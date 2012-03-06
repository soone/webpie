<?php
class Webpie_Handler_Exception extends Webpie_Exception{}

class Webpie_Handler
{
	public function __construct()
	{}

	public function render($tpl, $assign = NULL, $fetch = False)
	{
		if(is_object($this->view))
		{
			if($fetch)
				return $this->view->fetch($tpl, $assign);
			else
				return $this->view->display($tpl, $assign);
		}

		//进行模板引擎初始化
		//$this->view = new 
	}
}
