<?php


namespace MyNamespace\event;


class Config
{
    /**
     * 消息池的驱动
     * 类名?参数  参数格式URL中QUERY STRING模式
     * 内置驱动可以使用@来省略类的命名空间
     * @return string
     */
    static public function getPoolDriver(){
        //'\\MyNamespace\\Driver',
        //'@Sqlite?dsn=sqlite:/database/sqlite.db&table=ir_event_pool',
        //'@DbForLaravel?table=ir_event_pool',
        //'@DbForTp?table=ir_event_pool',
        //'@Redis?host=localhost&port=6379&password=密码&key=ir-e-store',

        return '@Redis?host=localhost&port=6379&password=xacegikm&key=ir-e-store';
    }

    /**
     * 获取订阅者列表
     * @return array
     */

    static public function getSubscribers(){
        $result = [];
        $files = glob(__DIR__.DIRECTORY_SEPARATOR.'subscribers'.DIRECTORY_SEPARATOR.'*.php');
        foreach ($files as $f) {
            $clsName = preg_replace('^class\.|\.class\.php$|\.php$','',basename($f));
            $result[] = __NAMESPACE__ . '\\subscribers\\'.$clsName;
        }
        return $result;
    }

    /*
     * 临时目录 (可写的临时目录) 默认系统临时目录
     */

    static public function getTempPath(){
        return sys_get_temp_dir();
    }

    /**
     * 日志目录 false不写日志
     * @return bool
     */

    static public function getLogPath(){
        return false;
    }


}