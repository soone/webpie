<?php
require '../webpie.php';
class WebpieTest extends PHPUnit_Framework_TestCase
{
	private $wp = NULL;
	public function setUp()
	{
		$this->wp = new Webpie();
	}
}

