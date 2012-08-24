<?php
class Webpie_Dal_Exception extends Webpie_Exception{}

class Webpie_Dal
{
	protected $objType = NULL;

	public function __construct(array $dalInfo)
	{
		$type = $dalInfo['type'];
		unset($dalInfo['type']);

		switch($type)
		{
			case 'memcached':
				$this->objType = 'Webpie_Dal_Memcache';
				return $this->setCacheObj($dalInfo);
				break;

			case 'redis':
				$this->objType = 'Webpie_Dal_Redis';
				return $this->setCacheObj($dalInfo);
				break;

			case 'mysql':
				$this->objType = 'Webpie_Dal_Mysql';
				return $this->setDbObj($dalInfo);
				break;

			default:
				throw new Webpie_Dal_Exception('Unknown dal type');
		}
	}

	protected function setCacheObj($dalInfo)
	{
		$cache = new $this->objType;
		$cache->setCurCacheObj($cache->cacheConnect($cache->cacheSetting($cacheInfo)));
		return $cache;
	}

	protected function setDbObj($dalInfo)
	{
		$db = new $this->objType;
		$db->setCurDbObj($db->dbConnect($db->dbSetting($dalInfo)));
		return $db;
	}
}
