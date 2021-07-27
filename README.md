## PHP-EVENT 2.0
Docs: [中文简体](./README.md), [English](./README-EN.md)
## 安装和使用
<a name="lang-zh-cn"></a>

### 1. 安装

①.使用compser装载 


```shell script
composer require iry/cli
compoer update
```

②.传统/手动装载：在你的公共代码中加入如下代码   [示例](./example/index.php)

```php
require_once('... php-event 路径/start.php');
```



### 2. 配置设置
在公共文件*`(单入口文件的项目建议在入口文件 )`*中加入如下代码. [示例](./example/index.php)

```php
// MyNamespace\event\Config为示例名称请修改自己的Class名称
iry\e\App::setCfg('\\MyNamespace\\event\\Config');//参数为 Class带命名空间的全名称

//iry\e\App::setCfg(\MyNamespace\event\Config::class);//如果php版本>= 5.5 也可以这样
```

### 3. 创建 Class \\MyNamespace\event\\Config
```php
<?php
namespace \MyNamespace\event;
class Config implements \iry\e\Config{
   public function getPoolDriver(){}
   public function getSubscribers(){}
   public function getEventRules(){return 'className';}
   public function getTempPath(){return sys_get_temp_dir();}

}
```
Config接口请参考: [./src/Config.php](./src/Config.php)<br><br>
接口实现示例请参考: [./example/event/Config.php](./example/event/Config.php)<br><br>
#### 方法
**public function getPoolDriver()**

该应用中内置了 Db （Sql DB），Sqlite，Redis，DbForLaravel ,DbForTp等驱动。<br>
内置驱动使用方法：'store_driver'=>'@DbForLaravel?table=event_store', 表示使用内置的驱动（DbForLaravel）参数(表)为event_store
<br> 更多内置驱动请参考[./src/drivers/RADME.md](./src/drivers/README.md)

**public function getSubscribers()**

返回类型 array（推荐）  class列表: ['class1','class2'];<br>
或者返回string <br>
如: <u>_files:subscriber目录/*.php_</u> (会自动从这些文件的代码中分析出来Class全名)

**public function getEventRules()**

返回类型："string", 返回一个 事件规则的Class名称，请参考[./example/event/Event.php](./example/event/Event.php)<br><br>


返回类型："string"

**public function getTempPath()**

返回类型："string",返回一个目录路径的，结尾不要加“/”。 如：_/tmp_<br><br>


### 3.启动事件服务
参考 [example/daemon.php](example/daemon.php)
```php
//启动守护进程
//$argv为所有的命令行参数 $_SERVER['argv']|| 如果是入口文件 也可使用$argv接收
iry\e\Service::start($argv);
```

### 4. 触发事件

#### 快捷使用方法
```php
use iry\e\Fire; //引用类

Fire::event('事件名',['参数1','更多参数...'],'延时广播 秒','依赖事件ID');
Fire::event('complete',[]);//常用方法
Fire::event('complete',[],10);//延时用法，10秒后广播事件消息
Fire::event('complete',[],0,5000);//消息ID为5000的广播确认完之后才会广播当前事件
```
#### 对象使用方法
会自动形成一条事件依赖链，用于保证事件成功广播出去的顺序
```php
use iry\e\Fire; //引用类

$fire = new Fire();
$fire ->start('beforeRequest',['参数1','...']);
    //... 你的业务代码
$fire ->then('afterRequest',['参数1','...']);
    //... 你的业务代码
$fire ->then('complete',['参数1','...']);
//执行顺序 beforeRequest > afterRequest > complete

$fire->getLastEventId();//获取最后一次事件的ID
```
这种用法自动形成一条事件依赖链，用于保证事件成功广播出去的顺序
complete 依赖 afterRequest    complete会自动等待afterRequest被所有监听者确认之后 才会真正广播出去。
afterRequest 依赖 beforeRequest    afterRequest会自动等待 beforeRequest 被所有监听者确认之后 才会真正广播出去。

### 4.事件的使用模式

1. 事件订阅模式
1. 事件绑定模式
1. 代码注入模式

**事件订阅模式**
通过订阅者自主监听事件 （推荐）
请参考 [/example/subscribers/README.md](./example/event/subscribers/README.md);


**事件绑定模式**
通过事件绑定动作，绑定方法：查看[事件配置](./example/event/event.php)的 actions=>['']
请参考 _[/example/event/event.php](./example/event/event.php)_; 和 _[/example/event/actions/README.md](./example/event/actions/README.md)_;


**代码注入模式**
同步注入代码到事件触发处。
查看事件配置的 exec=>['']
请参考 _[example/event/event.php](./example/event/event.php)_; 和 _[/example/event/scripts/README.md](./example/event/scripts/README.md)_;



