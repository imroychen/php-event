<?php


namespace MyNamespace\event\actions;


class TestAction
{
    private $_event;
    public function __construct($event)
    {
        $this->_event = $event;
    }

    public function exec(){
        echo "[Exec:TestAction] test action ^.^ ^.^ ^.^\n";
        //todo
        return true;
    }
}