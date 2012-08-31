<?php
class Webpie_Dal_Exception extends Webpie_Exception{}

class Webpie_Dal
{
	public $type;
	public $dalInfo;
	protected $objType = NULL;

	public function __construct(array $dalInfo)
	{
		$this->type = $dalInfo['type'];
		unset($dalInfo['type']);
		$this->dalInfo = $dalInfo;
	}

	public function factory()
	{
		switch($this->type)
		{
			case 'memcached':
				$this->objType = 'Webpie_Dal_Memcache';
				return $this->setCacheObj();
				break;

			case 'redis':
				$this->objType = 'Webpie_Dal_Redis';
				return $this->setCacheObj();
				break;

			case 'mysql':
				$this->objType = 'Webpie_Dal_Mysql';
				return $this->setDbObj();
				break;

			default:
				throw new Webpie_Dal_Exception('Unknown dal type');
		}
	}

	protected function setCacheObj()
	{
		$cache = new $this->objType;
		$cache->setCurCacheObj($cache->cacheConnect($cache->cacheSetting($this->dalInfo)));
		return $cache;
	}

	protected function setDbObj()
	{
		$db = new $this->objType;
		$db->setCurDbObj($db->dbConnect($db->dbSetting($this->dalInfo)));
		return $db;
	}
}
