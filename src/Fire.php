<?php


namespace iry\e;
/**
 * Class Fire
 * @package iry\e
 *
 * @example ./README.md *
 */

class Fire
{
    private $_delay = 0;
    private $_lastEventId = 0;
    public function start ($eventName,$args,$delay=3){
        $this->_delay = $delay;
        $this->_lastEventId = App::fire($eventName,$args,$delay);

        return $this;
    }


    public function then($eventName,$args,$delay=0){
        $lastId = $this->_lastEventId;
        $this->_lastEventId = App::fire($eventName,$args,$this->_delay+$delay,$lastId);

        return $this;
    }

    public function getLastEventId(){
        return $this->_lastEventId;
    }

    /**
     * @param string $event
     * @param array $args
     * @param int $delay 异步延时 可选
     * @param int $dependency 事件依赖 可选
     * @return int
     */
    static public function event($event,$args,$delay=2,$dependency=0){
        return App::fire($event,$args,$delay,$dependency);
    }
}