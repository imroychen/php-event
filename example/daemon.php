<?php

use ir\e;

if(substr(PHP_SAPI_NAME(),0,3) !== 'cli'){
    exit("请在CLI下运行 / The program runs only in CLI mode!");
}


chdir(__DIR__);
require (dirname(__DIR__)) . '/start.php';

e\App::setCfg([
    'subscribers' => 'files:' . __DIR__ . '/subscriber/*.php',
    'event' => '\\MyNamespace\\Event',
    //'store_driver'=>'\\MyNamespace\\Driver',
    'store_driver' => '@File:path=' . __DIR__ . '/file_store',    //事件消息存储仓库驱动
    //'temp_path'=>'/tmp',//项目可写入的临时目录， 可选 默认系统的临时目录
]);

//启动守护进程
new e\Daemon();