<?php
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
//---------------------------------------------------------------
require (dirname(__DIR__)).'/start.php';
//加载事件模块的配置
iry\e\App::setCfg('\\MyNamespace\\event\\Config');



//================================================================
//开始测试触发事件
//------------------------------------------
$param = uniqid();
for ($i = 0;$i<10;$i++){
    echo "Fire the \"Test\" \n";
    //触发一个test事件.
    iry\e\Fire::event('test',['test'=>$param.' '.date('H:i:s')],0);
    sleep(mt_rand(0,4));
}


