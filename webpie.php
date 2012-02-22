<?php
class Webpie
{
	public $envConf = NULL;

	/**
	* @name __constuct
	*
	* @param $conf 用户自定义的配置文件绝对地址
	*
	* @returns   
	*/
	public function __constuct($conf)
	{
		spl_autoload_register(array(__CLASS__, 'autoload'));
		$this->envConf = new Webpie_Config();
		$this->envConf->import($conf);

		//将envConf对象设置为全局可调用
		//setenv($this->envConf);
	}

	public function start()
	{
	}

	/**
	* @name autoload 
	* 自动加载框架的类文件方法
	*
	* @param $class
	*
	* @returns   
	*/
	public function autoload($class = NULL)
	{
		static $classes = NULL;
		$classes = array(
			'webpie_config' => 'config.php',
			'webpie_config_exception' => 'config.php',
			'webpie_handler' => 'handler.php',
			'webpie_handler_exception' => 'handler.php',
			'webpie_model' => 'model.php',
			'webpie_model_exception' => 'model.php',
			'webpie_render' => 'render/render.php',
			'webpie_render_exception' => 'render/render.php',
			'webpie_dal' => 'dal/dal.php',
			'webpie_dal_exception' => 'dal/dal.php',
			'webpie_exception' => 'webpie.php',
		);

		$path = dirname(__FILE__) . '/webpie/';
		$cn = strtolower($class);
		if(isset($classes[$cn]))
			require $path . $classes[$cn];
		else
		{
			$file = $path . str_replace('_', DIRECTORY_SEPARATOR, $cn);
			if(is_file($file))
				require $file;
		}
	}
}

class Webpie_Exception extends Exception{}
