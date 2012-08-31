<?php
class Webpie_Module_Exception extends Webpie_Exception{}

class Webpie_Module
{
	public $dalObj;
	public $cObj;

	public function __construct(){}

	public function factoryDal($dalInfo)
	{
		$this->dalObj = new Webpie_Dal($dalInfo);
		$this->cObj = $this->dalObj->factory();
	}

	public function getByCacheAndDb($cKey, $cObj, array $dbObj, $timeout = NULL)
	{
		if(!is_object($cObj))
			throw new Webpie_Module_Exception('No cache object');

		$cInfo = $cObj->get($cKey);
		if($cInfo !== False)
			return $cInfo;

		if(!is_object($dbObj[0]) || !is_callable(array($dbObj[0], $dbObj[1])))
			throw new Webpie_Module_Exception('No model object or no callable method');

		$cInfo = call_user_func_array(array($dbObj[0], $dbObj[1]), !empty($dbObj[2]) ? $dbObj[2] : array());
		$cObj->set($cKey, $cInfo, $timeout);
		return $cInfo;
	}
}
