# 事件绑定模式的代码 
所有动作的代码都写在该该目录

```php
namespace MyNamespace;
class ActionName{
    private $_event;
    public function __construct($event) {
        $this->_event = $event;
    }
    
    
    public function getResult(){
        return true;
    }

}
```