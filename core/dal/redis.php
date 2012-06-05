<?php
class Webpie_Dal_Redis extends Webpie_Dal_Cacheabstract
{
	public $setting = NULL;
	private $cacheObj = NULL;
	private $curCacheObj = NULL;
	public function __construct(){}

	/**
	* @name cacheSetting 缓存服务器信息设置
	*
	* @param $setting
	*
	* @returns   
	*/
	public function cacheSetting($setting)
	{
		$this->setting = $setting;
		$cacheObjName = NULL;
		array_walk_recursive($this->setting, function($s) use (&$cacheObjName){$cacheObjName .= $s;});
		$cacheObjName = md5($cacheObjName);
		!is_object($this->cacheObj[$cacheObjName]) ? $this->cacheObj[$cacheObjName] = NULL : '';
		return $cacheObjName;
	}

	/**
	* @name cacheConnect 连接缓存服务器，并设置预定义属性
	*
	* @param $name
	*
	* @returns   
	*/
	public function cacheConnect($name)
	{
		if(!is_object($this->cacheObj[$name]))
		{
			$this->cacheObj[$name] = new Redis;
			$this->cacheObj[$name]->connect($this->setting['host'], $this->setting['port'], $this->setting['timeout']);
			$this->cacheObj[$name]->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP); 

			if(!empty($this->setting['options']))
			{
				foreach($this->setting['options'] as $opt)
				{
					if(!$this->cacheObj[$name]->setOption($opt[0], $opt[1]))
						throw new Webpie_Dal_Exception('Dal Cache Error:setOption fail');
				}
			}
		}

		return $this->cacheObj[$name];
	}

	/**
	* @name setCurCacheObj 设置当前cache对象
	*
	* @param $obj
	*
	* @returns   
	*/
	public function setCurCacheObj($obj)
	{
		if(in_array($obj, $this->cacheObj))
			$this->curCacheObj = $obj;
		else
			throw new Webpie_Dal_Exception('Dal Cache Error:You not connect the cache');

		return $this;
		
	}

	/**
	* @name get 
	*
	* @param $key
	* @param $options
	*
	* @returns   
	*/
	public function get($key, $options = NULL)
	{
		$getRes = $this->curCacheObj->get($key);
		if($getRes === false && !empty($options['callback']))
			return $options['callback']();
		else
			return $getRes;
	}

	/**
	* @name mGet 同时取得多个key的值
	*
	* @param Array $key
	*
	* @returns   
	*/
	public function mGet($key)
	{
		$vals = $this->curCacheObj->mGet($key);
		$res = array();
		for($i = 0, $j = count($key); $i < $j; $i++)
		{
			$res[$key[$i]] = $vals[$i];
		}

		return $res;
	}

	/**
	* @name set 
	*
	* @param $key
	* @param $val
	* @param $exp
	*
	* @returns   
	*/
	public function set($key, $val, $exp = NULL)
	{
		if($exp !== NULL && intval($exp) > 0)
			return $this->curCacheObj->setex($key, $exp, $val);
		else
			return $this->curCacheObj->set($key, $val);
	}

	/**
	* @name append 对已经存在的key的值进行追加值
	*
	* @param $key
	* @param $val
	*
	* @returns   
	*/
	public function append($key, $val)
	{
		if($this->curCacheObj->append($key, $val) > 0)
			return true;
		else
			return false;
	}

	/**
	* @name casToSet 执行一个“检查并设置”的操作，因此，它仅在当前客户端最后一次取值后，该key 对应的值没有被其他客户端修改的情况下， 才能够将值写入。
	*
	* @param $key
	* @param $val
	* @param $exp
	* @param $cas
	*
	* @returns   
	*/
	public function casToSet($key, $val, $exp = 0)
	{
		$this->curCacheObj->watch($key);
		$res = $this->curCacheObj->multi()->set($key, $val)->exec();
		return $res[0];
	}

	/**
	* @name decr 减小数值元素的值
	*
	* @param $key
	* @param $offset
	*
	* @returns   
	*/
	public function decr($key, $offset = NULL)
	{
		return $offset ? $this->curCacheObj->decr($key, $offset) : $this->curCacheObj->decr($key);
	}

	/**
	* @name incr 增加数值元素的值
	*
	* @param $key
	* @param $offset
	*
	* @returns   
	*/
	public function incr($key, $offset = NULL)
	{
		return $offset ? $this->curCacheObj->incr($key, $offset) : $this->curCacheObj->incr($key);
	}

	/**
	* @name del 删除一个元素
	*
	* @param $key
	*
	* @returns   
	*/
	public function del($key)
	{
		return $this->curCacheObj->delete($key);
	}
}
