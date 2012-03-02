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
	public function __construct($conf = NULL)
	{
		spl_autoload_register(array(__CLASS__, 'autoload'));
		//set_error_handler();
		//set_exception_handler();
		//get_class();
		$this->envConf = Webpie_Config::getInstance();
		$this->envConf->import($conf);

		//将envConf对象设置为全局可调用
		$_ENV['WpConf'] = $this->envConf;
		//putenv("WpConf = $this->envConf");
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
		//事先处理钩子
		$hooks = $this->envConf->get('hooks', '');//是否存在全局的钩子
		if($hooks && $handlerHooks)
			$handlerHooks = array_merge((array)$hooks, (array)$handlerHooks);
		else if($hooks)
			$handlerHooks = $hooks;

		if($handlerHooks)
		{
			$handlerHooks = (array)$handlerHooks;
			foreach($handlerHooks as $hook)
			{
				if(empty($hook)) continue;

				if(is_array($hook))
				{
					$hookCls = $hook[0];
					$hookVar = empty($hook[1]) ? NULL : $hook[1];
				}
				else
				{
					$hookCls = $hook;
					$hookVar = NULL;
				}

				$hookObj = new $hookCls();
				$hookObj->setup($hookVar);
			}
		}

		if(strpos($handler, '->') !== false)//动态调用触发器
		{
			$handlers = explode('->', $handler);
			$this->envConf->set('router', array('control' => $handlers[0], 'action' => $handlers[1]));
			$objHandler = new $handlers[0];
			return $objHandler->$handlers[1]();
		}
		else if(strpos($handler, '::') !== false)//静态调用触发器
		{
			$handlers = explode('::', $handler);
			$this->envConf->set('router', array('control' => $handlers[0], 'action' => $handlers[1], 'type' => 1));
			return $handlers[0]::$handlers[1]();
		}
		else
			throw new Webpie_Exception('无法调用对应触发器');
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

		$cn = strtolower($class);
		if(isset($classes[$cn]))
			require dirname(__FILE__) . DIRECTORY_SEPARATOR . $classes[$cn];
		else
		{
			$file = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $cn) . '.php';
			if(is_file($file))
				require $file;
			else
			{
				$oriFile = $this->envConf->get('projectRoot') . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
				if(is_file($oriFile))
					require $oriFile;
			}
		}
	}
}
