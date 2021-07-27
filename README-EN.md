Docs: [中文简体](./README.md), [English](./README-EN.md)
## How to install and use
<a id="lang-en"></a>
## 1.installation
1). Install using composer. 

```shell script
composer require iry/cli
compoer update
```

2).Traditional loading: add require_once('php-event PATH/start.php'); to your public file [example](./example/index.php)

# Add configuration

```php
iry\e\App::setCfg('\\MyNamespace\\event\\Config');
```

### 3. Create class \\MyNamespace\event\\Config
```
namespace \\MyNamespace\event;
class Config implements \iry\e\Config{
   public function getPoolDriver()
   public function getSubscribers(){}
   public function getEventRules(){}
   public function getTempPath(){}

}
```
Interface : [./src/Config.php](./src/Config.php)<br><br>
Please refer to the interface docking example: [./example/event/Config.php](./example/event/Config.php)<br><br>
#### 方法
**public function getPoolDriver()**

 //This package contains the following built-in drivers (DB (Sql DB), Sqlite, Redis, DbForLaravel, DbForTp).<br>
    Custom drive:  'Class Name(Contains namespace)'
    Built-in drive: '@DbForLaravel?table=event_store'
    <br> For more built-in drivers, please refer to[./src/drivers/RADME.md](./src/drivers/RADME.md)

**public function getSubscribers()**

return array（recommended）|string<br>
  array: class list []: ['class1','class2'];<br>
  string: Example: <u>_files:Subscriber Path/*.php_</u> (Automatically analyze the full name of the class from the code of these files)

**public function getEventRules()**

return ："string", Class Name，Example[./example/event/Event.php](./example/event/Event.php)<br><br>

return："string"

**public function getTempPath()**

return："string",Return a directory path, do not add "/" at the end。 Example：_/tmp_<br><br>


### 3. Fire EVENT

#### Quick way
```php
use iry\e\Event;

Event::fire('Event Name',['argument 1','argument 2...'],'Delay broadcast for n seconds','Dependent event ID');
Event::fire('complete',[]);
Event::fire('complete',[],10);//Delay usage，Broadcast event message after 10 seconds
Event::fire('complete',[],0,5000);//5000 The current event will not be broadcast until the broadcast is confirmed
```
#### Use event objects
An event dependency chain will be automatically formed to ensure the sequence of events being broadcast successfully
```php
use iry\e\Fire; 

$fire = new Fire();
$fire ->start('beforeRequest',['arguments1','...']);
    //... Your code
$fire ->then('afterRequest',['arguments1','...']);
    //... Your code
$fire ->then('complete',['arguments1','...']);
//The broadcast sequence is:
// beforeRequest > afterRequest > complete

$fire->getLastEventId();//Get the ID of the last event
```
This usage automatically forms an event dependency chain to ensure the sequence in which events are successfully broadcast.
**_"Complete"_** depends on _**"afterRequest"**_ and _"complete"_ will automatically wait for _"afterRequest"_ to be confirmed by all listeners before it is actually broadcast.

**_"AfterRequest"_** relies on **_"beforeRequest"_**, **_"afterRequest"_** will automatically wait for **_"beforeRequest"_** to be confirmed by all listeners before it is actually broadcast.