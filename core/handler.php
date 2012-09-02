<?php
class Webpie_Handler_Exception extends Webpie_Exception{}

class Webpie_Handler
{
	public $view = NULL;

	public function __construct(){}

	public function checkInput()
	{
		if($_ENV['envConf']->import($_ENV['envConf']->get('projectConf') . 
					$_ENV['envConf']->get('router')['control'] . 'Conf.php') === False)
			return False;

		$filters = $_ENV['envConf']->get($_ENV['envConf']->get('router')['action']);
		if(empty($filters))
			return False;

		$msg = array();
		foreach($filters as $fk => $f)
		{
			foreach($f[0] as $k => $v)
			{
				if(empty($f[1][$k])) continue;
				$valid = new Webpie_Valid($v, $f[1][$k]);
				if($valid->toValid() === False)
				{
					$msg[] = $valid->alertMsg;
				}
				else
					$_ENV[$fk][$k] = $valid->validVar;
			}
		}

		return empty($msg) ? True : $msg;
	}

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
