<?php
//手动加载
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\','/',rtrim($class,'\\'));
    if(strpos($classPath,'ir/e')===0 && class_exists($class,false)){
        include __DIR__.'src/' . $classPath . '.php';
    }
});