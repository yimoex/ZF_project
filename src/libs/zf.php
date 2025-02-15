<?php
namespace YimoEx\Libs;

use YimoEx\Ai\Models\Kimi;
use YimoEx\Libs\Data;

class Zf {

    const API = 'https://';

    private $http;

    public $id;
    public $name;

    public $cookie = '';
    public $mainViewState = '';

    private $status = false;
    protected $model;

    protected $_headers = [
        'Referer' => self::API . '/xs_main.aspx?xh=',
        'connection' => 'keep-alive',
    ];

    public function __construct(int $id){
        $this -> http = new \Workerman\Http\Client();
        $this -> model = new Kimi($this -> http);
        $this -> id = $id;
        $rec = $this -> cacheLoader($id);
        if($rec){
            $this -> status = true;
        }else{
            $this -> mainViewState = $this -> getViewState('/default2.aspx');
        }
    }

    public function getViewState($address, $more = NULL, $exts = []){
        $query = [
            'xh' => $this -> id,
        ];
        if($more != NULL){
            $query['xm'] = $this -> name;
            $query['gnmkdm'] = $more;
        }
        $headers = isset($exts['header']) ? $exts['header'] : [
            'Referer' => $this -> _headers['Referer'],
            'Cookie' => $this -> cookie
        ];
        $res = $this -> http -> request(self::API . $address . '?' . http_build_query($query), [
            'method' => 'GET',
            'headers' => $headers
        ]);
        $left = 'name="__VIEWSTATE" value="';
        $right = '" />';
        return Spider::cut($res -> getBody(), $left, $right);
    }

    //获取考试信息
    public function getExam(){
        //$view = $this -> getViewState("/xskscx.aspx?xh={$this -> id}&xm={$this -> name}&gnmkdm=N121603");
        //该功能不需要view
        $rec = (new Data($this -> request('/xskscx.aspx', 'N121603')[0]))
        -> cut(
            '<table class="datelist" cellspacing="0" cellpadding="3" border="0" id="DataGrid1" width="100%">',
            '</table>'
        )
        -> trim() 
        -> explodeByElem('tr')
        -> foreach(function($key, &$value){
            $value = Spider::elem2array($value, 'td');
        })
        -> get('*');
        if($rec[0][0] != '选课课号') return false;
        return $rec;
    }

    //期末成绩
    public function getExamGrade(){
        $module = 'N121604';
        $uri = '/xscj.aspx';
        $view = $this -> getViewState($uri, $module, [
            'Referer' => self::API . $uri . '?'  . $this -> buildUriQuery($module)
        ]);
        //该功能不需要view
        $rec = (new Data($this -> request($uri, $module, [
            '__VIEWSTATE' => $view,
            'ddlXN'   => '',
            'ddlXQ'   => '',
            'txtQSCJ' => 0,
            'txtZZCJ' => 100,
            'Button2' => '在校学习成绩查询',
        ])[0]))
        -> cut(
            '<table class="datelist" cellspacing="0" cellpadding="3" border="0" id="DataGrid1" width="100%">',
            '</table>'
        )
        -> trim()
        -> explodeByElem('tr')
        -> foreach(function($key, &$value){
            $value = Spider::elem2array($value, 'td');
        })
        -> get('*');
        if($rec[0][0] != '课程代码') return false;
        return $rec;
    }

    public function getCaptach(){        
        $response = $this -> http -> get(self::API . '/CheckCode.aspx?t=' . time());
        $cookieRaw = $response -> getHeader('set-cookie')[0] ?? '';
        $this -> cookie = strstr($cookieRaw, ';', true);

        $first = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
        $data = (string)$response -> getBody();
        $image = strstr($data, $first, true);

        return $image;
    }

    public function resolveCaptach(){
        $image = $this -> getCaptach();
        $param = [
            [
                'role' => 'system',
                'content' => '你现在是一个验证码分析者，你只会输出验证码的值!'
            ],[
                'role' => 'user',
                'content' => [
                    [
                        "type" => "image_url",
                        "image_url" => ["url" => 'data:image/png;base64,' . base64_encode($image)]
                    ],[
                        "type" => "text",
                        "text" => '请输出该图片的四位值'
                    ]
                ]
            ]
        ];
        $res = $this -> model -> send($param);
        $rec = json_decode($res -> getBody(), true)['choices'][0]['message']['content'] ?? '';
        return $rec;
    }

    public function resolveCaptachHuman(){
        $image = $this -> getCaptach();
        file_put_contents('test.png', $image);
        printf("input: ");
        return trim(fgets(STDIN));
    }

    public function checkStatus($id){
        $url = self::API . '/xs_main.aspx?xh=' . $id;
        list($data, $info) = $this -> curl($url, []);
        if(!stripos($data, '同学')) return false;
        $this -> name = Spider::cut($data, '<span id="xhxm">', '同学</span></em>');
        $this -> log("[System] 欢迎回来! [{$this -> name}]");
        return true;
    }

    public function login($passwd){
        if($this -> status) return true;
        $code = $this -> resolveCaptach();

        $param = [
            '__VIEWSTATE' => $this -> mainViewState,
            'txtUserName' => $this -> id,
            'TextBox2' => $passwd,
            'txtSecretCode' => $code,
            'RadioButtonList1' => '学生',
            'Button1' => '',
            'lbLanguage' => '',
            'hidPdrs' => '',
            'hidsc' => '',
        ];
        list($data, $info) = $this -> curl(self::API . '/default2.aspx', [
            'postData' => $param,
        ]);
        if(!isset($info['redirect_url'])){
            return false;
        }
        $new = $info['redirect_url'];
        $checker = $this -> curl($new, []);
        $this -> name = $name = Spider::cut($checker[0], '<span id="xhxm">', '同学</span></em>');
        if(!$name){
            $this -> log("[System-Error] 发生错误 -> 无法找到名字!");
            return false;
        }
        $this -> log("[System] 欢迎! [{$name}]");
        $this -> cacheWriter($this -> id, $this -> cookie);
        return true;
    }

    public function log($message){
        echo $message . "\n";
    }

    public function request($address, $module, $param = []){
        return $this -> curl(self::API . $address . '?' . $this -> buildUriQuery($module), [
            'header' => true,
            'postData' => $param != NULL ? $param : false
        ]);
    }

    public function buildUriQuery($module){
        return http_build_query([
            'xh' => $this -> id,
            'xm' => $this -> name,
            'gnmkdm' => $module,
        ]);
    }

    public function curl(string $address, array $options = []){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $address);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(isset($options['header']) && $options['header']){
            $this -> _headers['Referer'] .= $this -> id;
            $newHeader = [];
            foreach($this -> _headers as $header => $value){
                $newHeader[] = $header . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $newHeader);
        }
        if(isset($options['postData']) && is_array($options['postData'])){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['postData']);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_COOKIE, $this -> cookie);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $buffer = iconv('GB2312', 'UTF-8', curl_exec($ch)); //转换编码
    
        $info = curl_getinfo($ch);
        curl_close($ch);
        return [$buffer, $info];
    }

    public function cacheLoader(string $id){
        $file = 'token_' . $id . '.cache';
        if(!is_file($file)) return false;
        $this -> cookie = file_get_contents($file);
        if(!$this -> checkStatus($id)) return false;
        return true;
    }

    public function cacheWriter(string $id, string $session = ''){
        $file = 'token_' . $id . '.cache';
        return file_put_contents($file, $session);
    }

}