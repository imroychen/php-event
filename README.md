## 事件2.0
## 安装和使用
### 1. 安装
1. 传统装载：在你的公共代码中加入 require_once('php-event PATH/start.php');
2. compser装载
```json
    {
        "require-dev": {
                "imroy/php-event":"dev-master"
        },
        "repositories":[
            {
                "name":"imroy/php-event",
                "type":"git",
                "url":"git@code.aliyun.com:imroy/php-event.git"
            }
        ]
    }
```
```SHELL
compoer update
```

### 2. 配置设置
    在公共文件*`(单入口文件的项目建议在入口文件)`*中加入如下代码
```php
ir\e\App::setCfg([
     'event'=>'',//事件配置Class
     'store_driver'=>'\\MyNamespace\event\\Driver',//事件消息存储仓库驱动
     'subscribers'=>'callback | string (files:subscriber绝对目录/*.php)',
     'temp_path'=>'/tmp',//项目可写入的临时目录， 可选 默认系统的临时目录
]);
```

**store_driver:** 
    //该应用中内置了 Db （Sql DB），Sqlite，Redis，DbForLaravel ,DbForTp(thinkphp 项目中的DB操作), 等驱动。<br>
    内置驱动使用方法：'store_driver'=>'@DbForLaravel:event_store', 表示使用内置的驱动（DbForLaravel）参数(表)为event_store
    <br> 更多内置驱动请参考<u>/src/drivers/RADME.md</u>
    
##### subscribers: 
callback | string 如:files:subscriber目录/*.php(会自动从这些文件的代码中分析出来Class全名)
```php
//callback
function(){
        //这里仅仅是一个实例 
        $result=[];
        $files = glob(__DIR__.'/subscriber/*.php');
        foreach ($files as $f) $result[] = '\\MyNamespace\\'.str_replace('.php','',basename($f));
        return $result;
}

//string 格式：(files:subscriber目录/*) 如下示例:
'auto:'.__DIR__.'/subscriber/*.php';
```

### 3触发事件

### 快捷使用方法
```php
use ir\e\Event; //引用类

Event::fire('事件名',['参数1','更多参数...'],'延时广播 秒','依赖事件ID');
Event::fire('complete',[]);//常用方法
Event::fire('complete',[],10);//延时用法，10秒后广播事件消息
Event::fire('complete',[],0,5000);//5000 广播确认完之后才会广播当前事件
```
### 对象使用方法
会自动形成一条事件依赖链，用于保证事件监听者的执行顺序
```php
use ir\e\Fire; //引用类

$fire = new Fire();
$fire ->start('beforeRequest',['参数1','...']);
    //... 你的业务代码
$fire ->then('afterRequest',['参数1','...']);
    //... 你的业务代码
$fire ->then('complete',['参数1','...']);
//执行顺序 beforeRequest > afterRequest > complete

$fire->getLastEventId();//获取最后一次事件的ID
```
这种用法自动形成一条事件依赖链，用于保证事件监听者的执行顺序
complete 依赖 afterRequest    complete会自动等待afterRequest被所有监听者确认之后 才会真正广播出去。
afterRequest 依赖 beforeRequest    afterRequest会自动等待 beforeRequest 被所有监听者确认之后 才会真正广播出去。

### 事件的使用模式


1. 事件订阅模式
1. 事件绑定模式
1. 代码注入模式

#### 事件订阅模式
请参考 example/subscribers/README.md;

#### 事件绑定模式
查看事件配置的 actions=>['']
请参考 _example/event.php_; 和 _example/actions/README.md_;

### 代码注入模式
查看事件配置的 exec=>['']
请参考 _example/event.php_; 和 _example/scripts/README.md_;



