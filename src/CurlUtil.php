<?php
/**
 * Created by PhpStorm.
 * User: asif<1156210983@qq.com>
 * Date: 2017/12/06
 * Time: 15:33
 */
class CurlUtil
{
    /**
     * 发送curl请求
     *
     * @param $array
     * @param $timeoutLimit
     * @param $connectTimeoutLimit
     * @return mixed
     */
    public static function curl($array, $timeoutLimit = 1, $connectTimeoutLimit = 1)
    {
        $ch = curl_init();
        $url = $array['url'];
        $isHttps = false;
        if(substr($url,0,5)=='https'){
            $isHttps = true;
        }
        curl_setopt($ch, CURLOPT_PROXY, $array['proxy']);
        if(strpos(strtolower($array['proxy_type']), 'so') !== false){
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }else{
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }
        curl_setopt($ch, CURLOPT_URL, $array['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutLimit);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeoutLimit);

        if (isset($array['header']) && $array['header']) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }
        if (isset($array['httpheader'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $array['httpheader']);
        }
        if (isset($array['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $array['referer']);
        }
        if (isset($array['post'])) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $array['post']);
        }
        if (isset($array['followlocation'])) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $array['followlocation']);
        }
        if (isset($array['cookiefile'])) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $array['cookiefile']);
        }
        if (isset($array['cookiejar'])) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $array['cookiejar']);
        }
        if (isset($array['useragent'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $array['useragent']);
        }
        // 增加https的。
        if ($isHttps) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        }
        $html = curl_exec($ch);
        $r['erro'] = curl_error($ch);
        $r['errno'] = curl_errno($ch);
        $r['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $r['html'] = $html;
        curl_close($ch);
        return $r;
    }

    /**
     * 并发curl请求
     *
     * @param $params
     * @param $timeoutLimit
     * @param $connectTimeoutLimit
     */
    public static function multiCurl($params, $timeoutLimit = 1, $connectTimeoutLimit = 1)
    {
        $mch = curl_multi_init();
        $connArr = [];
        foreach ($params as $key => $val) {
            $url = $val['url'];
            $connArr[$key] = curl_init($url);
            //设置参数 start
            if(substr($url,0,5)=='https'){
                $isHttps = true;
            }
            curl_setopt($connArr[$key], CURLOPT_PROXY, $val['proxy']);
            if(strpos(strtolower($val['proxy_type']), 'so') !== false){
                curl_setopt($connArr[$key], CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            }else{
                curl_setopt($connArr[$key], CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            }
//            curl_setopt($connArr[$key], CURLOPT_URL, $val['url']);
            curl_setopt($connArr[$key], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($connArr[$key], CURLOPT_TIMEOUT, $timeoutLimit);
            curl_setopt($connArr[$key], CURLOPT_CONNECTTIMEOUT, $connectTimeoutLimit);

            if (isset($val['header']) && $val['header']) {
                curl_setopt($connArr[$key], CURLOPT_HEADER, 1);
            }
            if (isset($val['httpheader'])) {
                curl_setopt($connArr[$key], CURLOPT_HTTPHEADER, $val['httpheader']);
            }
            if (isset($val['referer'])) {
                curl_setopt($connArr[$key], CURLOPT_REFERER, $val['referer']);
            }
            if (isset($val['post'])) {
                curl_setopt($connArr[$key], CURLOPT_POST, 1);
                curl_setopt($connArr[$key], CURLOPT_POSTFIELDS, $val['post']);
            }
            if (isset($val['followlocation'])) {
                curl_setopt($connArr[$key], CURLOPT_FOLLOWLOCATION, $val['followlocation']);
            }
            if (isset($val['cookiefile'])) {
                curl_setopt($connArr[$key], CURLOPT_COOKIEFILE, $val['cookiefile']);
            }
            if (isset($val['cookiejar'])) {
                curl_setopt($connArr[$key], CURLOPT_COOKIEJAR, $val['cookiejar']);
            }
            if (isset($val['useragent'])) {
                curl_setopt($connArr[$key], CURLOPT_USERAGENT, $val['useragent']);
            }
            // 增加https的。
            if ($isHttps) {
                curl_setopt($connArr[$key], CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
                curl_setopt($connArr[$key], CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
            }
            //设置参数 end
            curl_multi_add_handle ($mch, $connArr[$key]); // 添加线程
        }

        //执行线程
        do {
            $mrc = curl_multi_exec($mch,$active);
        }while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active and $mrc == CURLM_OK) {
            if (curl_multi_select($mch) != -1) {
                do {
                    $mrc = curl_multi_exec($mch, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        //搜集curl信息
        $arrInfo = [];
        foreach ($params as $i => $url) {
            $arrInfo[$i]['html'] = curl_multi_getcontent($connArr[$i]);
            $curlInfo = curl_getinfo($connArr[$i]);
            $arrInfo[$i]['total_time'] = $curlInfo['total_time'];
            $arrInfo[$i]['http_code'] = $curlInfo['http_code'];
            curl_multi_remove_handle($mch, $connArr[$i]);
            curl_close($connArr[$i]);
        }
        curl_multi_close($mch);

        return $arrInfo;
    }
}
