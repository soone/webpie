<?php
return array(
	'framework' => 'webpie',
	'version' => 1.1,
	'debug' => false,
	'dba' => array(
		'type' => 'mssql',
		'host' => 'localhost',
		'db' => 'webpie',
		'user' => 'root',
		'pass' => '123456',
	),
	'cache' => array(
		'redis' => array('localhost:8000'),
		'memcache' => array('localhost:7999')
	),
);
