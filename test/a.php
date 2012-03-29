<?php
class a
{
	public function __call($method, $args)
	{
		var_dump($method);
		var_dump($args);
		$b = array();
		for($i = 0, $j = count($args); $i < $j; $i++)
		{
			$b[] = &$args[$i];
		}
		call_user_func_array('test', $b);
	}
}

function test($a, $b, $c, $d, $f)
{
	var_dump($a);
	var_dump($b);
	var_dump($c);
	var_dump($d);
	var_dump($f);
}

$d = new a;
$d->test(1, 2, 3, 4, array('x','b'));
