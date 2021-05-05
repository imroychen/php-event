<?php
/**
 * User: roy
 * Date: 2017/2/24
 * Time: 16:41
 */

namespace ir\e;

use phpseclib3\Net\SFTP\Stream;

class Event
{

    private $_eventName;
    private $_args = [];

    private $_runtimeMsg = '';

    private $_eventConfig = [];


    /**
     * 参数检查
     */
    private function _checkArgs(){
        $res = true;
        $missFields = [];
        if(!empty($this->_required)){
            foreach ($this->_required as $k => $v){
                $missFields[]=$k;
                $res = $res && isset($this->_args[$k]);
            }
        }
        if(!$res) {
            $this->_runtimeMsg = 'Missing fields:[' . implode(' , ', $missFields) . ']';
        }
        return false;
    }

    function getName(){
        return $this->_eventName;
    }


    function __construct($eventName,$args)
    {
        $this->_eventName = $eventName;//strtolower($eventName);
        $eventCfgCls = App::cfg('event');
        if(empty($eventCfgCls) && method_exists($this->_eventName,$eventCfgCls)) {
            $this->_eventConfig = call_user_func([$eventCfgCls, $this->_eventName]);
        }
        $this->_args = $args;
    }

    function getListeners(){
        /*$name = $this->getName();
        $listenersList = include (__DIR__.'/Listeners.php');
        $append = [];
        if(is_array($listenersList) && isset($listenersList[$name])) {
            $append = $listenersList[$name];
        }
        $r = array_merge($this->_listeners, $append);
        return array_unique($r);
        */
    }

    function getResult(){}

    /**
     * @param string $key
     * @param mixed $default default null
     * @param null|callable $filter default null
     * @return mixed|null
     */

    function get($key,$default=null,$filter=null){
        if(isset($this->_args[$key])){
            if(is_callable($filter)){
                return call_user_func($filter,$this->_args[$key]);
            }
            return $this->_args[$key];
        }
        return $default;
    }

    /**
     * @param string $event
     * @param array $args
     * @param int $delay 异步延时
     * @param int $dependency 事件依赖
     * @return int
     */

    static public function fire($event,$args,$delay=3,$dependency=0){
        return App::fire($event,$args,$delay,$dependency);
    }
}