<?php


namespace ir\e;

use Exception;
use ReflectionClass;

class Daemon
{
    private $_timeout = 0;
    private $_enableTimeoutCtrl=false;
    private $_listeners = [];
    public function __construct($limitTime=-1)
    {
        $status = true;
        $this->_timeout = time()+$limitTime;
        $this->_enableTimeoutCtrl = ($limitTime>0);
        //$this->_listeners = self::getListeners();放在首次有任务的时候计算

        while ($status){
            $status = self::_runItem();
            if(!$status){
                echo "无任务\r\n";
                Pool::setMark(time()+60,true);//如果没有新的事件发生，将会在60秒后重试
                while (1){
                    $mark = Pool::getMark();//避免数据库过载
                    if($mark>time()) {
                        sleep(1);
                        echo date('H:i:s')."\r";
                    }else{
                        echo "发现新任务\r\n";
                        $status = true;
                        break;
                    }

                    if($this->_isTimeout()){break 2;}
                }
            }else{
                if($this->_isTimeout()){$status = false;}
            }
        }
    }

    private function _isTimeout(){
        return $this->_enableTimeoutCtrl && $this->_timeout<time();
    }

    private function _runItem(){
        $task = Pool::scan();
        if($task){
            if(empty($this->_listeners)){
                $this->_listeners = self::getListeners();
                $this->_listeners['__']='';//防止没有数据每次都重新分析
                Pool::resetRuntimeTracking(0);
            }
            $eventName = $task['name'];
            $nameLower = strtolower($eventName);
            $listeners = (isset($this->_listeners[$nameLower])&& is_array($this->_listeners[$nameLower]))? $this->_listeners[$nameLower]: [];
            echo 'Listeners:'.(empty($listeners)?'none':implode(',',array_keys($listeners)));
            $tracking = Pool::getRuntimeTracking($task['id']);//如果上次意外退出，接着上次继续运行
            $progress = array_flip($listeners);//记录进度
            $event = new Event($task['name'],$task['args']);

            echo 'ID:'.$task['id'].' / event:'.$task['name'].' / action:'.$task['listener'].' /args:' . $task['cfg'] . "\n";

            foreach ($listeners as $cls=>$method) {
                echo "/".$cls;
                if(isset($tracking[$cls]) && $tracking[$cls]) {
                    unset($progress[$cls]);
                    echo "> skip";
                }else {
                    $listenerObj = new $cls($task['id'],$event);

                    if ($listenerObj->$method()) {
                        unset($progress[$cls]);
                        echo " > ok";
                    } else {
                        echo "\t $cls > false";
                    }

                }
            }
            if(empty($progress)){
                Pool::remove($task['id']);
            }

            //有下一页
            return true;
        }
        //无下一页
        return false;
    }

    static private function _getClsByFilePath($f){
        $code = preg_replace('%(^|\n)//.*?\n%',"\n",file_get_contents($f));
        $code = preg_replace('%/\*(\w\W)*\*/%',"",$code);
        $matches = [];
        $ns = '';
        if(stripos($code,'namespace')) {
            preg_match('%(^|\n)\s*namespace\s+([\w\\\]+)\s*;%i', $code, $matches);
            $ns = $matches[2];
        }
        $clsName = '';
        if(stripos($code,'class')) {
            preg_match('%(^|\n)\s*class\s+([\w]+)\W%i', $code, $matches);
            $clsName = $matches[2];
        }
        return $ns.'\\'.$clsName;
    }

    static protected function getListeners(){
        $res = [];

        $subscribers = [];
        $subscribersCfg = App::cfg('subscribers');
        if(is_string($subscribersCfg) && strpos($subscribersCfg,'files:')===0){
            $path = str_replace('^files:','','^'.$subscribersCfg);
            $files = glob($path);
            //获取订阅这列表
            foreach ($files as $f){
                //开始分析该订阅者的监听器
                if($f) $subscribers[] = self::_getClsByFilePath($f);
            }
        }elseif(is_callable($subscribersCfg)){
            $subscribers = call_user_func($subscribersCfg);
        }

        if(count($subscribers)>0) {
            foreach ($subscribers as $cls) {
                try {
                    $obj = new ReflectionClass($cls);
                    $methods = $obj->getMethods();
                    foreach ($methods as $m) {
                        if (strpos($m, '_on') === 0) {
                            $eventName = strtolower(substr($m->name, 3));
                            if (!isset($res[$eventName])) {
                                $res[$eventName] = [];
                            }
                            $res[$eventName][$cls] = $m->name;
                        }
                    }
                } catch (Exception $e) {
                }
            }
        }

        return $res;
    }
}