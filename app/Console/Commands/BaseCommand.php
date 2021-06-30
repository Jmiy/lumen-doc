<?php

namespace App\Console\Commands;

use App\Util\Constant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use App\Util\FunctionHelper;
use App\Util\Response;
use App\Jobs\RequestLogJob;

class BaseCommand extends Command {

    /**
     * 处理请求
     * @param type $storeId
     */
    public function handleRequest($storeId = 0) {

        $requestData = \Illuminate\Support\Arr::collapse([$this->argument(), $this->option()]);

        $headerData = [];
        $requestMark = FunctionHelper::randomStr(10);
        data_set($requestData, 'headerData', $headerData);
        data_set($requestData, 'request_mark', $requestMark);

        $deviceType = 1;
        $agent = app('agent');
        switch (true) {
            case $agent->isMobile():
                $deviceType = 1;

                break;

            case $agent->isTablet():
                $deviceType = 2;

                break;

            case $agent->isDesktop():
                $deviceType = 3;

                break;

            default:
                break;
        }

        $isRobot = $agent->isRobot() ? 1 : 0;
        $languages = $agent->languages();
        $clientData = [
            Constant::DEVICE => $agent->device(), //设备信息
            Constant::DEVICE_TYPE => $deviceType, // 设备类型 1:手机 2：平板 3：桌面
            Constant::DB_TABLE_PLATFORM => $agent->platform(), //系统信息
            Constant::PLATFORM_VERSION => $agent->version($agent->platform()), //系统版本
            Constant::BROWSER => $agent->browser(), // 浏览器信息  (Chrome, IE, Safari, Firefox, ...)
            Constant::BROWSER_VERSION => $agent->version($agent->browser()), // 浏览器版本
            Constant::LANGUAGES => is_array($languages) ? json_encode($languages, JSON_UNESCAPED_UNICODE) : $languages, // 语言 ['nl-nl', 'nl', 'en-us', 'en']
            Constant::IS_ROBOT => $isRobot, //是否是机器人
            Constant::DB_TABLE_UPDATED_MARK => $requestMark,//请求标识
        ];
        data_set($requestData, Constant::CLIENT_DATA, $clientData);

        $service = '\App\Services\LogService';
        $method = 'addAccessLog';
        $action = 'console';
        $fromUrl = 'no';
        $account = '';
        $cookies = '';
        $ip = FunctionHelper::getClientIP();
        $apiUrl = static::class . '::' . __FUNCTION__;
        $createdAt = '';
        $extId = 0;
        $extType = '';
        $actId = 0;
        $parameters = [$action, $storeId, $actId, $fromUrl, $account, $cookies, $ip, $apiUrl, $createdAt, $extId, $extType, $requestData];
        $logData = [
            'apiUrl' => $apiUrl,
            'storeId' => $storeId,
            'account' => $account,
            'createdAt' => $createdAt,
            'handleData' => [
                [
                    'service' => $service,
                    'method' => $method,
                    'parameters' => $parameters,
                ],
            ]
        ];
        Queue::push(new RequestLogJob($logData)); //记录接口请求日志

        $request = app('request');
        $request->offsetSet('store_id', $storeId);
        $request->offsetSet('app_env', (data_get($requestData, 'appEnv') ? data_get($requestData, 'appEnv') : null));
        $request->offsetSet('account', '');
        $request->offsetSet('cookies', '');
        $request->offsetSet('ip', $ip);
        $request->offsetSet('act_id', $actId);

        foreach ($requestData as $key => $value) {
            $request->offsetSet($key, $value);
        }
    }

    /**
     * 处理响应
     */
    public function handleResponse() {
        Response::json([]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        define('APP_START', microtime(true));
        return $this->runHandle();
    }

}
