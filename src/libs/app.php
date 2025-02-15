<?php
namespace YimoEx\Libs;

//这里实现了简单的表格显示到Console
class App {

    public function showTab($tabData){
        $labs = $tabData[0]; //表格头组
        $counts = count($tabData[0]); //每个项目有多少项
        $this -> log('[Exam] 已获取到考试信息!');

        $console = '';
        foreach($labs as $k => $lab){
            $console .= $lab . ' |';
        }
        $this -> log(trim($console, '|'));
        //输出表格头部

        for($i = 1;$i < $counts;$i++){
            //开始输出每个考试的元信息
            $console = '';
            for($j = 0;$j < 5;$j++){
                $tmp = $tabData[$i][$j];
                if(mb_strlen($tmp) > 12) $tmp = mb_substr($tmp, 0, 12) . '...';
                else if($tmp === '&nbsp;') $tmp = ' ';
                $console .= $tmp . ' |  ';
            }
            $this -> log(trim($console, '|'));
        }
    }

    public function log($message){
        echo $message . "\n";
    }


}