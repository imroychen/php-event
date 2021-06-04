<?php


namespace MyNamespace\event;


class Config implements \ir\e\Config
{

    /**
     * @inheritDoc
     */
    public function getPoolDriver()
    {
        return '@Redis?host=localhost&port=6379&password=123456&key=ir-e-store';
    }

    /**
     * @inheritDoc
     */
    public function getSubscribers()
    {
        $result = [];

        $files = glob(__DIR__ . DIRECTORY_SEPARATOR . 'subscribers' . DIRECTORY_SEPARATOR . '*.php');
        foreach ($files as $f) {
            $clsName = preg_replace('/^class\.|\.class\.php$|\.php$/i', '', basename($f));
            $result[] = __NAMESPACE__ . '\\subscribers\\' . $clsName;
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getTempPath()
    {
        return sys_get_temp_dir();
    }

    /**
     * @inheritDoc
     */
    function getActionNs()
    {
        return __NAMESPACE__;
    }

    /**
     * @inheritDoc
     */
    public function getLogPath()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getEventRules()
    {
        return __NAMESPACE__.'\\Event';
    }
}