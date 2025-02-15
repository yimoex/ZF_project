<?php
namespace YimoEx\Libs;

class Spider {

    public static function cut(string $string, string $left, string $right){
        $start = stripos($string, $left);
        $end = stripos($string, $right, $start);
        $start += strlen($left);
        return substr($string, $start, $end - $start);
    }

    public static function elem2array(string $data, string $symbol){
        $symbol = '</' . $symbol . '>';
        $res = explode($symbol, $data);
        $result = [];
        foreach($res as $rec){
            if($rec == NULL) continue;
            $pos = stripos($rec, '<' . $symbol);
            $start = stripos($rec, '>', $pos);
            $start += 1; //+1是因为>符号
            $result[] = trim(substr($rec, $start));
        }
        return $result;
    }

}
