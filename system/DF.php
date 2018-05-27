<?php
namespace System;
//Data File
class DF{
    public $path;
    private static $instance;
    private $data = [];
    private $where = [];

    function __construct($path){
        $this->path = $path;
    }

    public static function instance($path = '')
    {
        if (null === static::$instance) {
            static::$instance = new static($path);
        }
        return static::$instance;
    }

    public function where($k, $v=null)
    {
        if(is_array($k)){
            $this->where = array_merge($this->where, $k);
        }

        if($v !== null){
            $this->where[$k] = $v;
        }

        return $this;
    }

    public function query($arr)
    {
        if(count($this->where)){

            $result = [];

            foreach ($arr as $k => $v) {
                $equal = true;

                //where
                if(count($this->where)){
                    foreach ($this->where as $wk => $wv) {
                        if(!isset($v[$wk]) || $v[$wk] != $wv){
                            $equal = false;
                        }
                    }
                }

                if($equal){
                    $result[$k] = $v;
                }
            }

            return $result;
        }

        return $arr;
    }

    public function add($f){
        $f = $this->prepare($f);
        if(file_exists($f)){
            return true;
        }
        return (new FileSystem)->makeFile($f, true)->fwrite('<?php return array();');
    }

    public function get($f)
    {
        if(isset($this->data[$f])){
            return $this->query($this->data[$f]);
        }

        $k = $f;
        $f = $this->prepare($f);
        if(file_exists($f)){
            return $this->query($this->data[$k] =  new Prototype((array) include $f));
        } OS::Errors("$f Not Exists");
    }

    //$f=file, $data=array,$r = bool (replace data)
    public function save($f, array $data, $r=false)
    {
        $f = $this->prepare($f);
        if(file_exists($f) && is_writable($f))
        {
            if((bool)$r === true){
                $fd = $data;
            }else{
                $fd = (array) include $f;
                $fd = array_merge($fd,$data);
            }

            try {
                (new FileSystem)->makeFile($f, true)->fwrite('<?php return ' . var_export($fd, true) . ';');
                return true;
            } catch (Exception $e) {
                return false;
            }
        }else{
            throw new \Exception("File Not found {$f} Or not writable");
        }
    }

    //$f = file ,$del = ['key','key2']
    public function delete($f,array $del){
        $f2 = $this->prepare($f);
        if(file_exists($f2) && is_writable($f2)){
            $fd = (array) include $f2;
            foreach ($del as $v) {
                unset($fd[$v]);
            }
        }
        return $this->save($f,$fd,true);
    }

    private function prepare($f){
        $p  = $this->path.DS;
        $p .= mb_strtolower( str_replace('.',DS, $f) , 'UTF-8').'.php';
        return $p;
    }
}
