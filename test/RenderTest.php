<?php
require_once '/data/www/webpie/core/webpie.php';
class RenderTest extends PHPUnit_Framework_TestCase
{
	public $cObj = NULL;
	public function setUp()
	{
		new webpie;
	}

	public function testConstruct()
	{
		$setting = array(
			'debugging' => true, 
			'caching' => true,
			'cache_lifetime' => 120,
			'left_delimiter' => '<!--{',
			'right_delimiter' => '}-->'
		);

		$view = new Webpie_Render($setting);
		$this->assertEquals($view->get('debugging'), true);
		$this->assertEquals($view->get('caching'), true);
		$this->assertEquals($view->get('left_delimiter'), '<!--{');
		$this->assertEquals($view->get('right_delimiter'), '}-->');
		$this->assertEquals($view->get('cache_lifetime'), 120);
	}
}
