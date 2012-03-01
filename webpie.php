<?php
class Webpie_Exception extends Exception{}

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
		$this->envConf = Webpie_Config::getInstance();
		$this->envConf->import($conf);

		//将envConf对象设置为全局可调用
		putenv("WpConf = $this->envConf");
	}

	/**
	* @name start 应用启动
	*
	* @returns   
	*/
	public function start()
	{
		$reqUri = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '?'));
		$url = $this->envConf->get('url');
		$handler = NULL;
		$handler_hooks = NULL;
		$handlerSuccess = false;
		foreach($url as $u)
		{
			$regx = strtolower($u[0]);

			if($regx[0] != '^')
				$regx = '^' . $regx;

			if($regx[strlen($regx) - 1] != '$')
				$regx = $regx . '$';

			if(preg_match($regx, $reqUri) === True)
			{
				$handlerSuccess = true;
				$handler = $u[1];
				//预置handler的钩子，会在handler初始化时触发
				!empty($u[2]) ? $handlerHooks = $u[2] : '';
				$this->handler($handler, $handlerHooks);
			}
		}

		if($handlerSuccess == false)
		{
			Webpie_Redirect::seeBy404(NULL);
			return false;
		}

		return true;
	}
	
	/**
	* @name handler 
	*
	* @param $handler
	* @param $handlerHooks
	*
	* @returns   
	*/
	public function handler($handler, $handlerHooks = NULL)
	{
		return true;
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
			'webpie_redirect' => 'util/redirect.php',
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
