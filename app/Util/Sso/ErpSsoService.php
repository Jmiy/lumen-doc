<?php

/**
 * ERPsso单点登录
 *
 * @ Package  : App\Util\Sso
 * @ Author   : Jmiy
 * @ Version  : 2018-09-11
 *
 */

namespace App\Util\Sso;

class ErpSsoService {

    private $ssoRequestDomain;
    private $ssoBackDomain;
    private $ssoBackUrl = "/ladmin/login";
    private $ssoUrl = "/admin/login?backUrl=";
    private $ssoTicketUrl = "/admin/getUserByTicket/";
    private $ssoLogoutUrl = "/admin/logout?backUrl=";
    private $domain = null;
    private $logoutDomain = null;
    private $ticketUrl = null;

    public function __construct($ssoBackUrl = '') {
        $this->ssoRequestDomain = config('erp.sso');
        $this->ssoBackDomain = $ssoBackUrl ? $ssoBackUrl : url('');
        $this->combineUrl();
    }

    private function combineUrl() {
        $this->domain = $this->ssoRequestDomain .
                $this->ssoUrl .
                $this->ssoBackDomain .
                $this->ssoBackUrl;
        $this->ticketUrl = $this->ssoRequestDomain . $this->ssoTicketUrl;
        $this->logoutDomain = $this->ssoRequestDomain .
                $this->ssoLogoutUrl .
                $this->ssoBackDomain .
                $this->ssoBackUrl;
    }

    /**
     * [getUserByTiket 根据ticket获取用户， 判断用户是否登录]
     * @param  [type] $ticket [获取到客户端传过来的ticket]
     * @return [type]         [通过api获取当前的用户信息]
     */
    public function getUserByTiket($ticket) {
        $url = $this->ticketUrl . $ticket;
        $response = $this->getRequest($url);

        $ret = false;
        if (empty($response['status'])) {
            return $ret;
        }

        $result = (string) $response['message'];
        $info = json_decode($result, true);
        if (!isset($info['success']) || empty($info['success'])) {//如果获取账号失败就退出
            return $ret;
        }

        $ret = $this->updateOrNewUser($info['item']);

        return $ret;
    }

    /**
     * [sendPost 发送网络请求]
     * @param  [type] $url     [description]
     * @param  [type] $data    [description]
     * @param  array  $headers [description]
     * @return [type]          [description]
     */
    protected function getRequest($url, $headers = []) {
        $curlOptions = array(
            CURLOPT_URL => $url, //访问URL
            CURLOPT_HTTPHEADER => $headers, //一个用来设置HTTP头字段的数组。使用如下的形式的数组进行设置： array('Content-type: text/plain', 'Content-length: 100')
            CURLOPT_HEADER => false, //获取返回头信息
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_CONNECTTIMEOUT_MS => 1000 * 5,
            CURLOPT_TIMEOUT_MS => 1000 * 5,
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

    /**
     * [getRequestDomain 获取用户登录的Erp地址]
     * @return [type] [description]
     */
    public function getRequestDomain() {
        return $this->domain;
    }

    /**
     * [getRequestLogoutDomain 获取用户退出的Erp地址]
     * @return [type] [description]
     */
    public function getRequestLogoutDomain() {
        return $this->logoutDomain;
    }

    /**
     * [giveUpOrNew 如果数据库已经存在就更新， 否则新增一条用户记录]
     * @param  [type] $userInfo [description]
     * 参数：  loginID是唯一的
     * @return [type]           [description]
     */

    /**
     * 同步oa账号数据
     * @param array $userInfo array(12) {
     *        ["id"]=>int(2812)//账号
     *        ["loginid"]=>string(12) "yangqingqing"//账号
     *        ["email"]=>string(22) "yangqingqing@stosz.com"
     *        ["deptId"]=>int(497)
     *        ["deptName"]=>string(11) "HN项目部"
     *        ["lastName"]=>string(9) "杨青青"
     *        ["deptNo"]=>string(2) "10"
     *        ["managerId"]=>int(347)
     *        ["companyId"]=>int(1)
     *        ["mobile"]=>string(11) "18846448879"
     *        ["jobs"]=>array(1) {
     *          [0]=>
     *          array(2) {
     *            ["id"]=>
     *            int(85)
     *           ["name"]=>
     *            string(24) "初级广告投放专员"
     *          }
     *        }
     *        ["jobAuthorityRelEnum"]=>string(6) "myself"
     *      }
     * @return array
     */
    private function updateOrNewUser($userInfo) {

        $data = ['id' => $userInfo['id']]; //账号id
        if (isset($userInfo['loginid'])) {//账号
            $data['username'] = $userInfo['loginid'];
        }

        if (isset($userInfo['email'])) {//邮箱
            $data['email'] = $userInfo['email'];
        }

        if (isset($userInfo['deptId'])) {//部门id
            $data['department_id'] = $userInfo['deptId']; //部门id
        }

        if (isset($userInfo['deptName'])) {//部门名称
            $data['department'] = $userInfo['deptName']; //部门
        }

        if (isset($userInfo['lastName'])) {//名字
            $data['name_cn'] = $userInfo['lastName']; //部门
        }

        if (isset($userInfo['managerId'])) {//上级领导id
            $data['manager_id'] = $userInfo['managerId']; //上级领导id
        }

        if (isset($userInfo['mobile'])) {//手机
            $data['mobile'] = $userInfo['mobile']; //手机号码
        }

//        if (isset($userInfo['companyId'])) {//公司id
//            $data['company_id'] = $userInfo['companyId'];//公司id
//        }

        $where = ['id' => $data['id']];

        //\Illuminate\Support\Facades\DB::enableQueryLog();
        $user = \App\Model\Admin::updateOrCreate($where, $data);
        //var_dump(\Illuminate\Support\Facades\DB::getQueryLog());
        return $user->toArray();
    }

}
