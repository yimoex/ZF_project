<?php
use Workerman\Worker;
use YimoEx\Libs\Zf;
use YimoEx\Libs\App;

require_once __DIR__ . '/vendor/autoload.php';
include 'yimoEx/ai/base.php';
include 'yimoEx/ai/models/kimi.php';

include 'yimoEx/libs/data.php';
include 'yimoEx/libs/spider.php';
include 'yimoEx/libs/zf.php';
include 'yimoEx/libs/app.php';

$worker = new Worker();
$worker -> onWorkerStart = function () {
    $zf = new Zf('123'); //这里填学号
    $app = new App();
    $rec = $zf -> login('密码');
    $res = $zf -> getExam();
    $app -> showTab($res);
};
Worker::runAll();