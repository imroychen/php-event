<?php
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