<?php
abstract class Webpie_Dal_Cacheabstract
{
	abstract public function cacheSetting($setting);
	abstract public function cacheConnect($name);
	abstract public function setCurCacheObj($obj);
	abstract public function get($key, $options = NULL);
	abstract public function mGet($key);
	abstract public function set($key, $val, $exp = NULL);
	abstract public function append($key, $val);
	abstract public function casToSet($key, $val, $exp = 0);
	abstract public function decr($key, $offset = NULL);
	abstract public function incr($key, $offset = NULL);
	abstract public function del($key);
	public function __call($method, $args)
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
