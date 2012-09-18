<?php
require_once 'request.php';
class WebpieTest extends PHPUnit_Framework_TestCase
{
	private $wp = NULL;
	public function setUp()
	{
		$this->wp = new Webpie();
	}

	public function testStart()
	{
		$this->wp->start();
	}
}
