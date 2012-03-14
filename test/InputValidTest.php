<?php
require '../webpie.php';
class InputFilterTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		new webpie;
	}

	public function test__()
	{
		$iv = new Webpie_InputFilter;
		$iv->filter($vars, $rules);
		$iv->filterFile($vars, $rules);
		$iv->filterEmail($vars);
		$iv->filterInt($vars);
		$iv->filterFloat($vars);
		$iv->filterStr($vars, $rules);
		$iv->filterUrl($vars, $rules);
		$iv->filterDate($vars, $rules);
		$iv->filterZip($vars, $rules);
		$iv->filterIp($vars, $rules);
		$iv->filterTel($vars, $rules);
		$iv->filterPhone($vars, $rules);
		$iv->filterArray($vars, $rules);
		$iv->filterIsTrue($vars, $rules);
	}
}

$vars = array('name' => 'soone',
				'age' => 10,
				'email' => 'fengyue15@15c.com',
				'password' => '123456',
				'repassword' => '123456',
				'truename' => '中文',
				'desc' => '发的所发第三发的撒肥,fdsafdsa',
				'homepage' => 'http://www.webpie.com',
				'zip' => '361000',
				'tel' => '+860694-8780987',
				'phone' => '18611740380',
				'hobby' => array(5, 6, 7, 8, 9),
				'birthdate' => '1990-09-21',
);

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
