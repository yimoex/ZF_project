<?php
namespace YimoEx\Libs;

class Data {

    protected $data = '';

    public function __construct($data){
        $this -> data = $data;
    }

    public function __toString(){
        return $this -> data;
    }

    public static function create($data){
        return new Data($data);
    }

    public function trim($char = "\n\r\t\v\x00"){
        if(!is_string($this -> data)) return $this;
        $this -> data = trim($this -> data, $char);
        return $this;
    }

    public function cut($left, $right = ''){
        if(!is_string($this -> data)) return $this;
        $this -> data = Spider::cut($this -> data, $left, $right);
        return $this;
    }

    public function explode($symbol = ' '){
        if(!is_string($this -> data)) return $this;
        $this -> data = explode($symbol, $this -> data);
        return $this;
    }

    public function explodeByElem($symbol){
        if(!is_string($this -> data)) return $this;
        $this -> data = Spider::elem2array($this -> data, $symbol);
        return $this;
    }

    public function foreach($func){
        if(!is_array($this -> data)) return $this;
        foreach($this -> data as $k => $v){
            $func($k, $this -> data[$k]);
        }
        return $this;
    }

    public function get($key = '*', $default = NULL){
        if($key === '*') return $this -> data;
        return $this -> data[$key] ?? $default;
    }

}
