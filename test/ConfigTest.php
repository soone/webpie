<?php
require '../webpie.php';
require '../config.php';
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
		return True;
	}

	/**
	* @dataProvider getProvider
	*/
	public function testGet($var, $val)
	{
		//$this->cObj->set('test', array('x' => 1, 'y' => array('t' => 2), 'z' => array(3)));
		$this->cObj->set('test->o', 1);
		$this->cObj->set('test->p->q', 2);
		$this->assertEquals($this->cObj->get($var), $val);

		//$this->cObj->set('test->o->p', 3);
		//$this->assertEquals($this->cObj->get('test->o'), 1);
		//$this->assertEquals($this->cObj->get('test->o->p'), 3);
	}

	public function getProvider()
	{
		return array(
			array('author', 'soone'),
			array('email', 'fengyue15@gmail.com'),
			array('name', 'Webpie'),
			//array('test->x', 1),
			//array('test->y->t', 2),
			//array('test->z->0', 3),
			//array('test->o', 1),
			array('test->p->q', 2),
		);
	}

	public function testSet()
	{
		return True;
	}
}
