<?php


namespace ir\e;


interface Config
{

    /**
     * 消息池的驱动
     * 类名?参数  参数格式URL中QUERY STRING模式
     * 内置驱动可以使用@来省略类的命名空间
     *
     * @example
     * return '@Sqlite?dsn=sqlite:/database/sqlite.db&table=ir_event_pool';
     * return '@DbForLaravel?table=ir_event_pool';
     * return '@DbForTp?table=ir_event_pool';
     * return '@Redis?host=localhost&port=6379&password=密码&key=ir-e-store';
     *
     *  @return string
     */

    public function getPoolDriver();

    /**
     * 获取订阅者列表
     * @return mixed
     */
    public function getSubscribers();

    /**
     * 临时目录 (可写的临时目录) 默认系统临时目录
     * @return string 绝对路径
     */
    public function getTempPath();

    /**
     * 事件绑定的公共命名空间
     * @return bool|string 
     */

    public function getActionNs();

    /**
     * @return mixed
     */

    public function getLogPath();


    /**
     * 返回className
     * @return string event Rule Class
     */
    public function getEventRules();


}