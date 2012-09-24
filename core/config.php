<?php
class Webpie_Config_Exception extends Webpie_Exception{}

class Webpie_Config
{
	private $name = 'Webpie';
	private $author = 'soone';
	private $email = 'fengyue15@gmail.com';
	private $ver = '0.1';
	private $date = '2012-02-20';
	private $debug = true;
	public $custom = array();
	public $wpRoot = __DIR__;
	public $log;
	public $logStage = "\n";

	private static $instance = NULL;
	private function __construct()
	{
		$this->log = $this->wpRoot . DS . 'logs' . DS . 'error.log';
	}

	/**
	* @name getInstance 单件模式返回config对象
	*
	* @returns   
	*/
	public static function getInstance()
	{
		if(self::$instance === NULL)
			self::$instance = new Webpie_Config;

		return self::$instance;
	}

	/**
	* @name import 用于配置文件的导入
	*
	* @param $conf 可以是导入的数组或者是配置文件的绝对地址
	*
	* @returns   
	*/
	public function import($conf = NULL)
	{
		if(is_array($conf))
		{
			$this->custom = array_merge($this->custom, $conf);
		}
		else if(is_file($conf))
		{
			$confCtx = require($conf);
			$this->custom = array_merge($this->custom, $confCtx);
		}
		else
			return false;

		return true;
	}

	/**
	* @name get 取得相应的配置属性
	*
	* @param $var 对应属性名称
	* @param $val 该参数有值，则当var属性不存在的时候自动返回该参数值
	*
	* @returns   
	*/
	public function get($var, $val = NULL)
	{
		$arrayVar = explode('->', $var);
		if(property_exists($this, $arrayVar[0]) || array_key_exists($arrayVar[0], $this->custom))
		{
			//优先判断用户的配置
			$res = array_key_exists($arrayVar[0], $this->custom) ? $this->custom[$arrayVar[0]] : $this->$arrayVar[0];
			if(count($arrayVar) > 1)
			{
				unset($arrayVar[0]);
				foreach($arrayVar as $strVar)
				{
					if(!isset($res[$strVar]))
					{
						if($val !== NULL)
						{
							$res = $val;
							break;
						}
						else
							throw new Webpie_Config_Exception('配置属性不存在');
					}
					$res = $res[$strVar];
				}
			}

			return $res;
		}
		else if($val !== NULL)
			return $val;
		else
			throw new Webpie_Config_Exception('配置属性不存在');
	}

	/**
	* @name set 设置配置属性
	*
	* @param $var
	* @param $val
	*
	* @returns   
	*/
	public function set($var, $val)
	{
		$arrayVar = explode('->', $var);
		$vars = count($arrayVar);
		if($vars == 1) return $this->$var = $val;
		if(property_exists($this, $arrayVar[0]))
		{
			$tempVal = $this->$arrayVar[0];
			$lastVar = NULL;
			for($i = 1; $i < $vars; $i++)
			{
				if(isset($tempVal[$arrayVar[$i]]) && $lastVar == NULL)
				{
					if(!is_array($tempVal[$arrayVar[$i]]))
						$tempVal[$arrayVar[$i]] = array();
					$i + 1 == $vars ? $tempVal[$arrayVar[$i]] = $val : '';
					$lastVar = $arrayVar[$i];
				}
				else if(isset($tempVal[$lastVar][$arrayVar[$i]]))
				{
					if(!is_array($tempVal[$lastVar][$arrayVar[$i]]))
						$tempVal[$lastVar][$arrayVar[$i]] = array();
					$i + 1 == $vars ? $tempVal[$lastVar][$arrayVar[$i]] = $val : '';
					$lastVar = $arrayVar[$i];
				}
				else if($lastVar == NULL)
				{
					$tempVal[$arrayVar[$i]] = $i + 1 == $vars ? $val : array();
					$lastVar = $arrayVar[$i];
				}
				else
				{
					$tempVal[$lastVar][$arrayVar[$i]] = $i + 1 == $vars ? $val : array();
					$lastVar = $arrayVar[$i];
				}
			}

			$this->$arrayVar[0] = $tempVal;
		}
		else
		{
			$tempVal = array($arrayVar[$vars - 1] => $val);
			for($j = $vars, $i = $j - 2; $i > 0; $i--)
			{
				$tempVal = array($arrayVar[$i] => $tempVal);
			}

			$this->$arrayVar[0] = $tempVal;
		}

		return true;
	}

	/**
	* @name getReqWith 判断请求是否是ajax
	*
	* @returns   
	*/
	public function getReqWith()
	{
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			$this->reqWithAjax = true;
		else
			$this->reqWithAjax = false;

		return $this->reqWithAjax;
	}
}
