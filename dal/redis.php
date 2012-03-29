<?php
class Webpie_Dal_Redis extends Webpie_Dal_Cacheabstract
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
			$this->cacheObj[$name] = new Redis;
			$this->cacheObj[$name]->connect($this->setting['host'], $this->setting['port'], $this->setting['timeout']);

			if(isset($this->setting['options']))
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

	public function setCurCacheObj($obj)
	{
		if(in_array($obj, $this->cacheObj))
			$this->curCacheObj = $obj;
		else
			throw new Webpie_Dal_Exception('Dal Cache Error:You not connect the cache');

		return $this;
		
	}

	public function get($key, $options = NULL)
	{
		$getRes = $this->curCacheObj->get($key);
		if($getRes === false && !empty($options['callback'])
			return $options['callback']();
		else
			return $getRes;
	}

	public function mGet($key)
	{
		return $this->curCacheObj->mGet($key);
	}

	public function set($key, $val, $exp = NULL)
	{
		if($exp !== NULL && intval($exp) > 0)
			return $this->curCacheObj->setex($key, $exp, $val);
		else
			return $this->curCacheObj->set($key, $val);
	}

	public function append($key, $val)
	{
		return $this->curCacheObj->append($key, $val);
	}

	public function casToSet($key, $val, $exp = 0, $cas = NULL)
	{
		if(!$cas)
			throw new Webpie_Dal_Exception('Dal Cache Error: var $cas is NULL');

		return $this->curCacheObj->cas($cas, $key, $val, $exp);
	}

	public function decr($key, $offset = 1)
	{
		if($this->curCacheObj->get($key) === false)
			return $this->curCacheObj->set($key, $offset);

		return $this->curCacheObj->decrement($key, $offset);
	}

	public function incr($key, $offset = 1)
	{
		if($this->curCacheObj->get($key) === false)
			return $this->curCacheObj->set($key, $offset);

		return $this->curCacheObj->increment($key, $offset);
	}

	public function del($key)
	{
		if(is_array($key))
		{
			$curCacheObj = &$this->curCacheObj;
			return array_walk($key, function($k) use (&$curCacheObj){$curCacheObj->delete($k);});
		}
		else
			return $this->curCacheObj->delete($key);
	}
}
