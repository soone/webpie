<?php
define('DS', DIRECTORY_SEPARATOR);

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
        $this->envConf = Webpie_Config::getInstance();
        $this->envConf->import($conf);

        set_error_handler(array(__CLASS__, 'errorHandler'));
        set_exception_handler(array(__CLASS__, 'exceptionHandler'));

        //将envConf对象设置为全局可调用
        $_ENV['envConf'] = $this->envConf;
    }

    /**
    * @name start 应用启动
    *
    * @returns   
    */
    public function start()
    {
		$uriPos = strpos($_SERVER['REQUEST_URI'], '?');
        $reqUri = $uriPos ? substr($_SERVER['REQUEST_URI'], 0, $uriPos) : substr($_SERVER['REQUEST_URI'], 0);
        $url = $this->envConf->get('url');
        $handler = NULL;
        $handlerHooks = NULL;
        $handlerSuccess = false;
        foreach($url as $u)
        {
			$matches = array();
            $regx = strtolower($u[0]);

            if($regx[0] != '^')
                $regx = '^' . $regx;

            if($regx[strlen($regx) - 1] != '$')
                $regx = $regx . '$';

            if(preg_match('/' . $regx . '/i', $reqUri, $matches) > 0)
            {
                $handlerSuccess = true;
                $handler = $u[1];
                //预置handler的钩子，会在handler初始化时触发
                !empty($u[2]) ? $handlerHooks = $u[2] : '';
				if(count($matches) > 1)
				{
					unset($matches[0]);
					$this->envConf->set('routerPars', $matches);
				}
                return $this->handler($handler, $handlerHooks);
            }
        }

        if($handlerSuccess == false)
        {
            Webpie_Redirect::seeBy404($this->envConf->get('go404', NULL));
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
        if(class_exists($class))
            return true;

        static $classes = NULL;
        $classes = array(
            'webpie_config' => 'config.php',
            'webpie_config_exception' => 'config.php',
            'webpie_handler' => 'handler.php',
            'webpie_handler_exception' => 'handler.php',
            'webpie_model' => 'model.php',
            'webpie_model_exception' => 'model.php',
			'webpie_module' => 'module.php',
			'webpie_module_exception' => 'module.php',
            'webpie_render_smarty' => 'render/render.php',
            'webpie_render_savant' => 'render/savant.php',
            'webpie_render_exception' => 'render/exception.php',
            'webpie_render_interface' => 'render/interface.php',
            'webpie_dal' => 'dal/dal.php',
            'webpie_dal_exception' => 'dal/dal.php',
            'webpie_dal_dbinterface' => 'dal/dbinterface.php',
            'webpie_dal_mysql' => 'dal/mysql.php',
            'webpie_dal_cacheabstract' => 'dal/cacheabstract.php',
            'webpie_dal_memcache' => 'dal/memcache.php',
            'webpie_dal_redis' => 'dal/redis.php',
            'webpie_exception' => 'webpie.php',
			'webpie_helper' => 'util/helper.php',
            'webpie_util_exception' => 'util/exception.php',
            'webpie_redirect' => 'util/redirect.php',
            'webpie_logs' => 'util/logs.php',
            'webpie_captcha' => 'util/captcha/captcha.php',
            'webpie_inputs' => 'util/inputs.php',
            'webpie_valid' => 'util/inputs.php',
			'webpie_hook' => 'util/hook/hook.php',
			'webpie_hook_interface' => 'util/hook/interface.php',
			'webpie_hook_exception' => 'util/hook/hook.php',
        );

        global $classMap;
        if(is_array($classMap) && count($classMap) > 0)
            $classes = array_merge($classes, $classMap);

        $cn = strtolower($class);
        if(isset($classes[$cn]))
        {
            require_once ((strpos($cn, 'webpie') !== False) ? dirname(__FILE__) . DS : $this->envConf->get('projectRoot')) . $classes[$cn];
        }
        else
        {
			$oriFile = $this->envConf->get('projectRoot', './') . implode(DS, array_map(function($v){return strtolower($v);}, explode('_', $class))) . '.php';
            if(is_file($oriFile))
                require_once $oriFile;
        }
    }

    /**
    * @name errorHandler 
    *
    * @param $errno
    * @param $errstr
    * @param $errfile
    * @param $errline
    *
    * @returns   
    */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if(!(error_reporting() & $errno)) return;
        $fmtMsg = '%s:[%d] %s in file %s on line %d' . $_ENV['envConf']->get('logStage');

        switch($errno)
        {
            case E_USER_ERROR:
                $errMsg = sprintf($fmtMsg, 'My ERROR', $errno, $errstr, $errfile, $errline);
                self::msgHandler($errMsg);
                break;

            case E_USER_WARNING:
                $errMsg = sprintf($fmtMsg, 'My WARNING', $errno, $errstr, $errfile, $errline);
                self::msgHandler($errMsg);
                break;

            case E_USER_NOTICE:
                $errMsg = sprintf($fmtMsg, 'My NOTICE', $errno, $errstr, $errfile, $errline);
                self::msgHandler($errMsg);
                break;

            default:
                $errMsg = sprintf($fmtMsg, 'Unknown error type', $errno, $errstr, $errfile, $errline);
                self::msgHandler($errMsg);
                break;
        }
    }

    /**
    * @name exceptionHandler 处理异常
    *
    * @param $e
    *
    * @returns   
    */
    public static function exceptionHandler($e)
    {
        $exceptionName = get_class($e);
        $fmtMsg = 'Exception(%s):[%s] %s in file %s on line %d' . $_ENV['envConf']->get('logStage');
        $errMsg = sprintf($fmtMsg, $exceptionName, $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
        return self::msgHandler($errMsg);
    }

    /**
    * @name msgHandler 异常和错误消息的处理
    *
    * @param $msg
    *
    * @returns   
    */
    private static function msgHandler($msg)
    {
        if($_ENV['envConf']->get('debug') === true)
            echo $msg;
        else
        {
            //记录操作
            $logs = new Webpie_logs($_ENV['envConf']->get('log'), 'a');
            $logs->record($msg);
            Webpie_Redirect::seeBy50x($_ENV['envConf']->get('go50x', NULL));
        }
    }
}
