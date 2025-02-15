<?php
namespace YimoEx\Ai;

class Base {
    
    const NAME = '基础模型';

    const API = 'https://api/v1';

    const KEY = 'sk-666';

    public $header = [
        'Authorization Bearer ',
        'Content-Type: application/json'
    ];

    public $model = 'none';

    public $http;

    public function __construct($http){
        $this -> http = $http;
    }

    public function send($message, array $exts = []){
        $temperature = $exts['temperature'] ?? 1.0; 
        $param = json_encode([
            'model' => $this -> model,
            'temperature' => $temperature,
            'messages' => $message
        ]);
        $api = static::API . '/chat/completions';
        return $this -> http -> request($api, [
            'method' => 'POST',
            'headers' => [
                'Authorization' =>  'Bearer ' . static::KEY,
                'Content-Type' => 'application/json'
            ],
            'data' => $param,
        ]);
    }

    public function getName(){
        return static::NAME;
    }
}