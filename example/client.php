<?php
require (__DIR__.'/no-composer-require.php');
//require (__DIR__.'/vendor/autoload.php');

//初始化：加载事件模块的配置
iry\e\App::setCfg('\\MyNamespace\\event\\Setting');

//客户端 开始测试触发事件
$t = microtime(true);
for ($i = 0;$i<100000;$i++){
    echo "Fire the \"Test\" \n";
    //触发一个test事件.
    iry\e\Fire::event('test',['test'=>$i.'. '.date('H:i:s')],0);
    sleep(mt_rand(0,3));
}
echo "\n".(microtime(true)-$t)."\n";
