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
				if(empty($this->validVar) || !count($this->validVar))
				{
					$this->alertMsg = $this->msg;
					return false;
				}
			}
			
			return true;
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
						return true;
					else
					{
						$alertMsg = $msg;
						return false;
					}
				};

			if($this->varType == 2)
				return $lenValid($this->validVar);
			else
			{
				$mapRes = array_map($lenValid, $this->validVar);
				if(in_array(false, $mapRes))
					return false;

				return true;
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
				$res = false;
				if($flag == 1)
				{
					$var = intval($var);
					if($var > $range[0] && $var <= $range[1])
						$res = true;
				}
				else
				{
					if(in_array($var, $range))
						$res = true;
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
			if(in_array(false, $mapRes))
				return false;
			else
				return true;
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
			return array_walk($expect, function($func, $key) use (&$var) {$var = call_user_func($func, $var);});
		}
		else if($expect)
			$this->validVar = call_user_func($expect, $this->validVar);

		return true;
	}

	/**
	* @name validEqualTo 对比值
	*
	* @returns   
	*/
	public function validEqualTo()
	{
		if($this->equalTo == NULL || $this->validVar == $this->equalTo)
			return true;

		return false;
	}

	/**
	* @name toValid 
	*
	* @returns   
	*/
	public function toValid()
	{
		if($this->isRequired() === false)
			return false;

		if($this->preExpect && $this->toApplyExpect($this->preExpect) === false)
			return false;

		if($this->length && $this->validLength() === false)
			return false;

		if($this->equalTo && $this->validEqualTo() === false)
			return false;

		if($this->range && $this->validRange() === false)
			return false;

		if($this->expect && $this->toApplyExpect($this->expect) === false)
			return false;

		$this->setDefault();
		return true;
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

class Webpie_Inputvalid
{
	const EMAIL = 1;

	public function __construct(){}
}
