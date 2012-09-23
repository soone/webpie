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

		$filters = $_ENV['envConf']->get(strtolower($_ENV['envConf']->get('router')['action']));
		if(empty($filters))
			return False;

		$msg = array();
		foreach($filters as $fk => $f)
		{
			foreach($f[1] as $k => $v)
			{
				if(empty($f[0][$k]) && (empty($v['required']) || $v['required'] != 1))
					continue;

				$valid = new Webpie_Valid($f[0][$k], $v);
				if($valid->toValid() === False)
				{
					$msg[] = $valid->alertMsg;
				}
				else
					$_ENV[$fk][$k] = $valid->validVar;
			}

			$_ENV[$fk] = empty($_ENV[$fk]) ? $f[0] : array_merge($_ENV[$fk], array_diff_key($f[0], $_ENV[$fk]));
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
			$this->view = new $templateConf['engine']($templateConf['setting']);
		}

		if($fetch)
			return $this->view->fetch($tpl, $assign);
		else
			return $this->view->display($tpl, $assign);
	}
}
