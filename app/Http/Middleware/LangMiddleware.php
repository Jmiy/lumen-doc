<?php

namespace App\Http\Middleware;

use App\Services\DictStoreService;
use App\Services\Platform\OrderService;
use App\Util\Constant;
use App\Util\Response;
use Closure;
use App\Util\FunctionHelper;
use Illuminate\Support\Facades\Log;

class LangMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $orderNo = $request->input(Constant::DB_TABLE_ORDER_NO, Constant::PARAMETER_STRING_DEFAULT);
        if (!empty($orderNo) && is_string($orderNo)) {
            if (!FunctionHelper::checkOrderNo($orderNo)) {
                return Response::json(...Response::getResponseData(Response::getDefaultResponseData(39006)));
            }
        }

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $langStore = DictStoreService::getByTypeAndKey($storeId, 'lang', 'store', true);
        if (!empty($langStore)) {
            app('translator')->setLocale($langStore);
            return $next($request);
        }

        app('translator')->setLocale(FunctionHelper::getCountry());

        $requestUri = $request->getRequestUri();

        if (!empty($orderNo) && is_string($orderNo)) {

            $countries = DictStoreService::getByTypeAndKey($storeId, 'lang', 'country', true); //国家
            $interfaceLangList = DictStoreService::getListByType($storeId, 'interface_lang'); //接口

            if (!empty($countries) && $interfaceLangList->isNotEmpty()) {
                $countries = explode(',', $countries);
                if (!empty($countries) && $interfaceLangList->firstWhere('conf_value', $requestUri)) {

                    $orderData = OrderService::getOrderData($orderNo, '', Constant::PLATFORM_SERVICE_AMAZON, $storeId);
                    if (data_get($orderData, Constant::RESPONSE_CODE_KEY, 0) != 1) {
                        return Response::json(...Response::getResponseData(Response::getDefaultResponseData(30002)));
                    }
                    $orderItemData = data_get($orderData, Constant::RESPONSE_DATA_KEY . Constant::LINKER . 'items', []);
                    if (empty($orderItemData)) {
                        return Response::json(...Response::getResponseData(Response::getDefaultResponseData(30001)));
                    }

                    $localeCountry = 'US';
                    $country = data_get(current($orderItemData), Constant::DB_TABLE_ORDER_COUNTRY, Constant::PARAMETER_STRING_DEFAULT);
                    $country = strtoupper($country);
                    if (in_array($country, $countries)) {
                        $localeCountry = $country;
                    }
                    app('translator')->setLocale(strtolower($localeCountry));
                    $request->offsetSet('order_data', $orderData);
                }
            }
        }

        return $next($request);
    }

}
