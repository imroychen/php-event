#事件绑定的动作

事件绑定的动作

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