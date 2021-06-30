<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\Monitor\MonitorServiceManager;

class DingExportController extends Controller {

    /**
     * 导出异常钉钉预警
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function alert(Request $request) {

        $apiUrl = $request->input('api_url', '');
        $code = $request->input('code', '');
        $message = $request->input('message', '');
        if (empty($apiUrl) || empty($code) || empty($message)) {
            return ['code' => 0, 'msg' => 'Parameter must not be empty', 'data' => []];
        }
        $exceptionName = '导出异常预警';

        $parameters = [(string) $exceptionName, (string) $message, $code, $apiUrl];
        MonitorServiceManager::handle('Ali', 'Ding', 'report', $parameters);

        return Response::json();
    }

}
