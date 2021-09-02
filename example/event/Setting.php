<?php


namespace MyNamespace\event;


class Setting implements \iry\e\Setting
{

    /**
     *  消息存储驱动
     * @inheritDoc
     */
    public function getPoolDriver()
    {
        return '@Sqlite?dsn=sqlite:./database/sqlite.db&table=ir_event_pool';
        //return '@Redis?host=localhost&port=6379&password=123&key=ir-e-store';
        //return '\MyNamespace\event\MyDriver'
    }

    /**
     * 消息订阅者列表
     * @inheritDoc
     */
    public function getSubscribers()
    {
        $result = [];

        $files = glob(__DIR__ . DIRECTORY_SEPARATOR . 'subscribers' . DIRECTORY_SEPARATOR . '*.php');
        foreach ($files as $f) {
            $clsName = preg_replace('/^class\.|\.class\.php$|\.php$/i', '', basename($f));
            $result[] = __NAMESPACE__ . '\\subscribers\\' . $clsName;
        }
        return $result;
    }

    /**
     * 事件规则配置
     * @inheritDoc
     */
    public function getEventRules()
    {
        return __NAMESPACE__.'\\Event';
    }

    /**
     * 临时目录
     * @inheritDoc
     */
    public function getTempPath()
    {
        return sys_get_temp_dir();
    }
}