<?php

require (dirname(__DIR__)).'/start.php';

ir\e\App::setCfg([
    'subscribers'=>'files:'.__DIR__.'/event/subscribers/*.php',
    'event'=>'\\MyNamespace\\event\\Event',//事件配置Class
    //'store_driver'=>'\\MyNamespace\\Driver',    //事件消息存储仓库驱动
    //'store_driver'=>'@Sqlite?dsn=sqlite:'.__DIR__.'/database/sqlite.db&table=ir_event_pool',    //事件消息存储仓库驱动
    'store_driver'=>'@Redis?host=localhost&port=6379&password=xacegikm&key=ir-e-store',
    //'temp_path'=>'./tmp',  //项目可写入的临时目录， 可选 默认系统的临时目录
]);


spl_autoload_register(function ($class) {
    $classPath = str_replace('\\','/',rtrim($class,'\\'));
    if(strpos($classPath,'MyNamespace')===0 && !class_exists($class,false)){
        include str_replace('^MyNamespace/',__DIR__.'/', '^'.$classPath).'.php';
    }
});

$param = uniqid();
for ($i = 0;$i<10;$i++){
    echo "Fire the \"Test\" \n";
    ir\e\Event::fire('test',['test'=>$param.' '.date('H:i:s')]);//触发一个test事件.
    sleep(mt_rand(0,4));
}


