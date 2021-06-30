<?php

namespace App\Util\Domain;

class Domain {

    private $domain;        //http://112.95.135.114:8090 正式地址
    private $token;         //domain通信token
    private $appProjectID;  // gomain系统项目ID

    function __construct() {

        $this->domain = config('erp.DOMAIN_URL', "192.168.105.136");
        $this->token = config('erp.DOMAIN_TOKEN', "WkUCdKeVKJHPDF8uROisFnNrOHJcFgIs");
        $this->appProjectID = config('erp.APP_PROJECT_ID', 2);
    }

    function getDomain($params) {
        $token = $this->token;
        $timestamp = time();
        $nonce = $this->getRandomStr(8);
        $uri = $this->domain . "/Home/Api/getDomainDepartment";
        $sign = $this->getSign($token, $timestamp, $nonce);

        $data = [];
        $data['domain'] = $params['domain'];
        $data['timestamp'] = $timestamp;
        $data['nonce'] = $nonce;
        $data['sign'] = $sign;

        $startTime = microtime(true);
        $res = $this->sendPost($uri, $data, $headers = []);
        $apiTime = number_format(microtime(true) - $startTime, 8, '.', '') * 1000;

        $res = json_decode($res['message'], 1);

        if (empty($res['group_name'])) {
            return '';
        } else {
            return $res;
        }
    }

    /**
     * [getRegionDomain 获取邮箱前缀]
     * @param  [type] $domain [description]
     * @return [type]         [description]
     */
    function getRegionDomain($domain) {
        $token = $this->token;
        $timestamp = time();
        $nonce = $this->getRandomStr(8);
        $uri = $this->domain . "/Home/Api/getDomainRegion";
        $sign = $this->getSign($token, $timestamp, $nonce);
        $data = [];
        $data['domain'] = $domain;
        $data['timestamp'] = $timestamp;
        $data['nonce'] = $nonce;
        $data['sign'] = $sign;
        $res = $this->sendPost($uri, $data, $headers = []);

        $res = json_decode($res['message'], 1);
        if (empty($res['user_name'])) {
            return '';
        } else {
            return $res;
        }
    }

    /**
     * [getSeoDomain 获取用户权限内的域名]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    function getSeoDomain($params) {
        $token = $this->token;
        $timestamp = time();
        $nonce = $this->getRandomStr(8);
        $uri = $this->domain . "/Home/Api/getSeoDomains";
        $sign = $this->getSign($token, $timestamp, $nonce);

        $data = [];
        $data['loginid'] = $params['loginid'];
        $data['id_department'] = $params['id_department'];
        $data['timestamp'] = $timestamp;
        $data['nonce'] = $nonce;

        // $data['project_id'] = \lib\register::getInstance('config')->get('domainProjectId');
        $data['project_id'] = $this->appProjectID;

        $data['sign'] = $sign;
        $res = $this->sendPost($uri, $data, []);

        if ($res['status'] == 1) {
            $res = json_decode($res['message'], 1);
        }

        if (isset($res['ret'])) {
            return $res;
        } else {
            return [];
        }
    }

    /**
     * 生成随机字符串
     * @param $length
     * @return string
     */
    protected function getRandomStr($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * [getSign 与domain系统通信加密]
     * @param  [type] $token     [description]
     * @param  [type] $timestamp [description]
     * @param  [type] $nonce     [description]
     * @return [type]            [description]
     */
    function getSign($token, $timestamp, $nonce) {
        return md5($token . $timestamp . $nonce);
    }

    /**
     * [sendPost 发送网络请求]
     * @param  [type] $url     [description]
     * @param  [type] $data    [description]
     * @param  array  $headers [description]
     * @return [type]          [description]
     */
    protected function sendPost($url, $data, $headers = []) {
        $curlOptions = array(
            CURLOPT_URL => $url, //访问URL
            CURLOPT_HTTPHEADER => $headers, //一个用来设置HTTP头字段的数组。使用如下的形式的数组进行设置： array('Content-type: text/plain', 'Content-length: 100')
            CURLOPT_HEADER => false, //获取返回头信息
            CURLOPT_POST => true, //发送时带有POST参数
            CURLOPT_POSTFIELDS => $data, //请求的POST参数字符串 全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串类似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组。如果value是一个数组，Content-Type头将会被设置成multipart/form-data。
            CURLOPT_CONNECTTIMEOUT_MS => 1000 * 10,
            CURLOPT_TIMEOUT_MS => 1000 * 10,
        );

        /* 获取响应信息并验证结果 */
        $responseData = \App\Util\Curl::handle($curlOptions, true); //

        $retdata['status'] = 1;
        $retdata['message'] = $responseData['responseText'];
        if ($responseData['responseText'] === false || $responseData['curlInfo']['http_code'] != 200) {
            $retdata['status'] = 0;
            $retdata['message'] = $responseData['errmsg'];
        }

        return $retdata;
    }

}
