<?php


namespace ir\e;

use Exception;
use ir\cli\Cli;
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
                echo '无任务 ['.date('H:i:s')."] \r\n";
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
    
    private function _print($text,$ln=false){
        echo $text.($ln?"\n":'');
    }

    private function _runItem(){
        $task = Pool::scan();
        if($task){
            $this->_print('EventMsg:// ID:'.$task['id'].' / event:'.$task['name'].' /args:' . $task['cfg'] ,true);

            if(empty($this->_listeners)){
                $this->_listeners = self::getListeners();
                $this->_listeners['__']='';//防止没有数据每次都重新分析
            }
            $eventName = $task['name'];
            $nameLower = strtolower($eventName);
            $listeners = (isset($this->_listeners[$nameLower])&& is_array($this->_listeners[$nameLower]))? $this->_listeners[$nameLower]: [];
            $this->_print("\tListeners:".(empty($listeners)?'none':implode(',',array_keys($listeners))) ,true);

            $tracking = Pool::getRuntimeTracking($task['id']);//如果上次意外退出，接着上次继续运行
            $progress = array_flip($listeners);//记录进度
            $event = new Event($task['name'],$task['args']);

            foreach ($listeners as $cls=>$method) {
                $this->_print( "/".$cls,false);
                if(isset($tracking[$cls]) && $tracking[$cls]) {
                    unset($progress[$cls]);
                    $this->_print( "> skip",false);
                }else {
                    $listenerObj = new $cls($task['id'],$event);
                    if ($listenerObj->run()) {
                        Pool::setRuntimeTracking($task['id'],$cls,1);//如果上次意外退出，接着上次继续运行
                        unset($progress[$cls]);
                        $this->_print( "> ok",false);
                    } else {
                        $this->_print( "> false",false);
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
        //var_export($subscribers);
        if(count($subscribers)>0) {
            foreach ($subscribers as $cls) {
                /**
                 * @var Subscriber $cls
                 */
                $cls = preg_replace('/(\.class)*\.php$/i','',$cls);
                //try {
                    //$cls::__check__();
                    $obj = new ReflectionClass($cls);
                    $methods = $obj->getMethods();
                    //var_export($methods);
                    foreach ($methods as $m) {
                        $nameLower = strtolower($m->name);
                        if (strpos($nameLower, '_on') === 0) {
                            $eventName = substr($nameLower, 3);
                            if (!isset($res[$eventName])) {
                                $res[$eventName] = [];
                            }
                            $res[$eventName][$cls] = $m->name;
                        }
                    }
                //} catch (Exception $e) {
                //}
            }
        }

        return $res;
    }

    static private function _showEvent($p=''){
        $listeners = self::getListeners();
        $eventCls = App::cfg('event');

        $funcList = [];
        $_tmp = get_class_methods($eventCls);
        foreach ($_tmp as $funcName){
            $funcList[strtolower($funcName)] = $funcName;
        }

        if(!empty($p)){
            $p = trim(strtolower($p));
            $listeners = isset($listeners[$p])?[$listeners[$p]]:[];
        }
        if(!empty($listeners)) {
            foreach ($listeners as $event => $sub) {

                $cfg = [];
                if (method_exists($eventCls, $event)) {
                    $cfg = $eventCls::$event();
                }

                echo "\n+------------------------------------------";
                echo "\n <" . (isset($funcList[$event]) ? $funcList[$event] : $event) . ">\t" . (empty($cfg) ? '--' : json_encode($cfg)) . "\n";

                foreach ($sub as $cls => $func) {
                    echo "\n\t" . $cls;//.' > '.$func;
                }
                echo "\n";
            }
            echo "\n+------------------------------------------\n";
        }
        exit;
    }

    static public function start($cmd=''){
        $cmd = trim($cmd);
        if($cmd==='--ls'){
            self::_showEvent();
        }elseif(strpos($cmd,'--event:')===0){
            list(,$event) = explode(':',$cmd);
            self::_showEvent($event);
        }
        //elseif ($cmd==='....'){}//更多参数
        else {
            //echo "5秒后启动监听器守护程序，结束请按 < Ctrl + C >\n";
            //for ($i=5;$i>0;$i--){sleep(1);echo $i."\r"; }

            //sleep(5);
            $cls = __CLASS__;
            new $cls();
        }

    }
}