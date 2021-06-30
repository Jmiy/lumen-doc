<?php

namespace App\Http\Middleware;

use Closure;
use App\Util\FunctionHelper;
use Carbon\Carbon;
use App\Jobs\RequestLogJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Arr;
use App\Util\Constant;
use App\Services\ReportLogService;
use App\Services\CustomerInfoService;
use App\Tasks\TaskManager;
use App\Processes\ProcessManager;

class RequestMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        if ($request->has('isRequestLog')) {//如果已经记录过请求流水，就直接进入下一步请求
            return $next($request);
        }

        $routeInfo = $request->route();
        $routeParameters = data_get($routeInfo, 2, Constant::PARAMETER_ARRAY_DEFAULT);//获取通过路由传递的参数
        $requestData = $request->all();
        foreach ($routeParameters as $routeKey => $routeParameter) {
            if (!(Arr::has($requestData, $routeKey))) {//如果 input 请求参数没有 $routeKey 对应的参数，就将 $routeKey 对应的参数设置到 input 参数中以便后续统一通过 input 获取
                if ($routeKey == 'data') {
                    $_data = decrypt($routeParameter);
                    $_data = json_decode($_data, true);
                    foreach ($_data as $key => $value) {
                        $request->offsetSet($key, $value);
                    }
                } else {
                    $request->offsetSet($routeKey, $routeParameter);
                }
            }
        }

        if ($request->has('email') && !$request->has(Constant::DB_TABLE_ACCOUNT)) {
            $request->offsetSet(Constant::DB_TABLE_ACCOUNT, $request->input('email', ''));
        }

        if (FunctionHelper::getClientIP() == '47.254.95.132' && $request->has(Constant::DB_TABLE_ACCOUNT)) {//如果是mpow社区过来的请求，账号统一清空账号的换行符和前后空格
            $request->offsetSet(Constant::DB_TABLE_ACCOUNT, trim($request->input(Constant::DB_TABLE_ACCOUNT, ''))); //清空账号的换行符
        }

        /**
         * 订单是否存在接口 使用 order_no 作为订单key，请求流水和响应流水 是从 orderno 获取订单号的，
         * 所以当且仅当有 order_no 没有 orderno 时，就使用order_no 作为 orderno的值
         */
        if ($request->has('order_no') && !$request->has('orderno')) {
            $request->offsetSet('orderno', $request->input('order_no', ''));
        }



        $uri = $request->getRequestUri();
        if (false === stripos($uri, '/api/admin/')) {//如果是前端api，就进行一下处理
            if (!$request->has('source')) {
                $request->offsetSet('source', data_get($routeInfo, '1.source', 1));
            }

            $ip = ('production' == config('app.env', 'production')) ? FunctionHelper::getClientIP() : data_get($requestData, Constant::DB_TABLE_IP, FunctionHelper::getClientIP());
            $request->offsetSet(Constant::DB_TABLE_IP, $ip);

            if (!($request->filled(Constant::DB_TABLE_COUNTRY))) {//has 方法将确定是否所有指定值都存在
                $country = FunctionHelper::getCountry($ip);
                $request->offsetSet(Constant::DB_TABLE_COUNTRY, $country);
            }
        }

        //设置时区
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT);
        FunctionHelper::setTimezone($storeId);

        //记录请求日志
        $noLog = data_get($routeInfo, '1.noLog', []); /* @var $validatorData array */
        $noLog = Arr::collapse([$noLog, array_keys($request->file())]);
        $requestData = $request->all();
        if ($noLog) {
            foreach ($noLog as $key) {
                if (isset($requestData[$key])) {
                    unset($requestData[$key]);
                }
            }
        }

        $headerData = $request->headers->all();
        $requestMark = FunctionHelper::randomStr(10);
        $request->offsetSet(Constant::REQUEST_HEADER_DATA, $headerData);
        $request->offsetSet(Constant::REQUEST_MARK, $requestMark);
        data_set($requestData, Constant::REQUEST_HEADER_DATA, $headerData);
        data_set($requestData, Constant::REQUEST_MARK, $requestMark);

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
        $request->offsetSet(Constant::CLIENT_DATA, $clientData);
        data_set($requestData, Constant::CLIENT_DATA, $clientData);

        $service = '\App\Services\LogService';
        $method = 'addAccessLog';
        $action = $request->input('account_action', data_get($routeInfo, '1.account_action', ''));

        //设置客户访问url
        $fromUrl = $request->input(Constant::CLIENT_ACCESS_URL, ($request->headers->get('Referer') ?? 'no'));
        $request->offsetSet(Constant::CLIENT_ACCESS_URL, $fromUrl);
        data_set($requestData, Constant::CLIENT_ACCESS_URL, $fromUrl);

        $account = $request->input(Constant::DB_TABLE_ACCOUNT, $request->input('help_account', $request->input('operator', '')));
        $cookies = $request->input('account_cookies', '');
        $ip = FunctionHelper::getClientIP($request->input('ip'));
        $apiUrl = $request->getRequestUri();
        $createdAt = data_get($requestData, 'created_at', Carbon::now()->toDateTimeString());
        $extId = $request->input('id', 0);
        $extType = $request->input('ext_type', '');

        $parameters = [$action, $storeId, $actId, $fromUrl, $account, $cookies, $ip, $apiUrl, $createdAt, $extId, $extType, $requestData];

        $_parameters = [
            'apiUrl' => $apiUrl,
            'storeId' => $storeId,
            Constant::DB_TABLE_ACCOUNT => $account,
            'createdAt' => $createdAt,
        ];

        $queueConnection = config('queue.log_queue_connection');
        $extData = [
            'queueConnectionName' => $queueConnection,//Queue Connection
            'queue' => config('queue.connections.' . $queueConnection . '.queue'),//Queue Name
            //'delay' => 1,//任务延迟执行时间  单位：秒
        ];

        $logTaskData = FunctionHelper::getJobData($service, $method, $parameters, $requestData, $extData);
        $taskData = [
            $logTaskData
        ];
        if ($storeId && $account) {
            $taskData[] = FunctionHelper::getJobData(CustomerInfoService::getNamespaceClass(), 'updateLastlogin', [$_parameters], $requestData, $extData);
        }

        try {
            $isDeliverTask = false;
            $illunminateProcessData = [Constant::PROCESS_PLATFORM_ILLUMINATE, 'Base', 'write', [$taskData, 'baseProcess']];
            if ($request->has(['post_key', 'get_key'])) {//如果是swoole service 模式运行，就将记录请求日志的任务放到BaseProcess进程中运行
                $isDeliverTask = ProcessManager::handle(Constant::PROCESS_PLATFORM, 'Base', 'write', [$illunminateProcessData, 'baseProcess']);
            }

            if (!$isDeliverTask) {//如果记录请求日志的任务放入BaseProcess进程失败，就使用消息队列异步执行
                ProcessManager::handle(...$illunminateProcessData);
            }

        } catch (\Exception $exc) {

        }

        $request->offsetSet('isRequestLog', 1); //设置已经记录过请求流水
        $request->offsetSet(Constant::CLIENT_ACCESS_API_URI, $apiUrl);
        data_set($requestData, Constant::CLIENT_ACCESS_API_URI, $apiUrl, false);

        $report = data_get($routeInfo, '1.report');
        if ($report) {
            if (!(Arr::has($requestData, Constant::ACTION_TYPE))) {//如果 input 请求参数没有 Constant::ACTION_TYPE 对应的参数，就将 Constant::ACTION_TYPE 对应的参数设置到 input 参数中以便后续统一通过 input 获取
                $actionType = data_get($routeInfo, '1.' . Constant::ACTION_TYPE);
                $request->offsetSet(Constant::ACTION_TYPE, $actionType);
                data_set($requestData, Constant::ACTION_TYPE, $actionType, false);
            }
            ReportLogService::handle($requestData);
        }

        unset($requestData);

        return $next($request);
    }

}
