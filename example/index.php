<?php
require (dirname(__DIR__)).'/start.php';
ir\e\App::setCfg([
    'subscribers'=>function(){
        glob(__DIR__.'/subscriber');
        return '';
    },
    'event'=>'\\MyNamespace\\Event',//事件配置Class
    'store_driver'=>'\\MyNamespace\\Driver',//事件消息存储仓库//
    //'temp_path'=>'/tmp',//项目可写入的临时目录， 可选 默认系统的临时目录
]);
