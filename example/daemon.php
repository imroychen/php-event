<?php
//MyNamespace autoLoad
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\','/',rtrim($class,'\\'));
    if(strpos($classPath,'MyNamespace')===0 && !class_exists($class,false)){
        include str_replace('^MyNamespace/',__DIR__.'/', '^'.$classPath).'.php';
    }
});


if(substr(PHP_SAPI_NAME(),0,3) !== 'cli'){
    exit("请在CLI下运行 / The program runs only in CLI mode!");
}

chdir(__DIR__);
require (dirname(__DIR__)) . '/start.php';

//-------------------------
//系统配置
ir\e\App::setCfg('\'\\MyNamespace\\event\\Config');
//启动守护进程
ir\e\Service::start($argv);