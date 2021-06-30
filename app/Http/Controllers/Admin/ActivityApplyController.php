<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\ActivityApplyService;
use App\Services\ExcelService;
use App\Util\Constant;
use App\Services\ReportLogService;
use App\Util\FunctionHelper;

class ActivityApplyController extends Controller {

    /**
     * 众测申请列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $requestData = $request->all();
        $data = ActivityApplyService::getListData($requestData);

        return Response::json($data);
    }

    /**
     * 列表导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {

        $requestData = $request->all();

        $header = [
            Constant::EXPORT_DISTINCT_FIELD => [
                Constant::EXPORT_PRIMARY_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::EXPORT_PRIMARY_VALUE_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::DB_EXECUTION_PLAN_SELECT => ['aa.id']
            ],
        ];

        $storeId = data_get($requestData, 'store_id', 0); //商城id
        switch ($storeId) {
            case 8:
                $header = Arr::collapse([$header, [
                                '账号邮箱' => 'account',
                                '会员名' => 'customer_name',
                                '国家' => 'country',
                                '用户ip' => 'ip',
                                '申请产品' => 'product_name',
                                '申请时间' => 'created_at',
                ]]);

                break;

            default:
                $header = Arr::collapse([$header, [
                                '活动期数' => 'act_name',
                                '会员名' => 'customer_name',
                                '账号邮箱' => 'account',
                                '账号激活' => 'is_activate',
                                '站点' => 'country',
                                '申请产品sku' => 'sku',
                                '产品标题' => 'product_name',
                                '申请时间' => 'created_at',
                                '申请ip' => 'ip',
                                '审核状态' => 'audit_status',
                                '审核人' => 'reviewer',
                                '审核时间' => 'review_at',
                                '备注' => 'remarks',
                                '兴趣产品' => 'products',
                ]]);
                break;
        }

        $header = Arr::collapse([$header, [
                        'Amazon profile  url' => 'profile_url',
                        '社交媒体' => 'social_media',
                        'youtube' => 'youtube_channel',
                        '博客或者技术站' => 'blogs_tech_websites',
                        '论坛' => 'deal_forums',
                        '其他' => 'others',
                        '订单号' => 'orderno',
                        '订单国家' => 'order_country',
                        '订单匹配' => 'order_status',
                        '申请描述' => 'apply_remarks',
        ]]);

        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;
        data_set($requestData, 'is_export', 1);

        $service = ActivityApplyService::getNamespaceClass();
        $method = 'getListData';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = $method;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

    /**
     * 审核
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function audit(Request $request) {

        $ids = $request->input('ids', []); //审核id
        if (empty($ids)) {
            return Response::json([], 10015, '数据不存在');
        }

        $storeId = $request->input('store_id', 0); //审核状态
        $auditStatus = $request->input('audit_status', 0); //审核状态
        $reviewer = $request->input('reviewer', ''); //审核人
        $remarks = $request->input('remarks', ''); //备注
        $applyType = $request->input('apply_type', 0); //申请类型
        $data = ActivityApplyService::audit($storeId, $ids, $auditStatus, $reviewer, $remarks, $applyType);

        return Response::json($data);
    }

    /**
     * 活动申请产品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function actApplyList(Request $request) {

        $data = ActivityApplyService::getActApplyList($request->all());

        return Response::json($data);
    }

    /**
     * 导出申请产品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportActApplyList(Request $request) {

        $requestData = $request->all();

        $header = [
            Constant::EXPORT_DISTINCT_FIELD => [
                Constant::EXPORT_PRIMARY_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::EXPORT_PRIMARY_VALUE_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::DB_EXECUTION_PLAN_SELECT => ['aa.' . Constant::DB_TABLE_PRIMARY]
            ],
            '邮箱' => Constant::DB_TABLE_ACCOUNT,
            '用户名' => 'use_name',
            '国家' => Constant::DB_TABLE_COUNTRY,
            '用户ip' => Constant::DB_TABLE_IP,
            '申请产品名称' => 'product_' . Constant::DB_TABLE_NAME,
            '注册时间' => Constant::DB_TABLE_OLD_CREATED_AT,
        ];

        if ($this->storeId == 3 && data_get($requestData, Constant::DB_TABLE_ACT_ID)) {
            $header = Arr::collapse([$header, [
                            '设备信息' => Constant::DEVICE, //'client_data' . Constant::LINKER .
                            '设备类型' => Constant::DEVICE_TYPE, // 设备类型 1:手机 2：平板 3：桌面
                            '系统信息' => Constant::DB_TABLE_PLATFORM, //系统信息
                            '系统版本' => Constant::PLATFORM_VERSION, //系统版本
                            '浏览器信息' => Constant::BROWSER, // 浏览器信息  (Chrome, IE, Safari, Firefox, ...)
                            '浏览器版本' => Constant::BROWSER_VERSION, // 浏览器版本
                            '语言' => Constant::LANGUAGES, // 语言 ['nl-nl', 'nl', 'en-us', 'en']
                            '是否是机器人' => Constant::IS_ROBOT, //是否是机器人
            ]]);
        }

        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;
        data_set($requestData, 'is_export', 1);

        $requestData['deviceTypeData'] = FunctionHelper::getDeviceType($this->storeId);
        $requestData['isRobotData'] = FunctionHelper::getWhetherData(null);

        $service = ActivityApplyService::getNamespaceClass();
        $method = 'getActApplyList';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = $method;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

    /**
     * 评测2.0申请列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function freeTestingList(Request $request) {
        $requestData = $request->all();
        data_set($requestData, 'free_test_new', true);
        $select = [
            'aa.' . Constant::DB_TABLE_PRIMARY,
            'aa.' . Constant::DB_TABLE_FIRST_NAME . ' as apply_first_name',
            'aa.' . Constant::DB_TABLE_LAST_NAME  . ' as apply_last_name',
            'aa.' . Constant::DB_TABLE_COUNTRY  . ' as apply_country',
            'aa.' . Constant::DB_TABLE_ACCOUNT,
            'aa.' . Constant::DB_TABLE_CUSTOMER_PRIMARY,
            'ap.' . Constant::DB_TABLE_SKU,
            'ap.' . Constant::DB_TABLE_SHOP_SKU,
            'ap.' . Constant::DB_TABLE_NAME . ' as product_name',
            'ap.' . Constant::DB_TABLE_COUNTRY,
            'aa.' . Constant::DB_TABLE_CREATED_AT,
            'aa.' . Constant::DB_TABLE_IP,
            'aa.' . Constant::AUDIT_STATUS,
            'aa.reviewer',
            'aa.' . Constant::REVIEW_AT,
            'aa.' . Constant::DB_TABLE_REMARKS,
            'ci.' . Constant::DB_TABLE_FIRST_NAME,
            'ci.' . Constant::DB_TABLE_LAST_NAME,
            'ci.' . Constant::DB_TABLE_COUNTRY . ' as register_country',
            'aa.' . Constant::DB_TABLE_EXT_ID,
            'act.' . Constant::DB_TABLE_NAME . ' as act_name',
        ];
        $data = ActivityApplyService::adminApplyList($requestData, true, true, $select);
        return Response::json($data);
    }

    /**
     * 评测2.0申请列表导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportFreeTestingList(Request $request) {
        $requestData = $request->all();
        data_set($requestData, 'is_export', 1);
        data_set($requestData, 'free_test_new', true);
        $header = [
            '活动期数' => 'act_name',
            '会员名' => Constant::DB_TABLE_NAME,
            '账号邮箱' => Constant::DB_TABLE_ACCOUNT,
            '申请ip' => Constant::DB_TABLE_IP,
            '注册国家' => 'register_country',
            //'账号激活' => '',
            '申请站点' => Constant::DB_TABLE_COUNTRY,
            '申请产品sku' => Constant::DB_TABLE_SKU,
            '产品标题' => 'product_name',
            '申请时间' => Constant::DB_TABLE_CREATED_AT,
            '审核状态' => Constant::AUDIT_STATUS,
            '审核人' => 'reviewer',
            '审核时间' => Constant::REVIEW_AT,
            '备注' => 'remarks',
            //'兴趣产品' => Constant::DB_TABLE_PRODUCT_STATUS,
            'Amazon profile  url' => Constant::DB_TABLE_PROFILE_URL,
            '订单号' => Constant::DB_TABLE_ORDER_NO,
            '订单国家' => Constant::DB_TABLE_ORDER_COUNTRY,
            '订单匹配' => 'show_order_status',
            '申请描述' => 'apply_remarks',
            'reviews' => 'reviews',
            Constant::EXPORT_DISTINCT_FIELD => [
                Constant::EXPORT_PRIMARY_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::EXPORT_PRIMARY_VALUE_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::DB_EXECUTION_PLAN_SELECT => ['aa.' . Constant::DB_TABLE_PRIMARY]
            ],
        ];

        $service = ActivityApplyService::getNamespaceClass();
        $method = 'adminApplyList';
        $select = [
            'aa.' . Constant::DB_TABLE_PRIMARY,
            'aa.' . Constant::DB_TABLE_FIRST_NAME . ' as apply_first_name',
            'aa.' . Constant::DB_TABLE_LAST_NAME  . ' as apply_last_name',
            'aa.' . Constant::DB_TABLE_ACCOUNT,
            'aa.' . Constant::DB_TABLE_CUSTOMER_PRIMARY,
            'aa.' . Constant::DB_TABLE_COUNTRY  . ' as apply_country',
            'ap.' . Constant::DB_TABLE_SKU,
            'ap.' . Constant::DB_TABLE_SHOP_SKU,
            'ap.' . Constant::DB_TABLE_NAME . ' as product_name',
            'ap.' . Constant::DB_TABLE_COUNTRY,
            'aa.' . Constant::DB_TABLE_CREATED_AT,
            'aa.' . Constant::DB_TABLE_IP,
            'aa.' . Constant::AUDIT_STATUS,
            'aa.reviewer',
            'aa.' . Constant::REVIEW_AT,
            'aa.' . Constant::DB_TABLE_REMARKS,
            'ci.' . Constant::DB_TABLE_FIRST_NAME,
            'ci.' . Constant::DB_TABLE_LAST_NAME,
            'aa.' . Constant::DB_TABLE_EXT_ID,
            'act.' . Constant::DB_TABLE_NAME . ' as act_name',
            'aai.' . Constant::DB_TABLE_ORDER_NO,
            'aai.order_country',
            'aai.order_status',
            'aai.remarks as apply_remarks',
            'aa.' . Constant::DB_TABLE_PROFILE_URL,
            'ci.' . Constant::DB_TABLE_PROFILE_URL . ' as customer_profile_url',
            'aai.social_media',
            'aai.youtube_channel',
            'aai.blogs_tech_websites',
            'aai.deal_forums',
            'aai.others',
            'ci.' . Constant::DB_TABLE_COUNTRY . ' as register_country',
        ];
        $parameters = [$requestData, true, true, $select, false, false];
        $countMethod = $method;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters);

        return Response::json([Constant::FILE_URL => $file]);
    }

    /**
     * 评测2.0申请详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function freeTestingInfo(Request $request) {
        $requestData = $request->all();
        $select = [
            'aa.' . Constant::DB_TABLE_PRIMARY,
            'aai.' . Constant::DB_TABLE_ORDER_NO,
            'aai.order_country',
            'aai.order_status as show_order_status',
            'aai.remarks',
            'aa.' . Constant::DB_TABLE_PROFILE_URL,
            'ci.' . Constant::DB_TABLE_PROFILE_URL . ' as customer_profile_url',
            'aai.social_media',
            'aai.youtube_channel',
            'aai.blogs_tech_websites',
            'aai.deal_forums',
            'aai.others',
        ];
        $data = ActivityApplyService::freeTestingInfo($requestData, true, true, $select);
        return Response::json($data);
    }
}
