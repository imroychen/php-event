<?php
namespace MyNamespace\event\subscribers;

class Example extends Base
{

    protected function _onAfterXxx(){
        //$args = $this->_event->getArgs();
        return true;
    }

    protected function _onTest(){
        $args = $this->_event->get();
        var_export($args);
        echo "OK";
        return true;
    }




}