<?php

namespace App\Util\Wx;

class WxManager {

    /**
     * 登录 https://developers.weixin.qq.com/miniprogram/dev/api/open-api/login/code2Session.html
     * @param string $code 登录凭证 通过 wx.login() 接口获得临时登录凭证
     * @return array 登录结果
     */
    public static function login($code) {

        //appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code
        $data = [
            'appid' => config('wx.appId', ''),
            'secret' => config('wx.appSecret', ''),
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $url = config('wx.code2session', '') . '?' . http_build_query($data);
        $curlOptions = array(
            CURLOPT_URL => $url, //访问URL
            CURLOPT_CUSTOMREQUEST => 'GET', //get请求
            CURLOPT_CONNECTTIMEOUT_MS => 1000 * 10,
            CURLOPT_TIMEOUT_MS => 1000 * 10,
        );

        /**
         * 获取响应信息并验证结果 
         * 正确响应：{"session_key":"8DJR6gLklKvFsuvd4PxMQQ==","openid":"oQtpG421bawl_c5D66sZkr0Yu7_A"}
         * 错误响应：{"errcode":40163,"errmsg":"code been used, hints: [ req_id: go.kQA00393119 ]"}
         */
        $responseData = \App\Util\Curl::handle($curlOptions, true); //
        if ($responseData['responseText'] == false) {
            return ['code' => 1, 'msg' => '登录失败', 'data' => $responseData['responseText'], 'api_response' => $responseData];
        }

        $data = json_decode($responseData['responseText'], true);
        if (isset($data['errcode'])) {
//            return ['code' => 2, 'msg' => '登录数据失效，登录失败', 'data' => $data]; //正式使用这个程序 , 'api_response' => $responseData
            $data = '{"session_key":"8DJR6gLklKvFsuvd4PxMQQ==","openid":"oQtpG421bawl_c5D66sZkr0Yu7_A"}'; //测试
            $data = json_decode($data, true);
        }

        return ['code' => 0, 'msg' => '登录成功', 'data' => $data];
    }

    public static function getUserInfo($wxData) {
        $appid = 'wx4f4bc4dec97d474b';
        $sessionKey = 'tiihtNczf5v6AKRyjwEUhQ==';
//        $appid = config('wx.appId', '');//小程序的appid
//        $sessionKey = $wxData['userData']['session_key'];//用户在小程序登录后获取的会话密钥
        $iv = $wxData['iv']; //与用户数据一同返回的初始向量
        $encryptedData = $wxData['encryptedData']; //加密的用户数据

        $pc = new WXBizDataCrypt($appid, $sessionKey);
        $data = [];
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

//        if ($errCode == 0) {
//            print($data . "\n");
//        } else {
//            print($errCode . "\n");
//        }

        $data = $errCode == 0 ? json_decode($data, true) : [];
        return ['code' => $errCode, 'data' => $data, 'msg' => ErrorCode::getErrorMsg($errCode)];
    }

}
