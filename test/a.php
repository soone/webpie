<?php
var_dump($_SERVER);
$reqUri = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '?'));
var_dump($reqUri);
class a
{
	function x()
	{
		echo 'fdsf';
	}
}

$c = new a();
$c->x();
