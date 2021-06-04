<?php
namespace MyNamespace\event\subscribers;

class Example extends Base
{

    protected function _onAfterXxx(){
        //$args = $this->_event->getArgs();
        return true;
    }

    protected function _onTest(){
        $args = $this->_event->getAll();
        echo "\n\t".__FUNCTION__.' '.json_encode($args)."\n";
        echo "\t\tOK\n";
        return true;
    }




}