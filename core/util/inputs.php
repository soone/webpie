<?php
class Webpie_Valid
{
	public $required = 0;
	public $msg = NULL;
	public $expect = NULL;
	public $preExpect = NULL;
	public $length = NULL;
	public $range = NULL;
	public $equalTo = NULL;
	public $default = NULL;
	public $validVar = NULL;
	public $alertMsg = NULL;
	public $varType = 2;//string

	/**
	* @name __construct 
	*
	* @param $var
	* @param $rule
	*
	* @returns   
	*/
	public function __construct($var, $rule)
	{
		$this->validVar = $var;
		if(is_array($this->validVar))
			$this->varType = 1;

		array_key_exists('required', $rule) ? $this->required = $rule['required'] : '';
		array_key_exists('length', $rule) ? $this->length = $rule['length'] : '';
		array_key_exists('range', $rule) ? $this->range = $rule['range'] : '';
		array_key_exists('equalTo', $rule) ? $this->equalTo = $rule['equalTo'] : '';
		array_key_exists('msg', $rule) ? $this->msg = $rule['msg'] : '';
		array_key_exists('default', $rule) ? $this->default = $rule['default'] : '';
		array_key_exists('preExpect', $rule) ? $this->preExpect = $this->setExpect($rule['preExpect']) : '';
		array_key_exists('expect', $rule) ? $this->expect = $this->setExpect($rule['expect']) : '';
	}

	protected function setExpect($expect)
	{
		$theExpect = NULL;
		if(is_callable($expect))
			$theExpect = $expect;
		else if(is_array($expect))
		{
			foreach($expect as $exp)
			{
				if(!is_callable($exp))
					throw new Webpie_Util_Exception('format error');
			}

			$theExpect = $expect;
		}
		else
			throw new Webpie_Util_Exception('format error');

		return $theExpect;
	}

	/**
	* @name isRequired 判断是否必须有值
	*
	* @returns   
	*/
	public function isRequired()
	{
		if(in_array($this->required, array(0, 1)))
		{
			if($this->required == 1)
			{
				if(!isset($this->validVar) || !count($this->validVar))
				{
					$this->alertMsg = $this->msg;
					return FALSE;
				}
			}
			
			return TRUE;
		}
		else
			throw new Webpie_Util_Exception('format error');
	}

	/**
	* @name validLength 对字符长度的判断
	*
	* @returns   
	*/
	public function validLength()
	{
		if(count($this->length) == 2)
		{
			$min = intval($this->length[0]);
			$max = intval($this->length[1]);
			$msg = &$this->msg;
			$alertMsg = &$this->alertMsg;
			$lenValid = function($var) use ($min, $max, &$msg, &$alertMsg)
				{
					$varLen = strlen($var);
					if($varLen >= $min && $varLen <= $max)
						return TRUE;
					else
					{
						$alertMsg = $msg;
						return FALSE;
					}
				};

			if($this->varType == 2)
				return $lenValid($this->validVar);
			else
			{
				$mapRes = array_map($lenValid, $this->validVar);
				if(in_array(FALSE, $mapRes))
					return FALSE;

				return TRUE;
			}
		}
		else
			throw new Webpie_Util_Exception('format error');
	}

	/**
	* @name setDefault 对默认值进行设置
	*
	* @returns   
	*/
	public function setDefault()
	{
		if($this->required == 0 && empty($this->validVar))
			$this->validVar = $this->default;
	}

	/**
	* @name validRange 对取值范围的判断
	*
	* @returns   
	*/
	public function validRange()
	{
		if(!is_array($this->range) || !($rLen = count($this->range)))
			throw new Webpie_Util_Exception('format error');

		$msg = &$this->msg;
		$alertMsg = &$this->alertMsg;
		$range = &$this->range;
		$rangeValid = function($var, $flag) use (&$range, &$msg, &$alertMsg)
			{
				$res = FALSE;
				if($flag == 1)
				{
					$var = intval($var);
					if($var > $range[0] && $var <= $range[1])
						$res = TRUE;
				}
				else
				{
					if(in_array($var, $range))
						$res = TRUE;
				}

				if(!$res)
					$alertMsg = $msg;

				return $res;
			};

		if($this->varType == 2)
		{
			if($rLen == 2 && is_int($this->range[0]) && is_int($this->range[1]))
				return $rangeValid($this->validVar, 1);
			else
				return $rangeValid($this->validVar, 2);
		}
		else
		{
			$mapRes = array_map($rangeValid, $this->validVar);
			if(in_array(FALSE, $mapRes))
				return FALSE;
			else
				return TRUE;
		}
	}

	/**
	* @name toAppExpect 应用对数据的处理
	*
	* @param $expect
	*
	* @returns   
	*/
	public function toApplyExpect($expect)
	{
		if(is_array($expect))
		{
			$var = &$this->validVar;
			$mapFunc = function($func) use (&$var) {$var = call_user_func($func, $var);return $var;};
			$mapRes = array_map($mapFunc, $expect);
			if(in_array(FALSE, $mapRes, TRUE))
			{
				$this->alertMsg = $this->msg;
				return FALSE;
			}
			//return array_walk($expect, function($func, $key) use (&$var) {$var = call_user_func($func, $var);});
		}
		else if($expect)
		{
			$res = call_user_func($expect, $this->validVar);
			if($res === FALSE)
			{
				$this->alertMsg = $this->msg;
				return FALSE;
			}

			$this->validVar = $res;
		}

		return TRUE;
	}

	/**
	* @name validEqualTo 对比值
	*
	* @returns   
	*/
	public function validEqualTo()
	{
		if($this->equalTo == NULL || $this->validVar == $this->equalTo)
			return TRUE;

		return FALSE;
	}

	/**
	* @name toValid 
	*
	* @returns   
	*/
	public function toValid()
	{
		if($this->isRequired() === FALSE)
			return FALSE;

		if($this->preExpect && $this->toApplyExpect($this->preExpect) === FALSE)
			return FALSE;

		if($this->length && $this->validLength() === FALSE)
			return FALSE;

		if($this->equalTo && $this->validEqualTo() === FALSE)
			return FALSE;

		if($this->range && $this->validRange() === FALSE)
			return FALSE;

		if($this->expect && $this->toApplyExpect($this->expect) === FALSE)
			return FALSE;

		$this->setDefault();
		return TRUE;
	}

	/**
	* @name getValidVar 
	*
	* @returns   
	*/
	public function getValidVar()
	{
		return $this->validVar;
	}

	/**
	* @name getMsg 
	*
	* @returns   
	*/
	public function getMsg()
	{
		return $this->alertMsg;
	}
}

class Webpie_Inputs
{
	protected $inputsType = array('Get', 'Post', 'Request', 'Cookie', 'Server', 'Env', 'Session');
	protected $gRedirect = NULL;
	protected $gInputs = NULL;
	protected $sError = NULL;
	protected $sRedirect = NULL;

	public function __construct(){}
	public function inputsValid($var)
	{
		$this->gRedirect = $this->getRedirect($var);
		foreach($var as $key => $val)
		{
			if(!in_array($key, $this->inputsType))
				continue;

			$func = 'inputs' . $key;
			$this->$key = $val;
			$this->$func();
		}

		return $this->inputsHandler();
	}

	public function inputsGet()
	{
		if(empty($this->Get)) return NULL;
		return $this->inputsFilter($_GET, $this->Get, 'Get');
	}

	public function inputsPost()
	{
		if(empty($this->Post)) return NULL;
		return $this->inputsFilter($_POST, $this->Post, 'Post');
	}

	public function inputsRequest()
	{
		if(empty($this->Request)) return NULL;
		return $this->inputsFilter($_REQUEST, $this->Request, 'Request');
	}

	public function inputsCookie()
	{
		if(empty($this->Cookie)) return NULL;
		return $this->inputsFilter($_COOKIE, $this->Cookie, 'Cookie');
	}

	public function inputsServer()
	{
		if(empty($this->Server)) return NULL;
		return $this->inputsFilter($_SERVER, $this->Server, 'Server');
	}

	public function inputsEnv()
	{
		if(empty($this->Env)) return NULL;
		return $this->inputsFilter($_ENV, $this->Env, 'Env');
	}

	public function inputsSession()
	{
		if(empty($this->Session)) return NULL;
		return $this->inputsFilter($_SESSION, $this->Session, 'Session');
	}

	public function inputsFilter($var, $rules, $varName)
	{
		$this->sRedirect = $this->getRedirect($rules);
		foreach($rules as $key => $val)
		{
			empty($var[$key]) ? $var[$key] = NULL : '';
			$vObj = new Webpie_Valid($var[$key], $val);
			if($vObj->toValid() === TRUE)
				$this->gInputs[$varName][$key] = $vObj->getValidVar();
			else
				$this->sError[] = $vObj->getMsg();
		}

		return $this->inputsHandler($varName);
	}

	protected function getRedirect(&$var)
	{
		$redirect = NULL;
		if(array_key_exists('__webpieRedirect', $var) && !empty($var['__webpieRedirect']))
		{
			$redirect = $var['__webpieRedirect'];
			unset($var['__webpieRedirect']);
		}

		return $redirect;
	}

	protected function inputsHandler($varName = NULL)
	{
		if($varName)
		{
			$redirect = $this->sRedirect;
			$res = $this->gInputs[$varName];
		}
		else
		{
			$redirect = $this->gRedirect;
			$res = $this->gInputs;
		}

		if($redirect && count($this->sError) > 0)
			Webpie_Redirect::see('200', $redirect, $this->sError);
		else
			return $res;
	}

	public static function validEmail($var)
	{
		return filter_var($var, FILTER_VALIDATE_EMAIL);
	}

	public static function validIp($var)
	{
		return filter_var($var, FILTER_VALIDATE_IP);
	}

	public static function validUrl($var)
	{
		return filter_var($var, FILTER_VALIDATE_URL);
	}

	public static function validUrlQuery($var)
	{
		return filter_var($var, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED);
	}

	public static function validUrlPath($var)
	{
		return filter_var($var, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
	}

	public static function validDate($var, $format = 'Y-m-d')
	{
		$sTime = strtotime($var);
		if($sTime === FALSE)
			return FALSE;

		if(date($format, strtotime($var)) == $var)
			return $var;
		else
			return FALSE;
	}

	public static function validCnZip($var)
	{
		return preg_match('/^[\d]{6}$/', $var) ? $var : FALSE;
	}

	public static function validCnPhone($var)
	{
		return preg_match('/^1[3|4|5|8][0-9]{9}$/', $var) ? $var : FALSE;
	}

	public static function validCnTel($var)
	{
		return preg_match('/^((\+)?(0)?86(-)?)?([\d]{3,4}(-)?)?[\d]{7,8}$/', $var) ? $var : FALSE;
	}

	public static function validCardByLuhm($var)
	{
		$cLen = strlen($var);
		if(!in_array($cLen, array(16, 19)))
			return FALSE;

		$i = 0;
		$eCo = 0;
		$aNum = 0;
		$leftNum = 0;
		for($i = 0; $i < $cLen-1; $i++)
		{
			if($i%2 == 0)
			{
				$aNum = $var[$i]*2;
				if($aNum >= 10)
				{
					$temp = strval($aNum);
					$aNum = $temp[0] + $temp[1];
				}
			}
			else
				$aNum = intval($var[$i]);

			$eCo += $aNum;
		}

		$leftNum = $eCo%10;
		if($leftNum != 0)
			$endNum = 10 - $leftNum;
		else
			$endNum = 0;

		if($endNum != $var[$cLen-1])
			return FALSE;
		else
			return $var;
	}

	public static function validCnId($var){}

	public static function validCnIdStrict($var)
	{
		
	}

	public static function validComPass($var, $length = array(6, 20))
	{
		return preg_match('/^[\d\w_\-\$!#%\^&\*\(\)\+<>\?\[\]]{' . $length[0] . ',' . $length[1] . '}$/', $var) ? $var : FALSE;
	}

	/**
	* @name validComUser 允许中文、英文、数字以及_和-字符，一个中文按照2个字符计算
	*
	* @param $var
	* @param $length
	*
	* @returns   
	*/
	public static function validComUser($var, $length = array(5, 20))
	{
		$gbkLen = Webpie_Inputs::stringLen($var);
		$utf8Len = Webpie_Inputs::stringLen($var, 1);
		if($utf8Len > $gbkLen)
			$length[1] = $length[1] + $utf8Len - $gbkLen;

		return preg_match('/^([\d\w_\-\x80-\xff]{' . $length[0] . ',' . $length[1] . '})?$/si', $var) ? $var : FALSE;
	}

	/**
	* @name stringLen 
	*
	* @param $var
	* @param $type 3-gbk(1个中文2个字符) 1-utf8(1个中文3个字符) 2-忽略中文字节长度
	*
	* @returns   
	*/
	public static function stringLen($var, $type = 3)
	{
		$i = 0;
		$count = 0;
		$len = strlen($var);
		while($i < $len)
		{
			$chr = ord($var[$i]);
			$count++;
			$i++;
			if($i > $len)
				break;

			if($chr & 0x80)
			{
				$chr <<= 1;
				while($chr & 0x80)
				{
					$i++;
					$chr <<= 1;
				}

				if($type == 3)
					$count++;
				else if($type == 1)
					$count += 2;
			}
		}

		return $count;
	}
}
