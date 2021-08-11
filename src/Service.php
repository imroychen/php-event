<?php


namespace iry\e;

use ReflectionClass;

class Service
{
    private $_timeout = 0;
    private $_enableTimeoutCtrl=false;
    private $_listeners = [];

    private $_colorStyle = true;

    private $_trackingResultLog = '';
    public function __construct($options = [])
    {
        $this->_trackingResultLog = App::getTempPath('iry-event-mark');
        if(!empty($options)){
            $this->_colorStyle = (isset($options['--color']) && strtolower($options['--color'])==='n')?false:true;
            if($this->_colorStyle && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                echo 'DEVICE=%WinDir%\System32\ANSI.SYS /x >%WinDir%\System32\CONFIG.NT';
                //装载window彩色驱动
            }
        }

    }


    private function _isTimeout(){
        return $this->_enableTimeoutCtrl && $this->_timeout<time();
    }
    
    private function _print($text,$fgColor=null,$bgColor=null){
        if($this->_colorStyle){
            $coloredString = "";
            // Check if given foreground color found
            if ($fgColor) {
                $coloredString .= "\033[" . $fgColor . "m";
            }
            // Check if given background color found
            if ($bgColor) {
                $coloredString .= "\033[" . $bgColor . "m";
            }
            echo $coloredString.$text . "\033[0m";
        }else{
            echo $text;
        }
    }

    private function _printLn($text,$fgColor=null,$bgColor=null){
        if($this->_colorStyle){
           $this->_print($text."\n",$fgColor,$bgColor);
        }else{
            echo $text."\n";
        }
    }

    private function _repairLastResult(){
        $logFile = $this->_trackingResultLog;
        if(file_exists($logFile)) {
            $lastResult = file_get_contents($logFile);
            if ($lastResult && trim($lastResult) != '') {
                $_tmp = explode("\n", $lastResult);


                $res = [];
                foreach ($_tmp as $v) {
                    list($id, $val) = explode("::", trim($v) . "::");
                    if ($id != '' && $val != '') {
                        $res[$id][$val] = 1;
                    }
                }

                if (!empty($res)) {
                    foreach ($res as $lastId => $lastResult) {
                        Pool::setResult($lastId, $lastResult);
                    }
                }

                file_put_contents($logFile, '');
            }
        }
    }

    private function _resetLastResult(){
        file_put_contents($this->_trackingResultLog,'');
    }

    private function _recordLastResult($id,$cls){
        file_put_contents($this->_trackingResultLog , "\n".$id."::".$cls ,FILE_APPEND);
    }

    private function _runItem(){
        $task = Pool::scan();
        if(empty($task)){
            //无下一页
            return false;
        }
        elseif (!isset($task['id']) || empty($task['id'])){
            $this->_print("\n\n * Invalid TASK ID \n",'1;37',41); echo "\n";
            print_r($task);
            return true;
        }
        elseif(!isset($task['name']) || empty($task['name'])){
            $this->_print("\n\n * Invalid TASK (ID:".$task['id'].") AND Auto Remove it",'1;37',41); echo "\n";
            Pool::remove($task['id']);
            return true;
        }
        else{
            $this->_printLn('EventMsg:// ID:'.$task['id'].' / event:'.$task['name'].' /args:' . json_encode($task['args']) );

            if(empty($this->_listeners)){
                $this->_listeners = $this->_getListeners();
                $this->_listeners['__']='';//防止没有数据每次都重新分析
            }
            $eventName = $task['name'];
            $nameLower = strtolower($eventName);
            $listeners = (isset($this->_listeners[$nameLower])&& is_array($this->_listeners[$nameLower]))? $this->_listeners[$nameLower]: [];
            $this->_printLn("\tListeners:".(empty($listeners)?'none':implode(',',array_keys($listeners))));

            $tracking = $task['result'];//如果上次意外退出，接着上次继续运行
            $progress = $listeners; //记录进度
            $event = new Event($task['name'],$task['args']);

            //执行事件绑定的动作
            $actions = $event->getActions();
            if(!empty($actions)) {
                foreach ($actions as $action) {
                    if(isset($tracking[$action]) && $tracking[$action]) {
                        //todo
                    }else{
                        $r = (new $action($event))->exec();
                        if ($r || is_null($r)) {//null 没有返回值当成功处理
                            //记录运行日志 如果上次意外退出，以便接着上次继续运行
                            $this->_recordLastResult($task['id'],$action);
                        }
                    }
                }
            }
            //发送消息到订阅者的监听器

            foreach ($listeners as $cls=>$method) {
                $this->_print( "/".$cls);
                if(isset($tracking[$cls]) && $tracking[$cls]) {
                    unset($progress[$cls]);
                    $this->_print( "> skip");
                }else {
                    $listenerObj = new $cls($task['id'],$event);
                    /**
                     * @var $listenerObj Subscriber
                     */
                    if ($listenerObj->run()) {
                        //记录运行日志 如果上次意外退出，以便接着上次继续运行
                        $this->_recordLastResult($task['id'],$cls);
                        unset($progress[$cls]);
                        $this->_print( "> ok");
                    } else {
                        $this->_print( "> false");
                    }
                }
            }

            if(empty($progress)){
                Pool::remove($task['id']);
                $this->_resetLastResult();
            }

            //有下一页
            return true;
        }

    }

    private function _getClsByFilePath($f){
        $code = preg_replace('%(^|\n)//.*?\n%',"\n",file_get_contents($f));
        $code = preg_replace('%/\*(\w\W)*\*/%',"",$code);
        $matches = [];
        $ns = '';
        if(stripos($code,'namespace')) {
            preg_match('%(^|\n)\s*namespace\s+([\w\\\]+)\s*;%i', $code, $matches);
            $ns = $matches[2];
        }
        $clsName = '';
        //尝试从注解中获取
        if(stripos($code,'@subscriberName')) {
            preg_match('/\n\s*\*\s*@subscriberName\s+(\w[\w\\\]+)\s+/i', $code, $matches);
            $clsName = empty($matches[1])?trim($matches[1]):'';
        }

        //从代码Class Name中获取
        if(empty($clsName) && stripos($code,'class')) {
            preg_match('%(^|\n)\s*class\s+([\w]+)\W%i', $code, $matches);
            $clsName = $matches[2];
        }
        return $ns.'\\'.$clsName;
    }

    private function _getListeners(){
        $res = [];

        $subscribers = App::cfg()->getSubscribers();
        if(is_string($subscribers) && strpos($subscribers,'files:')===0){
            $path = str_replace('^files:','','^'.$subscribers);
            $files = glob($path);
            //获取订阅这列表
            $subscribers = [];
            foreach ($files as $f){
                //开始分析该订阅者的监听器
                if($f) $subscribers[] = $this->_getClsByFilePath($f);
            }
        }
        //var_export($subscribers);
        if(count($subscribers)>0) {
            foreach ($subscribers as $cls) {
                /**
                 * @var Subscriber $cls
                 */
                $cls = preg_replace('/^class\.|(\.class)*\.php$/i','',$cls);
                //if($cls::__check__()) {
                    //try {
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
                //}
            }
        }

        return $res;
    }

    /**
     * 运行守护程序
     * @param $limitTime
     */
    public function daemon($limitTime=-1){
        $status = true;
        $this->_timeout = time()+$limitTime;
        $this->_enableTimeoutCtrl = ($limitTime>0);
        //$this->_listeners = $this->_getListeners();放在首次有任务的时候计算

        $this->_repairLastResult();//如果上次意外退出 尝试修复上次意外退出的的结果

        while ($status){
            $status = self::_runItem();
            if(!$status){
                echo 'No task 无任务 ['.date('H:i:s')."] \r\n";
                Pool::sendEMsg(time() + 60, true);//如果没有新的事件发生，将会在60秒后重试
                while (1) {
                    $mark = Pool::getEMsg();//避免读取数据导致服务器过载（特别是使用数据库驱动时候）
                    if ($mark > time()) {
                        sleep(1);
                        echo date('H:i:s') . "\r";
                    } else {
                        echo "New Task 发现新任务\r\n";
                        $status = true;
                        break;
                    }

                    if ($this->_isTimeout()) {
                        break 2;
                    }
                }
            }else{
                if($this->_isTimeout()){$status = false;}
            }
        }
    }

    /**
     * 列出事件 详情
     * @param bool $showEvent
     */
    public function ls($showEvent=false){
        $listeners = $this->_getListeners();
        $eventCls = App::cfg()->getEventRules();

        $funcList = [];
        $_tmp = get_class_methods($eventCls);
        if(!empty($_tmp)) {
            foreach ($_tmp as $funcName) {
                $funcList[strtolower($funcName)] = $funcName;
            }
        }

        if($showEvent){
            $eventList = array_keys($listeners);
            $e = Cli::select($eventList);
            $e = trim(strtolower($eventList[$e]));
            $listeners = isset($listeners[$e])?[$listeners[$e]]:[];
        }
        if(!empty($listeners)) {
            foreach ($listeners as $event => $sub) {

                $cfg = [];
                if (method_exists($eventCls, $event)) {
                    $cfg = $eventCls::$event();
                }

                $this->_print("\n+------------------------------------------\n");
                $this->_print( " " . (isset($funcList[$event]) ? $funcList[$event] : $event) . " :",'0;33');

                $this->_print( "\t" . (empty($cfg) ? '--' : json_encode($cfg)) . "\n",'1;30');

                if(!empty($cfg['actions'])) {
                    $this->_print( "actions:\n",'0;32');
                    $str = implode('\t\n', $cfg['actions']);
                    $this->_print( $str . "\n\n");
                }
                if(!empty($sub)) {
                    $this->_print( "\n\tSubscriber:",'0;32');
                    foreach ($sub as $cls => $func) {
                        $this->_print("\n\t\t" . $cls);//.' > '.$func;
                    }
                }
                $this->_print( "\n");
            }
            $this->_print( "\n+------------------------------------------\n");
        }
    }

    public function show(){
        $this->ls(true);
    }

    public function help(){
        $array = [
            'ls'=>'列出所有事件及监听状态 / List all events and subscription status',
            'show'=>'显示(指定的)事件及监听状态 / View event information and subscription status',
            //'color'=>'Cli模式 是否启用彩色文字 / Output styled information',
            'help'=> '帮助 / Help',
            'daemon'=>'启动事件服务 / Start Service',
        ];
        $keys = array_keys($array);
        $texts = array_values($array);
        $sel = Cli::select($texts);
        if(isset($keys[$sel])){
            $func = $keys[$sel];
            $this->$func();
        }
    }


    /**
     * @param string $cmd ls/help/show/daemon/''
     */
    static public function start($cmd=''){
        $cmd = strtolower(trim($cmd));

        /**
         * @var $daemon self
         */

        $cls = __CLASS__;
        $daemon = new $cls();
        if($cmd==='daemon'){
            $daemon->daemon();
            //echo "5秒后启动监听器守护程序，结束请按 < Ctrl + C >\n";
            //for ($i=5;$i>0;$i--){sleep(1);echo $i."\r"; }
            //sleep(5);
        }elseif(strpos('/ls/event/help/','/'.$cmd.'/')>0){
            $daemon->$cmd();
        }else{
            $daemon->help();
        }
    }
}