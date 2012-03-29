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
		if(!is_object($this->cacheObj[$name]))
		{
			$this->cacheObj[$name] = new Memcached;
			$this->cacheObj[$name]->addServers($this->settings['servers']);

			if(isset($this->settings['options']))
			{
				foreach($this->settiongs['options'] as $opt)
				{
					if(!$this->cacheObj[$name]->setOption($opt[0], $opt[1]))
						throw new Webpie_Dal_Exception('Dal Cache Error:setOption fail');
				}
			}
		}

		return $this->cacheObj[$name];
	}

	public function get($key, $options = NULL)
	{
		return $this->curCacheObj->get($key);
	}

	public function __call($func, $args)
	{
		if(method_exists($this->curCacheObj, $func))
		{
			if(count($args) > 1)
			{
				$argArr = array();
				for($i = 0, $j < count($args); $i < $j; $i++)
				{
					$argArr[] = &$args[$i];
				}

				return call_user_func_array(array($this->curCacheObj, $func), $args);
			}
			else
				return call_user_func(array($this->curCacheObj, $func), $args);
		}
		else
			throw new Webpie_Dal_Exception('Dal Cache Error: No Invalid method');
	}
}
