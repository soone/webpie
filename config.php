<?php
class Webpie_Config_Exception extends Webpie_Exception{}

class Webpie_Config
{
	private $name = 'Webpie';
	private $author = 'soone';
	private $email = 'fengyue15@gmail.com';
	private $ver = '0.1';
	private $date = '2012-02-20';

	private static $instance = NULL;
	private function __construct()
	{}

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

	public function import($conf = NULL)
	{
		return true;
	}

	/**
	* @name get 取得相应的配置属性
	*
	* @param $var 对应属性名称
	*
	* @returns   
	*/
	public function get($var)
	{
		$arrayVar = explode('->', $var);
		if(property_exists($this, $arrayVar[0]))
		{
			if(count($arrayVar) > 1)
			{
				$res = $this->$arrayVar[0];
				unset($arrayVar[0]);
				foreach($arrayVar as $strVar)
				{
					$res = $res[$strVar];
				}

				return $res;
			}
			else
				return $this->$arrayVar[0];
		}
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
					$i + 1 == $vars ? $tempVal[$arrayVar[$i]] = $val : '';
					//$tempVal[$arrayVar[$i]] = (array)$tempVal[$arrayVar[$i]];
					$lastVar = $arrayVar[$i];
				}
				else if(isset((array)$tempVal[$lastVar][$arrayVar[$i]]))
				{
					$i + 1 == $vars ? $tempVal[$lastVar][$arrayVar[$i]] = $val : '';
					$lastVar = $arrayVar[$i];
					//$tempVal[$lastVar][$arrayVar[$i]] = (array)$tempVal[$lastVar][$arrayVar[$i]];
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
			var_dump($tempVal);

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
}
