<?php
require (dirname(__DIR__)).'/start.php';

ir\e\App::setCfg([
    'subscribers'=>'files:'.__DIR__.'/subscriber/*.php',
    'event'=>'\\MyNamespace\\Event',    //事件配置Class
    //'store_driver'=>'\\MyNamespace\\Driver',    //事件消息存储仓库驱动
    'store_driver'=>'File:path='.__DIR__.'/file_store',    //事件消息存储仓库驱动
    //'temp_path'=>'./tmp',  //项目可写入的临时目录， 可选 默认系统的临时目录
]);

ir\e\Event::fire('test',[]);//触发一个test事件


