<?php
if(substr(PHP_SAPI_NAME(),0,3) !== 'cli'){
    exit("请在CLI下运行 / The program runs only in CLI mode!");
}
//================================================================
//设置当前示例项目的(MyNamespace)加载目录
//------------------------------
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\','/',rtrim($class,'\\'));
    if(strpos($classPath,'MyNamespace')===0 && !class_exists($class,false)){
        include str_replace('^MyNamespace/',__DIR__.'/', '^'.$classPath).'.php';
    }
});

//================================================================
//导入事件模块
//------------------------------
require (dirname(__DIR__)).'/start.php';
//加载事件模块的配置
iry\e\App::setCfg('\\MyNamespace\\event\\Config');
//-------------------------
//启动守护进程
iry\e\Service::start();