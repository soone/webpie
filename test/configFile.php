<?php
return array(
	'framework' => 'webpie',
	'version' => 1.1,
	'debug' => false,
	'dba' => array(
		'type' => 'mssql',
		'host' => '192.168.1.1',
		'db' => 'webpie',
		'user' => 'root',
		'pass' => '123456',
	),
	'cache' => array(
		'redis' => array('192.168.19.1:8000'),
		'memcache' => array('192.168.18.1:7999')
	),
);
