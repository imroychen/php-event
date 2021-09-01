#内置驱动
1. Db：该驱动 不可直接使用 需要你自己创建一个class 去实现 _query 和 _exec两个方法

2. Redis _<u>Redis?host=localhost&port=3307&dataset=key</u>_
----
3. 如果使用了框架Laravel ，且准备使用数据库作为事件消息存储. 你可以直接使用DbForLaravel驱动 用法'<u>@DbForLaravel?table=表名</u>'

2. 同上 thinkphp框架 也可直接使用_<u>DbForTp</u>_ 内置驱动,用法如上

3. 更多内置驱动参考代码
# 基于DB内置驱动来自定义
使用数据库存储 可以基于 DB内置驱动 自定义驱动
```php
class DbForTp extends \iry\e\drivers\Db
{
    /**
     * @inheritDoc
     */

    protected function _query($sql){}

    /**
     * @inheritDoc
     */

    protected function _exec($sql,$sqlType){}
}
```
# 自定义驱动
只要自己定义个Class 代码如下，代码可以放在你的项目的任何地方。只要符合你的项目的自动加载规范
```php
class MyDriver extends \iry\e\drivers\Driver{
        /**
         * @param array $args 编码后的参数 如:['host'=>'',....]
         * @param $rawArgs 原始的参数 如:host=127.0.0.1;port=....
         */
        protected function _init($args,$rawArgs){
            //$this->_args = $args;
        }
 
        /**
         * @param $id
         * @return mixed false|['字段名'=>'字段值', ...]
         */

        public function get($id){
            //return array|false; 
        }
    
        /**
         * 移除事件监听器动作
         * @param int $id
         * @return bool
         */
    
        public function remove($id){
           //return bool
        }
    
        /**
         * 插入事件监听器动作到池中
         * @param array $data [数据]
         * @return mixed false | id(不可使用0作为ID 0视为false)
         */
        public function create($data){
            //return id|false;
        }
   
    
        /**
         * 修改事件的广播消息时间  暂停事件或者延时的时候使用
         * @param int $id
         * @param int $time 时间戳
         * @return bool
         */
    
        public function setStartingTime($id, $time)
        {
            //string_time
        }
        
        public function getMinTime(){
        }
    
        /**
         * 扫描可运行的任务
         */
    
        public function scan()
        {
            //条件：string_time< time() 
            //规则: 先进先出
            //return []
        }
}
```

## DB驱动数据表
```sql
-- Mysql  ir_event_pool换成你的表名
 CREATE TABLE IF NOT EXISTS `ir_event_pool` (
  `id` varchar(32) NOT NULL,
  `name` varchar(100) NOT NULL,
  `starting_time` int(11) NOT NULL DEFAULT 0,
  `dependency` int(11) NOT NULL DEFAULT 0,
  `args` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `starting_time` (`starting_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```
```sql
-- Qqlite
create table ir_event_pool
(
    id            varchar(32)  not null primary key,
    name          varchar(100) not null,
    starting_time int(11) default 0 not null,
    dependency    int(11) default 0 not null,
    args           text         not null
);
create index starting_time  on ir_event_pool (starting_time);

//也可直接复制 ../../example/data/data_store.sqlite.db; 文件
```