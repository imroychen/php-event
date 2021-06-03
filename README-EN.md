Docs: [中文简体](./README.md), [English](./README-EN.md)
## How to install and use
<a id="lang-en"></a>
## 1.installation
1. Traditional loading: add require_once('php-event PATH/start.php'); to your public file [example](./example/index.php)

2. Install using composer. 

```json
    {
        "require-dev": {
                "imroy/php-event":"dev-master"
        },
        "repositories":[
            {
                "name":"imroy/php-event",
                "type":"git",
                "url":"git@co...hp-event.git"
            }
        ]
    }
```

```SHELL
compoer update
```

# Add configuration
```php
ir\e\App::setCfg([
     'event'=>'',//Event rules (Class File)
     'store_driver'=>'\\MyNamespace\event\\Driver',//Event message pool driver (Storage drive)
     'subscribers'=>'callback | string (files:subscriber PATH/*.php)',
     'temp_path'=>'/tmp',//[Optional] Writable temporary directory, default system temporary directory
]);
```


**event:** Example[./example/event/Event.php](./example/event/Event.php)<br><br>
**store_driver:** 
    //This package contains the following built-in drivers (DB (Sql DB), Sqlite, Redis, DbForLaravel, DbForTp).<br>
    Custom drive:  'store_driver'=>'Class Name(Contains namespace)'
    Built-in drive: 'store_driver'=>'@DbForLaravel?table=event_store'
    <br> For more built-in drivers, please refer to[./src/drivers/RADME.md](./src/drivers/RADME.md)
   <br> <br>
**subscribers:**
callable | string eg: files:subscriberPATH/*.php(The Class name will be automatically analyzed from the code of these files)
```php
// callable// callback function
function(){
        //This is just an example
        $result=[];
        $files = glob(__DIR__.'/subscriber/*.php');
        foreach ($files as $f) $result[] = '\\MyNamespace\\'.str_replace('.php','',basename($f));
        return $result;
}

//string (files:subscriber PATH/*):
'files:'.__DIR__.'/subscriber/*.php';
```

### 3. Fire EVENT

#### Quick way
```php
use ir\e\Event;

Event::fire('Event Name',['argument 1','argument 2...'],'Delay broadcast for n seconds','Dependent event ID');
Event::fire('complete',[]);
Event::fire('complete',[],10);//Delay usage，Broadcast event message after 10 seconds
Event::fire('complete',[],0,5000);//5000 The current event will not be broadcast until the broadcast is confirmed
```
#### Use event objects
An event dependency chain will be automatically formed to ensure the sequence of events being broadcast successfully
```php
use ir\e\Fire; 

$fire = new Fire();
$fire ->start('beforeRequest',['参数1','...']);
    //... Your code
$fire ->then('afterRequest',['参数1','...']);
    //... Your code
$fire ->then('complete',['参数1','...']);
//The broadcast sequence is:
// beforeRequest > afterRequest > complete

$fire->getLastEventId();//Get the ID of the last event
```
This usage automatically forms an event dependency chain to ensure the sequence in which events are successfully broadcast.
**_"Complete"_** depends on _**"afterRequest"**_ and _"complete"_ will automatically wait for _"afterRequest"_ to be confirmed by all listeners before it is actually broadcast.

**_"AfterRequest"_** relies on **_"beforeRequest"_**, **_"afterRequest"_** will automatically wait for **_"beforeRequest"_** to be confirmed by all listeners before it is actually broadcast.