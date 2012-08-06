<?php
require_once 'request.php';
class MemcacheTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		new webpie;
		$this->cache = new Webpie_Dal_Memcache;
		$this->setting = array(
			'servers' => array(array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 10)),
			'options' => array(
								array(Memcached::OPT_COMPRESSION, False),
							),
		);

		$this->cacheObjName = $this->cache->cacheSetting($this->setting);
		$this->cacheObj = $this->cache->cacheConnect($this->cacheObjName);
		$this->cache->setCurCacheObj($this->cacheObj);
		$this->cache->del('k1');
		$this->cache->del('k2');
		$this->cache->del('k3');
		$this->cache->del('k4');
		$this->cache->del('k5');
	}

	public function testCacheSetting()
	{
		$name = NULL;
		array_walk_recursive($this->setting, function($s) use (&$name){$name .= $s;});
		$this->assertEquals(md5($name), $this->cache->cacheSetting($this->setting));
	}

	public function testCacheConnect()
	{
		$setting = array(
			'servers' => array(array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 10)),
		);
		$this->assertInstanceOf('memcached', $this->cache->cacheConnect($this->cache->cacheSetting($setting)));
	}

	/**
	* @dataProvider getPro
	*
	* @returns   
	*/
	public function testGetAndSet($key, $val)
	{
		$this->assertTrue($this->cache->set($key, $val));
		$this->assertEquals($val, $this->cache->get($key));
	}

	public function getPro()
	{
		return array(
			array('k1', 1),
			array('k2', 2),
			array('k3', 3),
			array('k4', 4),
			array('k5', 5),
		);
	}

	public function testMGet()
	{
		$this->cacheObj->set('k1', 1);
		$this->cacheObj->set('k2', 2);
		$this->cacheObj->set('k3', 3);
		$this->cacheObj->set('k4', 4);
		$this->cacheObj->set('k5', 5);
		$this->assertEquals(array('k1' => 1, 'k2' => 2, 'k3' => 3, 'k4' => 4, 'k5' => 5), $this->cache->mGet(array('k1', 'k2', 'k3', 'k4', 'k5')));
	}

	/**
	* @dataProvider appendPro
	*
	* @param $key
	* @param $val
	* @param $append
	*
	* @returns   
	*/
	public function testAppend($key, $val, $append)
	{
		$this->cache->set($key, $val);
		$this->assertTrue($this->cache->append($key, $append));
		$this->assertEquals($val.$append, $this->cache->get($key));
	}
	
	public function appendPro()
	{
		return array(
			array('k1', '1', 'k'),
			array('k2', '2', 'k'),
			array('k3', '3', 'k'),
			array('k4', '4', 'k'),
			array('k5', '5', 'k'),
		);
	}

	/**
	* @dataProvider getPro
	*
	* @returns   
	*/
	public function testCasToSet($key, $val)
	{
		$this->cache->set($key, '1');
		$this->assertTrue($this->cache->casToSet($key, $val));
		$this->assertEquals($val, $this->cache->get($key));
	}

	public function testDecr()
	{
		$this->assertEquals(-1, $this->cache->decr('k1'));
		$this->assertEquals(-3, $this->cache->decr('k2', 3));
		$this->cache->set('k3', 1);
		$this->assertEquals(0, $this->cache->decr('k3'));
		$this->cache->set('k4', 1);
		$this->assertEquals(0, $this->cache->decr('k4', 3));
	}

	public function testIncr()
	{
		$this->assertEquals(1, $this->cache->incr('k1'));
		$this->assertEquals(3, $this->cache->incr('k2', 3));
		$this->cache->set('k3', 1);
		$this->assertEquals(2, $this->cache->incr('k3'));
		$this->cache->set('k4', 1);
		$this->assertEquals(4, $this->cache->incr('k4', 3));
	}

	/**
	* @dataProvider delPro
	*
	* @param $key
	* @param $val
	*
	* @returns   
	*/
	public function testDel($key, $val)
	{
		$this->cache->set($key, $val);
		$this->assertTrue($this->cache->del($key));
		$this->assertFalse($this->cache->get($key));
	}

	public function delPro()
	{
		return array(
			array('k1', 1),
			array('k2', 1),
			array('k3', 1),
			array('k4', 1),
			array('k5', 1),
			array('k6', 1),
		);
	}
}
