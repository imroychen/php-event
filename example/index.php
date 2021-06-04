<?php

require (dirname(__DIR__)).'/start.php';

ir\e\App::setCfg('\\MyNamespace\\event\\Config');


spl_autoload_register(function ($class) {
    $classPath = str_replace('\\','/',rtrim($class,'\\'));
    if(strpos($classPath,'MyNamespace')===0 && !class_exists($class,false)){
        include str_replace('^MyNamespace/',__DIR__.'/', '^'.$classPath).'.php';
    }
});

$param = uniqid();
for ($i = 0;$i<10;$i++){
    echo "Fire the \"Test\" \n";
    ir\e\Event::fire('test',['test'=>$param.' '.date('H:i:s')],0);//触发一个test事件.
    sleep(mt_rand(0,4));
}


