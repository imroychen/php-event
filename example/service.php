<?php
require (__DIR__.'/no-composer-require.php');
//require (__DIR__.'/vendor/autoload.php');

//初始化：加载事件模块的配置
iry\e\App::setCfg('\\MyNamespace\\event\\Config');

//-------------------------
//启动守护进程
iry\e\Service::start();