<?php
require '/data/www/webpie/webpie.php';
class WebpieTest extends PHPUnit_Framework_TestCase
{
	private $wp = NULL;
	public function setUp()
	{
		$this->wp = new Webpie();
		$this->cObj = Webpie_Config::getInstance();
	}

	public function testHandler()
	{
		$this->cObj->set('projectRoot', dirname(__DIR__) . '/');
		$this->assertEquals($this->wp->handler('test_WebpieTest->t'), 'x');
		$this->assertEquals($this->wp->handler('test_WebpieTest::s'), 'this is static method');
		$this->assertEquals($this->wp->handler('test_WebpieTest::s', 'test_handlerPro'), 'this is static method');
		$this->assertEquals($this->cObj->get('testHook'), 'no val');

		$this->wp->handler('test_WebpieTest::s', array(
														array('test_handlerPro', 'hello')
														)
		);
		$this->assertEquals($this->cObj->get('testHook'), 'hello');
		$this->wp->handler('test_WebpieTest::s', array(
														array('test_handlerPro', array('test'))
														)
		);
		$this->assertEquals($this->cObj->get('testHook'), array('test'));

		$this->cObj->set('hooks', 'test_handlerPro1');
		$this->assertEquals($this->wp->handler('test_WebpieTest::s'), 'this is static method');

		$this->cObj->set('hooks', array(
										array('test_handlerPro1', 'hello1'),
										array('test_handlerPro', array('test')),
										)
		);
		$this->assertEquals($this->wp->handler('test_WebpieTest::s'), 'this is static method');
		$this->assertEquals($this->cObj->get('testHook'), array('test'));
		$this->assertEquals($this->cObj->get('testHook1'), 'hello1');

		$this->cObj->set('hooks', array(
										array('test_handlerPro1', array('test1')),
										array('test_handlerPro', 'hello'),
										)
		);

		$this->assertEquals($this->wp->handler('test_WebpieTest::s'), 'this is static method');
		$this->assertEquals($this->cObj->get('testHook'), 'hello');
		$this->assertEquals($this->cObj->get('testHook1'), array('test1'));

		$this->cObj->set('hooks', array(
										array('test_handlerPro1', array('test2')),
										)
		);
		$this->wp->handler('test_WebpieTest::s', array(
														array('test_handlerPro', 'hello')
														));
		$this->assertEquals($this->cObj->get('testHook'), 'hello');
		$this->assertEquals($this->cObj->get('testHook1'), array('test2'));
	}
}

class test_WebpieTest
{
	public function t()
	{
		return 'x';
	}

	static public function s()
	{
		return 'this is static method';
	}
}
