<?php


namespace ir\e;
/**
 * Class Fire
 * @package lm\e
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
}