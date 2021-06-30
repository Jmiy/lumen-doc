<?php

namespace App\Util;

use App\Processes\ProcessManager;
use Carbon\Carbon;
use App\Services\ResponseLogService;
use Illuminate\Support\Arr;
use Validator;

class Response {

    /**
     * 获取统一响应数据结构
     * @param array $data 响应数据
     * @param boolean $isNeedDataKey
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return array 统一响应数据结构
     */
    public static function getResponseData($data = [], $isNeedDataKey = true, $status = 200, array $headers = [], $options = 0) {
        return [
            data_get($data, Constant::RESPONSE_DATA_KEY, Constant::PARAMETER_ARRAY_DEFAULT),
            data_get($data, Constant::RESPONSE_CODE_KEY, Constant::PARAMETER_INT_DEFAULT),
            data_get($data, Constant::RESPONSE_MSG_KEY, Constant::PARAMETER_STRING_DEFAULT),
            $isNeedDataKey,
            $status,
            $headers,
            $options,
        ];
    }

    /**
     * 获取  图片 资源地址
     * @param String $imgUrl
     * @param int $is_https 1：强制用https 0:不强制用https
     * @return \Illuminate\Http\JsonResponse
     */
    public static function json($data = [], $code = 1, $msg = 'ok', $isNeedDataKey = true, $status = 200, array $headers = [], $options = 0) {

        $request = app('request');
        $storeId = $request->input('store_id', 0);
        $actId = $request->input('act_id', 0);

        $result = [
            Constant::RESPONSE_EXE_TIME => Constant::PARAMETER_INT_DEFAULT,
            Constant::RESPONSE_CODE_KEY => $code,
            Constant::RESPONSE_MSG_KEY => static::getResponseMsg($storeId, $code, $msg),
            //'cpu_num' => swoole_cpu_num(),
//            'server' => $request->server->all(),
//            'headers' => $request->headers->all(),
//            'cookies' => $request->cookies->all(),
//            'getClientIps' => $request->getClientIps(),
//            'getClientIp' => $request->getClientIp(),
//            'getenv' => getenv(),
        ];

        if ($isNeedDataKey) {
            $result[Constant::RESPONSE_DATA_KEY] = $data;
        } else {
            $result = array_merge($result, $data);
        }

        //dump(constant("APP_START"));//, APP_START
        //$result[Constant::RESPONSE_EXE_TIME] = (number_format(microtime(true) - constant("APP_START"), 8, '.', '') * 1000) . ' ms'; //$request->input('APP_START', 0)  constant("APP_START")
        $result[Constant::RESPONSE_EXE_TIME] = (number_format(microtime(true) - $request->server->get('REQUEST_TIME_FLOAT', 0), 8, '.', '') * 1000) . ' ms'; //(defined('APP_START') ? APP_START : $request->server->get('REQUEST_TIME_FLOAT')) $request->input('APP_START', 0) $request->input('APP_START', 0)  constant("APP_START")

        try {

            $routeInfo = $request->route();
            $requestData = $request->all();

            $noLog = data_get($routeInfo, '1.noLog', []); /* @var $validatorData array */
            $noLog = Arr::collapse([$noLog, array_keys($request->file())]);//$_FILES
            if ($noLog) {
                foreach ($noLog as $key) {
                    if (isset($requestData[$key])) {
                        unset($requestData[$key]);
                    }
                }
            }

            data_set($requestData, 'responseData', $result);
            data_set($requestData, 'responseData.status', $status);
            data_set($requestData, 'responseData.headers', $headers);
            data_set($requestData, 'responseData.options', $options);

            //FunctionHelper::setTimezone($storeId);

            $action = $request->input('account_action', data_get($routeInfo, '1.account_action', ''));
            $fromUrl = $request->input('client_access_url', ($request->headers->get('Referer') ?? 'no'));
            $account = $request->input('account', $request->input('help_account', $request->input('operator', '')));
            $cookies = $request->input('account_cookies', '');
            $ip = $request->input('ip', '');
            $apiUrl = $request->getRequestUri();
            $createdAt = data_get($requestData, 'created_at', Carbon::now()->toDateTimeString());
            $extId = $request->input('id', 0);
            $extType = $request->input('ext_type', '');
            $parameters = [$action, $storeId, $actId, $fromUrl, $account, $cookies, $ip, $apiUrl, $createdAt, $extId, $extType, $requestData];

            $queueConnection = config('queue.log_queue_connection');
            $extData = [
                'queueConnectionName' => $queueConnection,//Queue Connection
                'queue' => config('queue.connections.' . $queueConnection . '.queue'),//Queue Name
                //'delay' => 1,//任务延迟执行时间  单位：秒
            ];

            $logTaskData = FunctionHelper::getJobData(ResponseLogService::getNamespaceClass(), 'addResponseLog', $parameters, null, $extData);
            $taskData = [
                $logTaskData,
            ];

            $isDeliverTask = false;
            $illunminateProcessData = [Constant::PROCESS_PLATFORM_ILLUMINATE, 'Base', 'write', [$taskData, 'baseProcess']];
            if ($request->has(['post_key', 'get_key'])) {//如果是swoole service 模式运行，就将记录请求日志的任务放到BaseProcess进程中运行
                $isDeliverTask = ProcessManager::handle(Constant::PROCESS_PLATFORM, 'Base', 'write', [$illunminateProcessData, 'baseProcess']);
            }

            if (!$isDeliverTask) {//如果记录请求日志的任务放入BaseProcess进程失败，就使用消息队列异步执行
                ProcessManager::handle(...$illunminateProcessData);
            }

        } catch (\Exception $exc) {
            //echo $exc->getTraceAsString();
        }

        if(env('DB_DEBUG', false)){
            $result['db_debug'] = data_get($requestData, 'db_debug');
        }

        return response()->json($result, $status, $headers, $options);
    }

    /**
     * 获取默认的响应数据结构
     * @param int $code 响应状态码
     * @param string $msg 响应提示
     * @param array $data 响应数据
     * @return array $data
     */
    public static function getDefaultResponseData($code = Constant::PARAMETER_INT_DEFAULT, $msg = null, $data = Constant::PARAMETER_ARRAY_DEFAULT) {
        return [
            Constant::RESPONSE_CODE_KEY => $code,
            Constant::RESPONSE_MSG_KEY => $msg,
            Constant::RESPONSE_DATA_KEY => $data,
        ];
    }

    /**
     * 获取响应提示
     * @param int $storeId 品牌商店id
     * @param int $code 响应状态码
     * @param string $msg 响应提示 默认：使用系统提示
     * @return string 响应提示
     */
    public static function getResponseMsg($storeId, $code, $msg = null) {

        if (!empty($msg)) {
            return $msg;
        }

        $field = PublicValidator::getAttributeName($storeId, $code);
        $validatorData = [
            $field => '',
        ];
        $rules = [
            $field => ['api_code_msg'],
        ];

        $validator = Validator::make($validatorData, $rules);
        $errors = $validator->errors();
        foreach ($rules as $key => $value) {
            if ($errors->has($key)) {
                return $errors->first($key);
            }
        }

        return '';
    }

}
