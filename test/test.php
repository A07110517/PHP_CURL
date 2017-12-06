<?php
/**
 * Created by PhpStorm.
 * User: asif<1156210983@qq.com>
 * Date: 2017/12/06
 * Time: 16:00
 */

require_once __DIR__ . '/../src/CurlUtil.php';

class Test
{
    public function oneCurl()
    {
        $params = ['url' => 'https://www.baidu.com'];
        $ret = CurlUtil::curl($params, 1, 1);
        print_r($ret);
    }

    public function moreCurl()
    {
        $params = ['baidu1' => ['url' => 'https://www.baidu.com'], 'baidu2' => ['url' => 'https://www.baidu.com']];
        $ret = CurlUtil::multiCurl($params, 1, 1);
        print_r($ret);
    }
}

$test = new Test();
$test->oneCurl();
$test->moreCurl();
