<?php


namespace App\Http\Controllers\Admin;


use App\Services\DictStoreService;
use App\Services\OrderReviewService;
use App\Services\ActivityService;
use App\Services\OrderWarrantyService;
use App\Util\Constant;
use App\Util\Response;
use Illuminate\Http\Request;

class EditConfigController extends Controller {

    /**
     * 修改各品牌延保时间
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editInsurance(Request $request) {
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_STRING_DEFAULT);
        $id = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_STRING_DEFAULT);
        $warranty_at = $request->input(Constant::WARRANTY_AT, Constant::PARAMETER_STRING_DEFAULT);
        if (empty($id) || empty($storeId) || empty($warranty_at)) {
            return Response::json([], -1, Constant::PARAMETER_STRING_DEFAULT);
        }
        $data[Constant::WARRANTY_AT] = $warranty_at;
        $where = [Constant::DB_TABLE_PRIMARY => $id];
        $res = OrderWarrantyService::editInsurance($storeId, $where, $data);

        $rdata['code'] = $res;
        $parameters = Response::getResponseData($rdata);

        return Response::json(...$parameters);
    }

    /**
     * 修改各品牌邮件模板
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editEmailTemple(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_STRING_DEFAULT);
        $id = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_STRING_DEFAULT);
        $conf_value = $request->input(Constant::DB_TABLE_STORE_DICT_VALUE, Constant::PARAMETER_STRING_DEFAULT);
        if (empty($id) || empty($storeId) || empty($conf_value)) {
            return Response::json([], -1, Constant::PARAMETER_STRING_DEFAULT);
        }
        $data[Constant::DB_TABLE_STORE_DICT_VALUE] = $conf_value;
        $where = [Constant::DB_TABLE_PRIMARY => $id];
        $res = DictStoreService::update($storeId, $where, $data);

        $rdata['code'] = $res;
        $parameters = Response::getResponseData($rdata);

        return Response::json(...$parameters);
    }

    /**
     * 新增各品牌邮件模板
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addEmailTemple(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_STRING_DEFAULT);
        $type = $request->input(Constant::DB_TABLE_TYPE, Constant::PARAMETER_STRING_DEFAULT);
        $conf_key = $request->input(Constant::DB_TABLE_STORE_DICT_KEY, Constant::PARAMETER_STRING_DEFAULT);
        $country = $request->input(Constant::DB_TABLE_COUNTRY, Constant::PARAMETER_STRING_DEFAULT);
        $conf_value = $request->input(Constant::DB_TABLE_STORE_DICT_VALUE, Constant::PARAMETER_STRING_DEFAULT);
        $remark = $request->input(Constant::DB_TABLE_REMARK, Constant::PARAMETER_STRING_DEFAULT);
        if (empty($storeId) || empty($type) || empty($conf_key) || empty($country) || empty($conf_value) || empty($remark)) {
            return Response::json([], -1, Constant::PARAMETER_STRING_DEFAULT);
        }
        $res = DictStoreService::add($storeId, $type, $conf_key, $country, $conf_value, $remark);
        if ($res){
            $rdata['code'] = 1;
        } else {
            $rdata['code'] = 0;
        }

        $parameters = Response::getResponseData($rdata);

        return Response::json(...$parameters);
    }

    /**
     * 修改测评活动时间
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editActivityTime(Request $request) {
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_STRING_DEFAULT);
        $id = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_STRING_DEFAULT);
        $end_at = $request->input(Constant::DB_TABLE_END_AT, Constant::PARAMETER_STRING_DEFAULT);
        if (empty($id) || empty($storeId) || empty($end_at)) {
            return Response::json([], -1, Constant::PARAMETER_STRING_DEFAULT);
        }
        $data[Constant::DB_TABLE_END_AT] = $end_at;
        $where = [Constant::DB_TABLE_PRIMARY => $id];
        $res = ActivityService::updateExpireTime($storeId, $where, $data);

        $rdata['code'] = $res;
        $parameters = Response::getResponseData($rdata);

        return Response::json(...$parameters);
    }
}
