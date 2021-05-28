<?php
spl_autoload_register(function ($class) {
    $classPath = str_replace('\\','/',rtrim($class,'\\'));
    if(strpos($classPath,'MyNamespace')===0 && !class_exists($class,false)){
        include str_replace('^MyNamespace/',__DIR__.'/', '^'.$classPath).'.php';
    }
});


if(substr(PHP_SAPI_NAME(),0,3) !== 'cli'){
    exit("请在CLI下运行 / The program runs only in CLI mode!");
}
chdir(__DIR__);
require (dirname(__DIR__)) . '/start.php';

//-------------------------
//系统配置
\ir\e\App::setCfg([
    'subscribers' => 'files:' . __DIR__ . '/event/subscribers/*.php',
    'event' => '\\MyNamespace\\event\\Event',
    //'store_driver'=>'\\MyNamespace\\Driver',
    'store_driver'=>'@Sqlite?dsn=sqlite:'.__DIR__.'/database/sqlite.db&table=ir_event_pool',    //事件消息存储仓库驱动
    //'temp_path'=>'/tmp',//项目可写入的临时目录， 可选 默认系统的临时目录
]);

//启动守护进程
$cmd = isset($argv[1])?$argv[1]:'';
ir\e\Daemon::start($cmd);