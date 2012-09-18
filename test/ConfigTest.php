<?php
require_once 'request.php';
class ConfigTest extends PHPUnit_Framework_TestCase
{
	public $cObj = NULL;
	public function setUp()
	{
		new webpie;
		$this->cObj = Webpie_Config::getInstance();
	}

	public function testGetInstance()
	{
		$this->cObj->x = 1;
		$this->assertTrue(is_object($this->cObj));
		
		$bObj = Webpie_Config::getInstance();
		$this->assertTrue($this->cObj->x === $bObj->x);
	}

	public function testImport()
	{
		$importStr = array(
			'debug' => true,
			'version' => 1.0,
			'author' => 'soone',
			'website' => 'www.wendaju.com',
			'dba' => array(
				'type' => 'mysql',
				'host' => 'localhost',
				'db' => 'wendaju',
				'user' => 'root',
				'pass' => '123456',
			),
		);

		$this->cObj->import($importStr);
		$this->assertEquals($importStr['debug'], $this->cObj->get('debug'));
		$this->assertEquals($importStr['version'], $this->cObj->get('version'));
		$this->assertEquals($importStr['author'], $this->cObj->get('author'));
		$this->assertEquals($importStr['website'], $this->cObj->get('website'));
		$this->assertEquals($importStr['dba'], $this->cObj->get('dba'));
		$this->assertEquals($importStr['dba']['type'], $this->cObj->get('dba->type'));
		$this->assertEquals($importStr['dba']['host'], $this->cObj->get('dba->host'));
		$this->assertEquals($importStr['dba']['db'], $this->cObj->get('dba->db'));
		$this->assertEquals($importStr['dba']['user'], $this->cObj->get('dba->user'));
		$this->assertEquals($importStr['dba']['pass'], $this->cObj->get('dba->pass'));

		$importStr = WEBPIE . 'test/configFile.php';
		$this->cObj->import($importStr);
		$this->assertEquals(false, $this->cObj->get('debug'));
		$this->assertEquals(1.1, $this->cObj->get('version'));
		$this->assertEquals('webpie', $this->cObj->get('framework'));
		$this->assertEquals('mssql', $this->cObj->get('dba->type'));
		$this->assertEquals('localhost', $this->cObj->get('dba->host'));
		$this->assertEquals('webpie', $this->cObj->get('dba->db'));
		$this->assertEquals('root', $this->cObj->get('dba->user'));
		$this->assertEquals('123456', $this->cObj->get('dba->pass'));
		$this->assertEquals('localhost:8000', $this->cObj->get('cache->redis->0'));
		$this->assertEquals('localhost:7999', $this->cObj->get('cache->memcache->0'));
	}

	public function testGetAndSet()
	{
		$this->assertEquals('soone', $this->cObj->get('author'));
		$this->assertEquals('fengyue15@gmail.com', $this->cObj->get('email'));
		$this->assertEquals('Webpie', $this->cObj->get('name'));
		$this->assertEquals(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core', $this->cObj->get('wpRoot'));
		$this->assertEquals(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'error.log', $this->cObj->get('log'));

		$this->cObj->set('test', array('x' => 1, 'y' => array('t' => 2), 'z' => array(3)));
		$this->assertEquals(1, $this->cObj->get('test->x'));
		$this->assertEquals(2, $this->cObj->get('test->y->t'));
		$this->assertEquals(3, $this->cObj->get('test->z->0'));

		$this->cObj->set('test->o', 1);
		$this->assertEquals(1, $this->cObj->get('test->o'));
		$this->cObj->set('test->o->b', 2);
		$this->assertEquals(2, $this->cObj->get('test->o->b'));

		$this->cObj->set('test->p->q', 2);
		$this->cObj->set('test->p->x', 3);
		$this->assertEquals(2, $this->cObj->get('test->p->q'));
		$this->assertEquals(3, $this->cObj->get('test->p->x'));

		$this->assertEquals('', $this->cObj->get('fsdaf', ''));
	}

	public function testGetReqWith()
	{
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		$this->assertTrue($this->cObj->getReqWith());
		$_SERVER['HTTP_X_REQUESTED_WITH'] = '';
		$this->assertFalse($this->cObj->getReqWith());
	}
}
