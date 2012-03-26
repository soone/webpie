<?php
class Webpie_Dal_Memcache implements Webpie_Dal_Cacheinterface
{
	public $setting = NULL;
	private $cacheObj = NULL;
	private $curCacheObj = NULL;
	public function __construct(){}

	public function cacheSetting($setting)
	{
		$this->setting = $setting;
		$cacheObjName = md5(implode('', $this->setting));
		$this->cacheObj[$cacheObjName] = NULL;
		return $cacheObjName;
	}

	public function cacheConnect($name)
	{
		if(!is_object($this->cacheObj[$name])){}

		return $this->cacheObj[$name];
	}

	public function cacheCreate()
	{
		
	}
}
