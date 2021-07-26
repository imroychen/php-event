<?php
/**
 * User: roy
 * Date: 2017/2/24
 * Time: 20:41
 */

namespace iry\e;

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


    function __construct($eventName,$args)
    {
        $this->_eventName = $eventName;//strtolower($eventName);
        $eventCfgCls = App::cfg()->getEventRules();

        if(!empty($eventCfgCls) && method_exists($eventCfgCls,$this->_eventName)) {
            $this->_eventConfig = call_user_func([$eventCfgCls, $this->_eventName]);
            $actionNs =  App::cfg()->getActionNs();
            if(!empty($this->_eventConfig['actions'])) {
                foreach ($this->_eventConfig['actions'] as $key=>$cls) {
                    //auto append ns
                    if (strstr($cls,'\\')===false) {
                        $this->_eventConfig['actions'][$key] = $actionNs .'\\'. $cls;
                    }
                }
            }
        }
        $this->_args = $args;
    }

    /**
     * 获取事件名称
     * @return string
     */
    function getName(){
        return $this->_eventName;
    }

    /**
     * 预留方法
     */
    public function getResult(){}

    /**
     * 获取绑定的动作
     * @return array
     */
    public function getActions(){
        if(isset($this->_eventConfig['actions'])) {
            return $this->_eventConfig['actions'];
        }else{
            return [];
        }
    }

    /**
     * @param string $key
     * @param mixed $default default null
     * @param null|callable $filter default null
     * @return mixed
     */

    public function get($key,$default=null,$filter=null){
        if(isset($this->_args[$key])){
            if(is_callable($filter)){
                return call_user_func($filter,$this->_args[$key]);
            }
            return $this->_args[$key];
        }
        return $default;
    }

    public function getAll(){return $this->_args;}
}