#此目录为注入的代码

在不改变源代码的结构和逻辑的情况下在触发事件的地方注入您的业务代码

```php
namespace MyNamespace;
class ScriptName{
    public function __construct($event) {
        //todo
    }
}
```