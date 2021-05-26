# 事件订阅者

在不改变源代码的结构和逻辑的情况下在触发事件的地方注入您的业务代码

```php
namespace MyNamespace;

class ClassName extends \ir\e\Subscriber{
    
    protected function _onAfterXxx(){
            //$args = $this->_event->getArgs();
    } 
}
```