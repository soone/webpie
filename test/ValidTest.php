<?php
require '../webpie.php';
class ValidTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		new webpie;
	}

	/**
	* @dataProvider source
	*
	* @returns   
	*/
	public function testIsRequired($var, $rule)
	{
		$valid = new Webpie_Valid($var['name'], $rule['name']);
		$this->assertTrue($valid->isRequired());
		$valid = new Webpie_Valid($var['nick'], $rule['nick']);
		$this->assertFalse($valid->isRequired());
		$this->assertEquals($valid->alertMsg, $rule['nick']['msg']);
		$valid = new Webpie_Valid($var['age'], $rule['age']);
		$this->assertTrue($valid->isRequired());
	}

	/**
	* @dataProvider source
	*
	* @returns   
	*/
	public function testValidLength($var, $rule)
	{
		$valid = new Webpie_Valid($var['name'], $rule['name']);
		$this->assertTrue($valid->validLength());
		$valid = new Webpie_Valid($var['nick'], $rule['nick']);
		$this->assertFalse($valid->validLength());
		$this->assertEquals($valid->alertMsg, $rule['nick']['msg']);
		$valid = new Webpie_Valid($var['truename'], $rule['truename']);
		$this->assertTrue($valid->validLength());
		$valid = new Webpie_Valid($var['password1'], $rule['password1']);
		$this->assertFalse($valid->validLength());
	}

	/**
	* @dataProvider source
	*
	* @returns   
	*/
	public function testSetDefault($var, $rule)
	{
		$valid = new Webpie_Valid($var['desc'], $rule['desc']);
		$valid->setDefault();
		$this->assertEquals($valid->validVar, $var['desc']);
		$valid = new Webpie_Valid($var['default1'], $rule['default1']);
		$valid->setDefault();
		$this->assertEquals($valid->validVar, $rule['default1']['default']);
		$valid = new Webpie_Valid($var['default2'], $rule['default2']);
		$valid->setDefault();
		$this->assertEquals($valid->validVar, $rule['default2']['default']);
	}

	/**
	* @dataProvider source
	*
	* @returns   
	*/
	public function testValidRange($var, $rule)
	{
		$valid = new Webpie_Valid($var['age'], $rule['age']);
		$this->assertTrue($valid->validRange());
		$valid = new Webpie_Valid($var['age1'], $rule['age1']);
		$this->assertTrue($valid->validRange());
		$valid = new Webpie_Valid($var['age2'], $rule['age2']);
		$this->assertTrue($valid->validRange());
		$valid = new Webpie_Valid($var['age3'], $rule['age3']);
		$this->assertFalse($valid->validRange());
		$this->assertEquals($valid->alertMsg, $rule['age3']['msg']);
		$valid = new Webpie_Valid($var['age4'], $rule['age4']);
		$this->assertFalse($valid->validRange());
		$this->assertEquals($valid->alertMsg, $rule['age4']['msg']);
	}

	/**
	* @dataProvider source
	*
	* @returns   
	*/
	public function testToApplyExpect($var, $rule)
	{
		$valid = new Webpie_Valid($var['name'], $rule['name']);
		$valid->toApplyExpect();
		$this->assertEquals($valid->validVar, call_user_func($rule['name']['expect'], $var['name']));
		$valid = new Webpie_Valid($var['nick'], $rule['nick']);
		$valid->toApplyExpect();
		$this->assertEquals($valid->validVar, call_user_func($rule['nick']['expect'], $var['nick']));
		$valid = new Webpie_Valid($var['age'], $rule['age']);
		$valid->toApplyExpect();
		$this->assertEquals($valid->validVar, call_user_func($rule['age']['expect'], $var['age']));
		$valid = new Webpie_Valid($var['name1'], $rule['name1']);
		$valid->toApplyExpect();
		$this->assertEquals($valid->validVar, call_user_func($rule['name1']['expect'][1], call_user_func($rule['name1']['expect'][0], $var['name1'])));
		$valid = new Webpie_Valid($var['name2'], $rule['name2']);
		$valid->toApplyExpect();
		$this->assertEquals($valid->validVar, call_user_func($rule['name2']['expect'][1], call_user_func($rule['name2']['expect'][0], $var['name2'])));
	}

	/**
	* @dataProvider source
	*
	* @returns   
	*/
	public function testEqualTo($var, $rule)
	{
		$valid = new Webpie_Valid($var['password'], $rule['password']);
		$this->assertTrue($valid->validEqualTo());
		$valid = new Webpie_Valid($var['repassword'], $rule['repassword']);
		$this->assertTrue($valid->validEqualTo());
		$valid = new Webpie_Valid($var['password1'], $rule['password1']);
		$this->assertFalse($valid->validEqualTo());
	}

	public function source()
	{
		$_GET['repassword'] = '123456';
		$_GET['password'] = '123456';
		$rules = array(
			'name' => array(
				'required' => 1,
				'preExpect' => 'trim',
				'msg' => 'name is have to fill',
				'expect' => 'trim',
				'length' => array(5, 10)
			),
			'nick' => array(
				'required' => 1,
				'msg' => 'it is need to fill',
				'expect' => 'trim',
				'length' => array(5, 16)
			),
			'age' => array(
				'required' => 0,
				'msg' => 'please choose your age',
				'expect' => function($v){return intval(trim($v));},
				'range' => array(1, 80),
			),
			'email' => array(
				'required' => 1,
				'msg' => 'email is required',
				//'expect' => Webpie_Inputvalid::EMAIL,
			),
			'password' => array(
				'required' => 1,
				'msg' => 'password is required',
				//'expect' => Webpie_Inputvalid::PASSWORD,
				'length' => array(6, 12),
				'equalTo' => $_GET['repassword'],
			),
			'repassword' => array(
				'required' => 1,
				'msg' => 'repassword is must equal to password',
				'equalTo' => $_GET['password'],
			),
			'password1' => array(
				'required' => 1,
				'msg' => 'password is required',
				//'expect' => Webpie_Inputvalid::PASSWORD,
				'length' => array(8, 12),
				'equalTo' => $_GET['repassword'],
			),
			'truename' => array(
				'required' => 0,
				//'expect' => Webpie_Inputvalid::SAFESTR,
				'length' => array(5, 10),
				'msg' => 'truename\'s length must between 5 and 10',
			),
			'desc' => array(
				'required' => 0,
				'default' => 'there is not desc',
				//'expect' => Webpie_Inputvalid::HTML,
				'length' => array(5, 3000),
				'msg' => 'content\'s length must between 5 and 3000',
			),
			'homepage' => array(
				'required' => 1,
				//'expect' => Webpie_Inputvalid::URL,
				'msg' => 'url must front be http:// and not empty',
			),
			'zip' => array(
				'required' => 0,
				//'expect' => Webpie_Inputvalid::ZIP,
			),
			'tel' => array(
				'required' => 0,
				//'expect' => Webpie_Inputvalid::TEL,
			),
			'phone' => array(
				'required' => 0,
				//'expect' => Webpie_Inputvalid::PHONE,
			),
			'hobby' => array(
				'required' => 1,
				'msg' => 'at least 3 items',
				'expect' => function($val){return true;},
			),
			'birthdate' => array(
				'required' => 1,
				'msg' => 'please choose your birthdate',
				//'expect' => Webpie_Inputvalid::BIRTHDATE,
			),
			'default1' => array(
				'required' => 0,
				'default' => 'default1',
				'msg' => 'it is need to fill',
			),
			'default2' => array(
				'default' => 'default2',
			),
			'age1' => array(
				'range' => array('1', '80'),
			),
			'age2' => array(
				'range' => array('test', 't2', 4),
			),
			'age3' => array(
				'range' => array('1', '80'),
				'msg' => 'please choose your age',
			),
			'age4' => array(
				'range' => array('test', 't2', 4),
			),
			'name1' => array(
				'required' => 1,
				'msg' => 'name is have to fill',
				//'expect' => function($v){return htmlspecialchars(trim($v));},
				'expect' => array('trim', 'htmlspecialchars'),
				'length' => array(5, 10)
			),
			'name2' => array(
				'required' => 1,
				'msg' => 'name is have to fill',
				'expect' => array(function($v){return trim($v);}, function($v){return addslashes($v);}),
				'length' => array(5, 10)
			),
		);

		$vars = array(
			'name' => 'soone',
			'age' => 21,
			'email' => 'fengyue15@163.com',
			'password' => '123456',
			'repassword' => '123456',
			'truename' => 'soone2adou',
			'desc' => 'fasfsdaf',
			'homepage' => 'http://www.caokee.com',
			'zip' => '361000',
			'tel' => '0592-878-975',
			'phone' => '13774693800',
			'hobby' => array(1, 2, 3, 5, 6, 4),
			'birthdate' => '1990-02-20',
			'password1' => '1234561',
			'default1' => '',
			'age1' => 1,
			'age2' => 'test',
			'age3' => 10,
			'age4' => 't1',
			'name1' => '<script>test</script>',
			'name2' => 'test"addslaish<html>',
		);

		return array(
			array($vars, $rules),
		);
	}
}
