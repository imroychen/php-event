# 事件订阅者

```php
namespace MyNamespace;

class ClassName extends \ir\e\Subscriber{
    
    //Listen AfterXxx 
    //监听 AfterXxx事件
    protected function _onAfterXxx(){
            //$args = $this->_event->getArgs();
    } 
}
```