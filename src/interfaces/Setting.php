<?php


namespace iry\e\interfaces;


interface Setting
{

    /**
     * 当前项目或者设置的名称
     * The name of the current project or setting
     *
     * 多套事件同时使用时 该名称用于隔离
     * When multiple sets of events are used at the same time, the name is used for isolation
     *
     * @return string
     *  允许 字母、数组、下划线
     *  Allow letters, arrays, underscores
     *
     */

    public function name();

    /**
     * Where are the messages stored (example: Redis，Sqlite, Mysql and more DB... )
     * Refer to QUERY STRING in the URL for parameter passing method
     * The built-in driver can use @ to omit the namespace of the class
     *
     * 消息池的驱动 (消息暂存在哪？ 可以是 Redis Sqlite/DB Mysql/DB等等)
     * 用法:
     * 类名?参数  参数格式参考 URL中QUERY STRING
     * 内置驱动可以使用@来省略类的命名空间
     *
     * @example
     * return '@Sqlite?dsn=sqlite:/database/sqlite.db&table=ir_event_pool';
     * return '@DbForLaravel?table=ir_event_pool';                              // 为laravel项目内置的数据库驱动
     * return '@DbForTp?table=ir_event_pool';                                   // 为thinkphp项目内置的数据库驱动
     * return '@Redis?host=localhost&port=6379&password=...&key=ir-e-store';   // 内置的Redis
     * return '\MyNamespace\EventConfig';                                       // 自定义驱动 Class全称
     *
     *  @return string
     */

    public function getPoolDriver();

    /**
     * List subscribers
     * 获取订阅者列表
     *
     * @return array ['Subscriber Class1','Subscriber Class2']
     */

    public function getSubscribers();

    /**
     * Writable temporary directory
     *
     * 临时目录 (可写的临时目录) 默认系统临时目录
     *
     * @return string 绝对路径
     */

    public function getTempPath();//eg: return sys_get_temp_dir()


    /**
     * The name of the event rule
     * 事件规则的名称
     * @return string event Rule Class
     */
    public function getEventRules();

    //public function distributed();


}