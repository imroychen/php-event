<?php
//
if(substr(PHP_SAPI_NAME(),0,3) !== 'cli'){
    exit("请在CLI下运行 / The program runs only in CLI mode!");
}
require (dirname(__DIR__)).'/start.php';
ir\e\App::setCfg([
    'subscribers'=>'auto:'.__DIR__.'/subscriber',
    'event'=>'\\MyNamespace\\Event',
    'store_driver'=>'\\MyNamespace\\Driver',
    //'temp_path'=>'/tmp',//项目可写入的临时目录， 可选 默认系统的临时目录
]);
//启动守护进程
new ir\e\Daemon();