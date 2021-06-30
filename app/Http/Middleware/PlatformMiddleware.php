<?php

namespace App\Http\Middleware;

use Closure;
use App\Util\FunctionHelper;
use App\Util\Constant;
use App\Services\Store\PlatformServiceManager;
use App\Services\Platform\CallbackDetailService;
use App\Util\Response;

class PlatformMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

//        $storeId = $request->route(Constant::DB_TABLE_STORE_ID, $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT)); //获取store_id
//        $platform = $request->route(Constant::DB_TABLE_PLATFORM, null); //平台

        $routeInfo = $request->route();
        $storeId = data_get($routeInfo, '2.' . Constant::DB_TABLE_STORE_ID, $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT)); //获取store_id
        $platform = data_get($routeInfo, '2.' . Constant::DB_TABLE_PLATFORM, $request->input(Constant::DB_TABLE_PLATFORM)); //平台
        $verifyRet = PlatformServiceManager::handle($platform, 'Base', 'callBackVerify', [$storeId, $request]);
        if ($verifyRet[Constant::RESPONSE_CODE_KEY] != 1) {
            $parameters = Response::getResponseData($verifyRet);
            return Response::json(...$parameters);
        }

        FunctionHelper::setTimezone($storeId);

        $data = $request->all(); //请求参数
        $_businessType = data_get($routeInfo, '1.business_type', '');
        $businessSubType = data_get($routeInfo, '1.business_subtype', '');
        $businessId = PlatformServiceManager::handle($platform, 'Base', 'getBusinessId', [$_businessType, $businessSubType, $data]); //业务id(order_id|refund_id|fulfillment_id|等)
        $businessType = PlatformServiceManager::handle($platform, 'Base', 'getBusinessType', [$_businessType, $businessSubType, $data]); //业务id(order_id|refund_id|fulfillment_id|等)
        $businessExtId = PlatformServiceManager::handle($platform, 'Base', 'getBusinessExtId', [$_businessType, $businessSubType, $data]); //业务关联id(如:refund关联order_id等)
        $businessExtType = PlatformServiceManager::handle($platform, 'Base', 'getBusinessExtType', [$_businessType, $businessSubType, $data]); //业务关联类型(如:refund关联order等)

        $request->offsetSet(Constant::DB_TABLE_STORE_ID, $storeId);
        $request->offsetSet(Constant::DB_TABLE_PLATFORM, $platform);
        $request->offsetSet('business_id', $businessId);
        $request->offsetSet('business_type', $businessType);
        $request->offsetSet('business_subtype', $businessSubType);
        $request->offsetSet('business_ext_id', $businessExtId);
        $request->offsetSet('business_ext_type', $businessExtType);

        $data = $request->all(); //请求参数
        $service = CallbackDetailService::getNamespaceClass();
        $method = 'handle';
        $parameters = [$storeId, $platform, $businessId, $businessType, $businessSubType, $businessExtId, $businessExtType, $data];

        FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters)); //记录第三方回调数据

        return $next($request);
    }

}
