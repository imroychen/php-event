<?php
namespace ir\e;

/**
 * Class Base
 * @package ir\e
 */
class Subscriber
{

    protected $_id = 0;

    /**
     * @var Event
     */
    protected $_event;

    /**
     * @var bool 调试状态
     */
    protected $_debug=false;

    protected $_eventName='';

    /**
     * Base constructor.
     * @param $id
     * @param Event $event
     */

    public function __construct($id,$event)
    {
        $this->_id = $id;
        $this->_eventName = $event->getName();
        $this->_event = $event;
    }

    public function debug($status){
        $this->_debug=$status;
        return $this;
    }

    protected function _log($msg){
        if($this->_debug){
            print_r($msg);
        }
    }

    public function run(){

        $method = 'on'.$this->_eventName;
        if(method_exists($this,$method)){
            $r = $this->$method();
        }else {
            $r = false;
            //$r = $this->exec();
        }
        if($r) {
            Pool::remove($this->_id);
            return true;
        }
        return false;
    }

    /**
     * 抽象方法的规范 结果只能返回ture|false
     * 如失败 稍后会自动重试 他的下游依赖链也会延时执行
     * @return bool true:成处理,false:失败，
     */
    //abstract protected function exec();


}