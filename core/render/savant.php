<?php
class Webpie_Render_Savant extends Webpie_Render_Interface
{
	public $render = NULL;
	public function __construct($setting = array())
	{
		require_once file_exists(__DIR__ . DS . 'savant3' . DS . 'Savant3.php') ?  __DIR__ . DS . 'savant3' . DS . 'Savant3.php' : 'Savant3.php';
		$this->render = new Savant3;
		foreach($setting as $key => $val)
		{
			$this->render->$key = $val;
		}
	}

	/**
	* @name display 直接显示输出模板
	*
	* @returns   
	*/
	public function display()
	{
		if(func_num_args() < 1)
			throw new Webpie_Render_Exception('至少需要一个参数，即模板名称');

		$args = func_get_args();
		$res = $this->toAssign($args);

		return $this->render->display($res);
	}

	/**
	* @name fetch 返回模板内容作为字符串
	*
	* @returns   
	*/
	public function fetch()
	{
		if(func_num_args() < 1)
			throw new Webpie_Render_Exception('至少需要一个参数，即模板名称');

		$args = func_get_args();
		$res = $this->toAssign($args);

		return $this->render->fetch($res);
	}

	/**
	* @name toAssign 对动态参数的处理和赋值
	*
	* @param $args
	*
	* @returns   
	*/
	protected function toAssign($args)
	{
		if(!empty($args[1]))
			$this->render->assign($args[1]);

		return $args[0];
	}

	/**
	* @name get 
	*
	* @param $var
	*
	* @returns   
	*/
	public function get($var)
	{
		if(!property_exists(__CLASS__, $var))
			return $this->savant->$var;
		else
			return $this->$var;
	}

	/**
	* @name set 
	*
	* @param $var
	* @param $val
	*
	* @returns   
	*/
	public function set($var, $val)
	{
		if(!property_exists(__CLASS__, $var))
			$this->savant->$var = $val;
		else
			$this->$var = $val;
	}

	/**
	 * @name __call 
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return 
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->savant, $name), $arguments);
	}
}
