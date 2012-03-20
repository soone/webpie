<?php
require '../webpie.php';
class InputsTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		new webpie;
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		date_default_timezone_set('Asia/Chongqing');
	}

	/**
	* @dataProvider emailPro 
	*
	* @returns   
	*/
	public function testValidEmail($email, $res)
	{
		$this->assertEquals(Webpie_Inputs::validEmail($email), $res);
	}

	public function emailPro()
	{
		return array(
			array('fengyue15@gmail.com', 'fengyue15@gmail.com'),
			array('1816772@qq.com', '1816772@qq.com'),
			array('test_1@163.com', 'test_1@163.com'),
			array('15@gmail.com', '15@gmail.com'),
			array('_-*faf@gmail.com', '_-*faf@gmail.com'),
			array('5@g.c', '5@g.c'),
			array(';:*faf@gmail.com', false),
			array('15gmail.com', false),
		);
	}

	/**
	* @dataProvider ipPro 
	*
	* @returns   
	*/
	public function testValidIp($ip, $res)
	{
		$this->assertEquals(Webpie_Inputs::validIp($ip), $res);
	}

	public function ipPro()
	{
		return array(
			array('192.168.1.102', '192.168.1.102'),
			array('192.168.1.258', false),
			array('202.144.23.56', '202.144.23.56'),
			array('test', false),
		);
	}

	/**
	* @dataProvider urlPro 
	*
	* @returns   
	*/
	public function testValidUrl($url, $res)
	{
		$this->assertEquals(Webpie_Inputs::validUrl($url), $res);
	}

	public function urlPro()
	{
		return array(
			array('http://www.caokee.com', 'http://www.caokee.com'),
			array('www.caokee.com', false),
			array('http://www.caokee.com/index/', 'http://www.caokee.com/index/'),
			array('http://127.0.0.1', 'http://127.0.0.1'),
			array('http://', false),
		);
	}

	/**
	* @dataProvider urlPathPro 
	*
	* @returns   
	*/
	public function testValidUrlPath($url, $res)
	{
		$this->assertEquals(Webpie_Inputs::validUrlPath($url), $res);
	}

	public function urlPathPro()
	{
		return array(
			array('http://www.caokee.com/test', 'http://www.caokee.com/test'),
			array('www.caokee.com/test', false),
			array('http://www.caokee.com', false),
			array('http://127.0.0.1/test', 'http://127.0.0.1/test'),
			array('http://2789.0.0.1/test', 'http://2789.0.0.1/test'),
		);
	}

	/**
	* @dataProvider urlQueryPro 
	*
	* @returns   
	*/
	public function testValidUrlQuery($url, $res)
	{
		$this->assertEquals(Webpie_Inputs::validUrlQuery($url), $res);
	}

	public function urlQueryPro()
	{
		return array(
			array('http://www.caokee.com/index', false),
			array('http://www.caokee.com/?index', 'http://www.caokee.com/?index'),
			array('http://www.caokee.com/?index=test&h1=2', 'http://www.caokee.com/?index=test&h1=2'),
			array('www.caokee.com/?index=test&h1=2', false),
		);
	}

	/**
	* @dataProvider datePro 
	*
	* @returns   
	*/
	public function testValidDate($date, $format, $res)
	{
		$this->assertEquals(Webpie_Inputs::validDate($date, $format), $res);
	}

	public function datePro()
	{
		return array(
			array('2010-03-21', 'Y-m-d', '2010-03-21'),
			array('2010-03-21 12:11:15', 'Y-m-d H:i:s', '2010-03-21 12:11:15'),
			array('03/21/2010', 'm/d/Y', '03/21/2010'),
			array('21/03/2010', 'd/m/Y', false),
			array('12:37:50 03/21/2010', 'H:i:s m/d/Y', '12:37:50 03/21/2010'),
		);
	}

	/**
	* @dataProvider zipPro 
	*
	* @returns   
	*/
	public function testValidCnZip($zip, $res)
	{
		$this->assertEquals(Webpie_Inputs::validCnZip($zip), $res);
	}

	public function zipPro()
	{
		return array(
			array('361000', '361000'),
			array('3610001', false),
			array('testet', false),
			array('361001', '361001'),
		);
	}

	/**
	* @dataProvider phonePro 
	*
	* @returns   
	*/
	public function testValidCnPhone($phone, $res)
	{
		$this->assertEquals(Webpie_Inputs::validCnPhone($phone), $res);
	}

	public function phonePro()
	{
		return array(
			array('158361000', false),
			array('15860745961', '15860745961'),
			array('18611740290', '18611740290'),
			array('13778979787', '13778979787'),
			array('14778979787', '14778979787'),
			array('17778979787', false),
			array('158607459611', false),
			array('186117402910', false),
			array('137789797817', false),
			array('fdasdf9834d', false)
		);
	}

	/**
	* @dataProvider telPro
	*
	* @returns   
	*/
	public function testValidCnTel($tel, $res)
	{
		$this->assertEquals(Webpie_Inputs::validCnTel($tel), $res);
	}

	public function telPro()
	{
		return array(
			array('8780975', '8780975'),
			array('87809751', '87809751'),
			array('05928780975', '05928780975'),
			array('059287809751', '059287809751'),
			array('0592-8780975', '0592-8780975'),
			array('0592-87809751', '0592-87809751'),
			array('8605928780975', '8605928780975'),
			array('86059287809751', '86059287809751'),
			array('86-0592-8780975', '86-0592-8780975'),
			array('86-0592-87809751', '86-0592-87809751'),
			array('08605928780975', '08605928780975'),
			array('086059287809751', '086059287809751'),
			array('086-0592-8780975', '086-0592-8780975'),
			array('086-0592-87809751', '086-0592-87809751'),
			array('878975', false),
			array('8780975x', false),
			array('878097x', false),
			array('0592-880975', false),
			array('0592-87809x1', false),
		);
	}

	/**
	* @dataProvider cardPro
	*
	* @returns   
	*/
	public function testValidvalidCardByLuhm($card, $res)
	{
		$this->assertEquals(Webpie_Inputs::validCardByLuhm($card), $res);
	}

	public function cardPro()
	{
		return array(
			array('6226220102460341', '6226220102460341'),
			array('6226220102460342', false),
		);
	}

	public function testValidCnId(){}
	public function testValidCnIdStrict(){}

	/**
	* @dataProvider passPro
	*
	* @returns   
	*/
	public function testValidComPass($pass, $res)
	{
		$this->assertEquals(Webpie_Inputs::validComPass($pass), $res);
	}

	public function passPro()
	{
		return array(
			array('he]llo201^y%e2', 'he]llo201^y%e2'),
			array('h;ello201^y%e2', false),
			array('123456', '123456'),
		);
	}

	/**
	* @dataProvider userPro
	*
	* @returns   
	*/
	public function testValidComUser($user, $res)
	{
		$this->assertEquals(Webpie_Inputs::validComUser($user), $res);
	}

	public function userPro()
	{
		return array(
			array('我叫今你三顺', '我叫今你三顺'),
			array('soone', 'soone'),
			array('123456', '123456'),
			array('我叫soone', '我叫soone'),
			array('我叫soone2adou', '我叫soone2adou'),
			array('我叫soone2-adou', '我叫soone2-adou'),
			array('我叫soon_e2-adou', '我叫soon_e2-adou'),
			array('我叫我叫今你三顺soon', '我叫我叫今你三顺soon'),
			array('我叫s\'oon_e2-adou', false),
			array('我叫我叫今你三顺soon_e2-adou', false),
		);
	}

	/**
	* @dataProvider stringPro
	*
	* @returns   
	*/
	public function testStringLen($str, $type, $res)
	{
		$this->assertEquals(Webpie_Inputs::stringLen($str, $type), $res);
	}

	public function stringPro()
	{
		return array(
			array('我叫今你三顺', 3, 12),
			array('soone', 1, 5),
			array('soone', 2, 5),
			array('soone', 3, 5),
			array('123456soone', 1, 11),
			array('123456soone', 2, 11),
			array('123456soone', 3, 11),
			array('我叫soone', 1, 11),
			array('我叫soone', 2, 7),
			array('我叫soone', 3, 9),
			array('我叫soone123', 1, 14),
			array('我叫soone123', 2, 10),
			array('我叫soone123', 3, 12),
		);
	}

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

//$rules = array(
//	'name' => array(
//		'required' => 1,
//		'msg' => 'name is have to fill',
//		'expect' => Webpie_InputFilter::SAFEEN,//Webpie_InputFilter::SAFECN, Webpie_InputFilter::SAFESTR
//		'length' => array(5, 10)
//	),
//	'age' => array(
//		'required' => 1,
//		'msg' => 'please choose your age',
//		'expect' => Webpie_InputFilter::INT,
//		'range' => array(1, 80),
//	),
//	'email' => array(
//		'required' => 1,
//		'msg' => 'email is required',
//		'expect' => Webpie_InputFilter::EMAIL,
//	),
//	'password' => array(
//		'required' => 1,
//		'msg' => 'password is required',
//		'expect' => Webpie_InputFilter::PASSWORD,
//		'length' => array(6, 12),
//		'equalTo' => 'repassword',
//	),
//	'repassword' => array(
//		'required' => 1,
//		'msg' => 'repassword is must equal to password',
//		'equalTo' => $_GET['password'],
//	),
//	'truename' => array(
//		'required' => 0,
//		'expect' => Webpie_InputFilter::SAFESTR,
//		'length' => array(5, 10)
//		'msg' => 'truename\'s length must between 5 and 10',
//	),
//	'desc' => array(
//		'required' => 0,
//		'default' => 'there is not desc',
//		'expect' => Webpie_InputFilter::HTML,
//		'length' => array(5, 3000),
//		'msg' => 'content\'s length must between 5 and 3000',
//	),
//	'homepage' => array(
//		'required' => 1,
//		'expect' => Webpie_InputFilter::URL,
//		'msg' => 'url must front be http:// and not empty',
//	),
//	'zip' => array(
//		'required' => 0,
//		'expect' => Webpie_InputFilter::ZIP,
//	),
//	'tel' => array(
//		'required' => 0,
//		'expect' => Webpie_InputFilter::TEL,
//	),
//	'phone' => array(
//		'required' => 0,
//		'expect' => Webpie_InputFilter::PHONE,
//	),
//	'hobby' => array(
//		'required' => 1,
//		'msg' => 'at least 3 items',
//		'expect' => function($val){return true},
//	),
//	'birthdate' => array(
//		'required' => 1,
//		'msg' => 'please choose your birthdate',
//		'expect' => Webpie_InputFilter::BIRTHDATE,
//	),
//);
