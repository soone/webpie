<?php
require_once dirname(__FILE__) . '/webpie.php';

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
		global $argv;
        $reqUri = empty($argv[1]) ? '' : trim($argv[1]);
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

            if(preg_match('/' . $regx . '/i', $reqUri) > 0)
            {
                $handlerSuccess = true;
                $handler = $u[1];
                //预置handler的钩子，会在handler初始化时触发
                !empty($u[2]) ? $handlerHooks = $u[2] : '';
                $this->handler($handler, $handlerHooks);
				break;
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
