# 使用实例
## 目录
```
/
├── database       测试项目的存储仓库（数据库）
├── client.php     客户端 测试触发事件的代码 
├── service.php    服务端 守护进程（广播事件消息用的 请使用CLI启动） php daemon.php
├── no-composer-require.php 无Composer环境使用

+++++++++++++++++++++【 以上为示例模拟项目的基础代码 和事件代码无关 】+++++++++++++++
                    
├── event/ 事件相关的代码目录
│   ├── Event.php      事件配置
│   ├── Driver.php     自定义驱动的示例  【可选】
│   ├── subscribers/   事件订阅者目录 （用于存放订阅者模式相关的代码） 【可选】
│   │   ├── Example.php    事件监听者及动作        
│   ├──actions/       事件绑定的动作目录 （用于存放 事件模式下绑定的动作代码） 【可选】
│   ├──scripts/       注入代码 （该目录用于存放 注入到 事件触发处的同步运行的代码） 【可选】
```


# E.g

## content
```
/
├── database       The storage warehouse (or database) of the test project
├── client.php     client test the code that triggers the event
├── service.php    server daemon (for broadcasting event messages, please use CLI to start) php daemon.php
├── no-composer-require.php    No Composer environment use

+++++++++++++++++++++ [The above is the basic code of the sample simulation project and has nothing to do with the event code] +++++++++++++++
        
├── event/ event-related code directory
│   ├── Event.php     event rules and setting
│   ├── Driver.php    Examples of custom drivers [optional]
│   ├── subscribers/ event subscriber directory (used to store codes related to subscriber mode) [optional]
│   │   ├── Example.php event listener and action          
│   ├── actions/ Action directory bound to events (used to store the action codes bound in event mode) [Optional]
│   ├── scripts/ injection code (this directory is used to store the synchronous running code injected into the event trigger) [optional]
```
  