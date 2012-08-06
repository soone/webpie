<?php
require_once './webpie.php';

class WebpieCli extends Webpie
{
    /**
    * @name __constuct
    *
    * @param $conf 用户自定义的配置文件绝对地址
    *
    * @returns   
    */
    public function __construct($conf = NULL)
    {
		parent::__construct($conf);
    }

    /**
    * @name start 应用启动
    *
    * @returns   
    */
    public function start()
    {
		var_dump($argv);
        $reqUri = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '?'));
        $url = $this->envConf->get('url');
        $handler = NULL;
        $handlerHooks = NULL;
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
            Webpie_Redirect::seeBy404($this->envConf->get('go404', NULL));
            return false;
        }

        return true;
    }
}
