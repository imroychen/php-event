<?php

namespace iry\e;
/**
 * Class Fire 客户端使用
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
        $this->_lastEventId = self::Event($eventName,$args,$delay);

        return $this;
    }


    public function then($eventName,$args,$delay=0){
        $lastId = $this->_lastEventId;
        $this->_lastEventId = self::Event($eventName,$args,$this->_delay+$delay,$lastId);

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
        $event = App::formatEName($event);
        $argsIsValid = true;//$this->_checkArgs($args);
        if($argsIsValid) {
            //在不改变原代码结构的情况下，注入自己的代码
            $cls = App::cfg()->getEventRules();
            $eventInfo  = method_exists($cls,$event)?$cls::$event():[];
            if(isset($eventInfo['exec']) && count($eventInfo['exec'])>0){
                foreach ($eventInfo['exec'] as $cls){
                    $cls = str_replace('.','\\',$cls);
                    $_tmp = new $cls();
                    /**
                     * @var $_tmp interfaces\Script
                     */
                    if(method_exists($_tmp,'exec')){
                        $_tmp->exec();
                    }
                }
            }

            if ($event) {
                $data = [
                    'name' => $event,
                    'dependency' => $dependency,
                    'args' => $args
                ];
                //$data['args'] = Pool::dataEncode($args);
                return Pool::add($data,time() + $delay);
            }
        }
        return false;
    }
}