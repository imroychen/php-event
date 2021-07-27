<?php
//手动加载
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\','/',rtrim($class,'\\'));
    if(strpos($classPath,'iry/e')===0 && !class_exists($class,false)){
        //var_export(__DIR__.'/src/' . $classPath . '.php');echo "\n";
        include str_replace('^iry/e/',__DIR__.'/src/', '^'.$classPath).'.php';
    }
});