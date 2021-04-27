<?php


namespace MyNamespace\subscriber;


use ir\e;

    /**
     * 这个是一个 监听器 的示例 请勿删除
     * @var $_event e\Event
     */
class Base extends e\Subscriber
{

    protected function _onAfterXxx(){
        //$args = $this->_event->getArgs();
    }

}