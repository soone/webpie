<?php
require '../webpie.php';
class InputsTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		new webpie;
	}

	public function test__(){}

	public function source()
	{
		$test= array(
			'cookie' => array(
				'userName' => array(
					'required' => 1,
					'preExpect' => 'trim',
					'equalTo' => 'soone',
					'msg' => '请先登录',
				),
				'password' => array(
					'required' => 1,
					'preExpect' => 'auth_decode',
					'equalTo' => true,
					'msg' => '请先登录'
				),
				'__webpieRedirect' => './login',
			),
			'get' => array(),
			'post' => array(),
			'session' => array(),
			'env' => array(),
			'request' => array(),
			'server' => array(),
			'__webpieRedirect' => './login',
		);
	}
}

$rules = array(
	'name' => array(
		'required' => 1,
		'msg' => 'name is have to fill',
		'expect' => Webpie_InputFilter::SAFEEN,//Webpie_InputFilter::SAFECN, Webpie_InputFilter::SAFESTR
		'length' => array(5, 10)
	),
	'age' => array(
		'required' => 1,
		'msg' => 'please choose your age',
		'expect' => Webpie_InputFilter::INT,
		'range' => array(1, 80),
	),
	'email' => array(
		'required' => 1,
		'msg' => 'email is required',
		'expect' => Webpie_InputFilter::EMAIL,
	),
	'password' => array(
		'required' => 1,
		'msg' => 'password is required',
		'expect' => Webpie_InputFilter::PASSWORD,
		'length' => array(6, 12),
		'equalTo' => 'repassword',
	),
	'repassword' => array(
		'required' => 1,
		'msg' => 'repassword is must equal to password',
		'equalTo' => $_GET['password'],
	),
	'truename' => array(
		'required' => 0,
		'expect' => Webpie_InputFilter::SAFESTR,
		'length' => array(5, 10)
		'msg' => 'truename\'s length must between 5 and 10',
	),
	'desc' => array(
		'required' => 0,
		'default' => 'there is not desc',
		'expect' => Webpie_InputFilter::HTML,
		'length' => array(5, 3000),
		'msg' => 'content\'s length must between 5 and 3000',
	),
	'homepage' => array(
		'required' => 1,
		'expect' => Webpie_InputFilter::URL,
		'msg' => 'url must front be http:// and not empty',
	),
	'zip' => array(
		'required' => 0,
		'expect' => Webpie_InputFilter::ZIP,
	),
	'tel' => array(
		'required' => 0,
		'expect' => Webpie_InputFilter::TEL,
	),
	'phone' => array(
		'required' => 0,
		'expect' => Webpie_InputFilter::PHONE,
	),
	'hobby' => array(
		'required' => 1,
		'msg' => 'at least 3 items',
		'expect' => function($val){return true},
	),
	'birthdate' => array(
		'required' => 1,
		'msg' => 'please choose your birthdate',
		'expect' => Webpie_InputFilter::BIRTHDATE,
	),
);
