<?php
if(substr(PHP_SAPI_NAME(),0,3) !== 'cli'){
    exit("请在CLI下运行 / The program runs only in CLI mode!");
}

$file=$argv[1];
$script = basename($file);
$dir = dirname($file);

//计算工作目录并且换 / Calculate and change the working directory
if($dir!='.'){
    if (strpos($dir,'..')===0){
        $dir = realpath(rtrim(getcwd(),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$dir);
    }
    chdir($dir);//切换目录
    echo "工作目录已经切换到【". getcwd(). "】\n";
}else{
    echo "工作目录:【". getcwd(). "】\n";
}


function start($script,$sleep){
    for ($i=$sleep; $i>0;$i--) {
        echo $i." 秒后启动 / Start in $i seconds\r";
        sleep(1);
    }
    passthru('php '.$script);//避免修改代码后需要重启
}

//您的脚本退出后 该服务会自动重启你的进程
while (1){
    start($script,1);
    //exit;
}