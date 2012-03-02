<?php
require '../webpie.php';
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
		$this->assertEquals($this->cObj->get('debug'), $importStr['debug']);
		$this->assertEquals($this->cObj->get('version'), $importStr['version']);
		$this->assertEquals($this->cObj->get('author'), $importStr['author']);
		$this->assertEquals($this->cObj->get('website'), $importStr['website']);
		$this->assertEquals($this->cObj->get('dba'), $importStr['dba']);
		$this->assertEquals($this->cObj->get('dba->type'), $importStr['dba']['type']);
		$this->assertEquals($this->cObj->get('dba->host'), $importStr['dba']['host']);
		$this->assertEquals($this->cObj->get('dba->db'), $importStr['dba']['db']);
		$this->assertEquals($this->cObj->get('dba->user'), $importStr['dba']['user']);
		$this->assertEquals($this->cObj->get('dba->pass'), $importStr['dba']['pass']);

		$importStr = './configFile.php';
		$this->cObj->import($importStr);
		$this->assertEquals($this->cObj->get('debug'), false);
		$this->assertEquals($this->cObj->get('version'), 1.1);
		$this->assertEquals($this->cObj->get('framework'), 'webpie');
		$this->assertEquals($this->cObj->get('dba->type'), 'mssql');
		$this->assertEquals($this->cObj->get('dba->host'), '192.168.1.1');
		$this->assertEquals($this->cObj->get('dba->db'), 'webpie');
		$this->assertEquals($this->cObj->get('dba->user'), 'root');
		$this->assertEquals($this->cObj->get('dba->pass'), '123456');
		$this->assertEquals($this->cObj->get('cache->redis->0'), '192.168.19.1:8000');
		$this->assertEquals($this->cObj->get('cache->memcache->0'), '192.168.18.1:7999');

	}

	public function testGetAndSet()
	{
		$this->assertEquals($this->cObj->get('author'), 'soone');
		$this->assertEquals($this->cObj->get('email'), 'fengyue15@gmail.com');
		$this->assertEquals($this->cObj->get('name'), 'Webpie');

		$this->cObj->set('test', array('x' => 1, 'y' => array('t' => 2), 'z' => array(3)));
		$this->assertEquals($this->cObj->get('test->x'), 1);
		$this->assertEquals($this->cObj->get('test->y->t'), 2);
		$this->assertEquals($this->cObj->get('test->z->0'), 3);

		$this->cObj->set('test->o', 1);
		$this->assertEquals($this->cObj->get('test->o'), 1);
		$this->cObj->set('test->o->b', 2);
		$this->assertEquals($this->cObj->get('test->o->b'), 2);

		$this->cObj->set('test->p->q', 2);
		$this->cObj->set('test->p->x', 3);
		$this->assertEquals($this->cObj->get('test->p->q'), 2);
		$this->assertEquals($this->cObj->get('test->p->x'), 3);

		$this->assertEquals($this->cObj->get('fsdaf', ''), '');
	}
}
