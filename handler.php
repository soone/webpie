<?php
class Webpie_Handler_Exception extends Webpie_Exception{}

class Webpie_Handler
{
	public $view = NULL;

	public function __construct(){}

	/**
	* @name render 
	*
	* @param $tpl
	* @param $assign
	* @param $fetch
	*
	* @returns   
	*/
	public function render($tpl, $assign = NULL, $fetch = False)
	{
		if(!is_object($this->view))//进行模板引擎初始化
		{
			$templateConf = $_ENV['envConf']->get('templateConf');
			if($templateConf['engine'] == 'smarty')
				$className = 'Webpie_Render';
			else
				$className = $templateConf['engine'];

			$this->view = new $className($templateConf['setting']);
		}

		if($fetch)
			return $this->view->fetch($tpl, $assign);
		else
			return $this->view->display($tpl, $assign);
	}
}
