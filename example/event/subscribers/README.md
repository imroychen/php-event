# 事件订阅者

```php
namespace MyNamespace;

class ClassName extends \iry\e\Subscriber{
    
    //Listen AfterXxx 
    //监听 AfterXxx事件
    protected function _onAfterXxx(){
            //$args = $this->_event->getArgs();
            //$args = $this->_event->get('参数名');
    } 
}
```