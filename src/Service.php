<?php


namespace ir\e;

use Exception;
use ir\cli\Cli;
use ReflectionClass;

class Service
{
    private $_timeout = 0;
    private $_enableTimeoutCtrl=false;
    private $_listeners = [];

    private $_colorStyle = true;
    public function __construct($options = [])
    {
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

    private function _runItem(){
        $task = Pool::scan();
        if(empty($task)){
            //无下一页
            return false;
        }
        elseif (!isset($task['id']) || empty($task['id'])){
            $this->_print("\n\n * Invalid TASK ID \n",'1;37',41); echo "\n";
            print_r($task);
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

            $tracking = Pool::getRuntimeTracking($task['id']);//如果上次意外退出，接着上次继续运行
            $progress = $listeners; //记录进度
            $event = new Event($task['name'],$task['args']);

            //执行事件绑定的动作
            $actions = $event->getActions();
            if(!empty($actions)) {
                foreach ($actions as $action) {
                    if(isset($tracking[$action]) && $tracking[$action]) {

                    }else{
                        $r = (new $action($event))->exec();
                        if ($r || is_null($r)) {//null 没有返回值当成功处理
                            Pool::setRuntimeTracking($task['id'],$action,1);//如果上次意外退出，接着上次继续运行
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
                    if ($listenerObj->run()) {
                        Pool::setRuntimeTracking($task['id'],$cls,1);//如果上次意外退出，接着上次继续运行
                        unset($progress[$cls]);
                        $this->_print( "> ok");
                    } else {
                        $this->_print( "> false");
                    }
                }
            }

            if(empty($progress)){
                Pool::remove($task['id']);
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

        while ($status){
            $status = self::_runItem();
            if(!$status){
                echo 'No task 无任务 ['.date('H:i:s')."] \r\n";
                Pool::setMark(time()+60,true);//如果没有新的事件发生，将会在60秒后重试
                while (1){
                    $mark = Pool::getMark();//避免数据库过载
                    if($mark>time()) {
                        sleep(1);
                        echo date('H:i:s')."\r";
                    }else{
                        echo "New Task 发现新任务\r\n";
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

    /**
     * 列出事件 详情
     * @param string $p
     */
    public function ls($p=''){
        $listeners = $this->_getListeners();
        $eventCls = App::cfg()->getEventRules();

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

    public function help($argvs){
        $tpl = implode('  ',$argvs);
        echo "\n";
        $this->_print("--ls",'1;33');
        $this->_printLn("\t\t列出所有事件及监听状态 / List all events and subscription status");
        $this->_print("\t\teg: ",'0;33');
        $this->_printLn('php '.str_replace('--help','--ls',$tpl)."\n",'1;30');

        $this->_print("--event:Test",'1;33');
        $this->_printLn("\t显示Test(指定的)事件及监听状态 / View event information and subscription status");
        $this->_print("\t\teg: ",'0;33');
        $this->_printLn('php '.str_replace('--help','--event:test',$tpl)."\n",'1;30');

        $this->_print("--color:y/n",'1;33');
        $this->_printLn("\tCli模式 是否启用彩色文字 / Output styled information");
        $this->_print("\t\teg: ",'0;33');
        $this->_printLn('php '.str_replace('--help','--color:n',$tpl)."\n",'1;30');

        $this->_print("--help",'1;33');
        $this->_printLn("\t\thelp\n");
    }

    static public function start($argvs=null){
        $argvs = (empty($argvs) && isset($_SERVER['argv'])&& is_array($_SERVER['argv']))? $_SERVER['argv']:$argvs;

        $options = [];
        $len = count($argvs);
        for($i=1;$i<$len;$i++){
            if(strpos($argvs[$i],'--')===0){
                $_tmp = explode(':',$argvs[$i].':');
                $options[strtolower(trim($_tmp[0]))] = trim($_tmp[1]);
            }
        }

        /**
         * @var $daemon self
         */
        $cls = __CLASS__;
        $daemon = new $cls($options);

        if(isset($options['--ls'])){
            $daemon->ls();exit;
        }elseif(isset($options['--event'])){
            $daemon->ls($options['--event']);exit;
        }elseif(isset($options['--help'])){
            $daemon->help($argvs);exit;
        }
        //elseif ($cmd==='....'){}//更多参数
        else {
            $daemon->daemon();
            //echo "5秒后启动监听器守护程序，结束请按 < Ctrl + C >\n";
            //for ($i=5;$i>0;$i--){sleep(1);echo $i."\r"; }

            //sleep(5);

        }

    }
}