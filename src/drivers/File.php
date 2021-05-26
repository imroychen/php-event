<?php


namespace ir\e\drivers;

/**
 * File 驱动
 * File?path=/your/path
 * @package ir\e\drivers
 * @example new File('path=...')
 */
class File extends Driver
{
    private $_path='';
    protected $_dataset, $_dataset_bak;
    protected $_redis;


    /**
     * Redis constructor.
     * @param array $args
     * @param string $rawArgs 'path='
     */
    protected function _init($args,$rawArgs)
    {
        $this->_path = $args['path'];
        if(!is_dir($this->_path)){
            mkdir($this->_path,0777,true);
            chmod($this->_path,0777);

            $indexFile = $this->_file('@index');
            if(!file_exists($indexFile)){
                file_put_contents($indexFile,'');
                chmod($indexFile,0777);
            }
        }
    }

    private function _file($id){
        if($id==='@index'){
            return $this->_path.DIRECTORY_SEPARATOR.'index';
        }
        return $this->_path.DIRECTORY_SEPARATOR.$id[0].DIRECTORY_SEPARATOR.$id;
    }

    /**
     * @param $id
     * @return mixed false|['字段名'=>'字段值', ...]
     */
    public function get($id)
    {
        $f = $this->_file($id);
        if(file_exists($f)) {
            $c = file_get_contents($f);
            $task = unserialize($c);
            $task['id'] = $id;
            return $task;
        }
        return false;
    }

    /**
     * 移除事件监听器动作
     * @param string $id
     * @return bool
     */

    public function remove($id)
    {
        $idxFile = $this->_file('@index');

        $f = $this->_file($id);
        if(file_exists($f)) {
            unlink($f);
        }

        $content = file_get_contents($idxFile);
        $content = preg_replace('/(^|\n)'.$id.'\,\d*?\|\s*/','$1',$content);
        file_put_contents($idxFile,$content);
        return true;
    }

    /**
     * 插入事件监听器动作到池中
     * @param array $data [
     *        'name'=>'',
     *        'sign'=>'',
     *
     *        //'listener'=>'',     *
     *        'dependency'=>'',
     *        'cfg'=>[]
     *    ];
     * @return int
     */
    public function create($data)
    {
        $id = $data['sign'];

        $data['id'] = $id;
        $data['args'] = array_merge(['args'=>[]],$data);
        $dataStr = serialize($data);

        $fIdx = $this->_file('@index');
        $startTime = (isset($data['starting_time']) && $data['starting_time']>0)? ($data['starting_time']+0): 1;

        $f = $this->_file($id);
        $dir = dirname($f);

        if(!is_dir($dir)){
            mkdir($dir,0777,true);
            chmod($dir,0777);
        }
        file_put_contents($f,$dataStr);
        if(strpos(file_get_contents($fIdx),$id)===false) {
            file_put_contents($fIdx, "\n$id,$startTime|" , FILE_APPEND);
        }
        return $id;
    }


    /**
     * 检查事件监听器动作否存在
     * @param $sign
     * @return  mixed false|id
     */

    public function isExist($sign)
    {
        $id = $sign;
        $f = $this->_file($id);
        if(file_exists($f)){
            return $id;
        }
        return false;
    }

    /**
     * 暂停事件监听器动作
     * @param string $id
     * @param int $time 时间戳
     * @return bool
     */

    public function setStartingTime($id, $time)
    {
        $fileIdx = $this->_file('@index');
        $content = preg_replace('/(^|\n)'.$id.'\,\d*?\|/',$id.','.$time.'|',file_get_contents($fileIdx));
        return file_put_contents($fileIdx,$content);
    }

    /**
     * 扫描可运行的任务
     */

    public function  scan()
    {
        $fileIdx = $this->_file('@index');
        $content = file_get_contents($fileIdx);
        $tmp = explode("\n",$content);
        $time = time();
        //var_export($tmp);
        foreach ($tmp as $v ){
            if($v!='') {
                list($v) = explode('|', $v);
                list($id, $execTime) = explode(',', $v.',1');
                //var_export($id);var_export($v);echo "\r\n";
                if ($execTime < $time) {
                    $r = $this->get($id);
                    if(!$r && !file_exists($this->_file($id))){
                        $this->remove($id);//无效任务
                    }
                    return $r;
                }
            }
        }
        return false;
    }
    
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
}