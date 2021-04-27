<?php
#脚手架
function copyDir($src, $dst, $override = false,$fileCallback = false)
{
    $dir = opendir($src);
    if (!is_dir($dst)) {
        @mkdir($dst);
    }
    $hasCallback = is_callable($fileCallback);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..') && ($file != '.git')) {
            if (is_dir($src . '/' . $file)) {
                copyDir($src . '/' . $file, $dst . '/' . $file, $override);
                continue;
            } else {

                if ($override || !file_exists($dst . '/' . $file)) {
                    //echo "\n".$src . '/' . $file."   完成";
                    copy($src . '/' . $file, $dst . '/' . $file);
                    if($hasCallback){
                        call_user_func($fileCallback,$dst,$src);
                    }
                } else {
                    //echo "\n".$src . '/' . $file."   跳过";
                }
            }
        }
    }
    closedir($dir);
}

//$dest = '';
//copy(__DIR__.'/example',$dest)