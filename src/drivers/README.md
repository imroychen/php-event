#内置驱动
1. mysql：该驱动 不可直接使用 需要你自己创建一个class 去实现 method _query 和 _exec两个方法

1. Redis

1. 如果使用了框架thinkphp 3.* 且准备使用数据库作为事件消息存储 你可以直接使用Tp3Db驱动 用法'<u>@Tp3Db:</u>'

1. 同上 thinkphp 5.* 或者 6.* 使用Tp5Db 或者 Tp6Db

1. 更多内置驱动请参考该目录下的文件名
 
# 自定义驱动
只要自己定义个Class 代码如下
```php
class MyDriver implements Driver{
        public function __construct($args){
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
         * 检查事件监听器动作否存在
         * @param $sign
         * @return mixed false | id(不可使用0作为ID 0视为false)
          */
    
        public function isExist($sign)
        {
            //return false|id
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

## mysql 数据表
