<?php
$file=$argv[1];
$script = basename($file);
$dir = dirname($file);

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
        echo $i." 秒后启动\r";
        sleep(1);
    }
    passthru('php '.$script);
}


while (1){
    start($script,5);
}