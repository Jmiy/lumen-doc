<?php

namespace App\Http\Controllers;

use App\Services\Platform\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Util\Response;
use App\Util\Constant;

class ExampleController extends Controller {

    /**
     * 清空opcache
     * @param Request $request
     */
    public function opcache(Request $request) {
        $rs = opcache_reset();
        return Response::json(['msg' => 'opcache cleared======' . $rs]);
    }

    /**
     * 清空缓存
     * @param Request $request
     */
    public function clear(Request $request) {

        $data = [];

        //清空用户认证缓存
        $data[] = '清空用户认证缓存===>' . Cache::tags(config('cache.tags.auth', ['{auth}']))->flush();
        $data[] = '清空后台用户认证缓存===>' . Cache::tags(config('cache.tags.adminAuth', ['{adminAuth}']))->flush();
        $data[] = '清空ip缓存===>' . Cache::tags(config('geoip.cache_tags'))->flush();

        //清空邀请统计
        $data[] = '清空邀请统计===>' . \App\Services\InviteService::delInviteStatisticsCache(0, 0, 0);

        //清空活动缓存
        $data[] = '清空活动缓存===>' . \App\Services\ActivityService::clear();

        //清空商城字典缓存
        $data[] = '清空商城字典缓存===>' . \App\Services\DictStoreService::clear();

        //清空系统字典缓存
        $data[] = '清空系统字典缓存===>' . \App\Services\DictService::clear();

        //清空会员缓存
        $data[] = '清空会员缓存===>' . Cache::tags(config('cache.tags.customer', ['{customer}']))->flush();

        $data[] = '清空后台列表总数缓存===>' . Cache::tags(config('cache.tags.adminCount', ['{adminCount}']))->flush();

        $data[] = '清空活动倒计时缓存===>' . Cache::tags(config('cache.tags.countdown'))->flush();

        $data[] = '清空类目列表缓存===>' . Cache::tags('{categoryList}')->flush();

        //$data[] = '清空抽奖次数限制缓存===>' . Cache::tags(config('cache.tags.lotteryLimit'))->flush();
        //清空中奖排行榜缓存
        $data[] = '清空中奖排行榜缓存===>' . \App\Services\ActivityWinningService::delRankCache(2, 6);
        $data[] = '清空中奖排行榜缓存===>' . \App\Services\ActivityWinningService::delRankCache(2, 12);
        $data[] = '清空中奖排行榜缓存===>' . \App\Services\ActivityWinningService::delRankCache(1, 6);
        $data[] = '清空中奖排行榜缓存===>' . \App\Services\ActivityWinningService::delRankCache(3, 3);
        $data[] = '清空中奖排行榜缓存===>' . \App\Services\ActivityWinningService::delRankCache(3, 5); //holife 2020一月新品活动
        $data[] = '清空抽奖分布式锁===>' . \App\Services\ActivityWinningService::clear();
        $data[] = '清空调查问券缓存===>' . \App\Services\Survey\SurveyService::clear();
        $data[] = '清空排行榜缓存===>' . \App\Services\RankService::clear();
        $data[] = '清空拉取订单缓存===>' . \App\Services\OrdersService::clear();
        $data[] = '清空唯一id缓存===>' . \App\Services\UniqueIdService::clear();
        $data[] = '清空平台订单缓存===>' . \App\Services\Platform\OrderService::clear();
        $data[] = '清空分布式锁===>' . \App\Services\BaseService::clear();
        $data[] = '清空统计缓存===>' . \App\Services\StatisticsService::clear();
        $data[] = '清空产品类目缓存===>' . \App\Services\Platform\CategoryService::clear();
        $data[] = '清空storeid缓存===>' . Cache::tags('{store}')->flush();
        $data[] = '清空storeid缓存===>' . Cache::tags(config('cache.tags.store'))->flush();

        $data[] = '清空产品类目缓存===>' . \App\Services\Permission\AdminConfigService::clear();
        $data[] = '清空产品类目缓存===>' . \App\Services\Permission\AdminUserConfigService::clear();
        $data[] = '清空产品类目缓存===>' . \App\Services\StoreService::clear();


        return Response::json($data);
    }

    /**
     * 修复数据
     * @param Request $request
     */
    public function hotfix(Request $request) {

        $data = \App\Services\InviteService::hotFix(8, 4);

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 页面发布
     * @param Request $request
     */
    public function pagePublish(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $data = \App\Services\Platform\OnlineStore\PagePublishService::handlePublish($storeId, Constant::PLATFORM_SERVICE_SHOPIFY);

        return Response::json(...Response::getResponseData($data));
    }

    /**
     * 临时调试使用
     * @param Request $request
     */
    public function tempTest(Request $request) {

        $orderData = [
            'AE' => '403-8807699-4152308',
            'AU' => '249-0084176-3109414',
            'CA' => '701-9008885-2597830',
            'DE' => '305-9589325-8974713',
            'ES' => '403-7977325-4838712',
            'FR' => '406-5324411-2698755',
            'IN' => '405-0859840-6144362',
            'IT' => '403-1541325-4493956',
            'JP' => '249-4303553-0971853',
            'MX' => '701-4408592-3635441',
            'NL' => '404-0627291-4038712',
            'SG' => '503-6930381-2629403',
            'UK' => '204-1394368-0797915',
            'US' => '112-7756756-6448231',
        ];

        $orderRet = [];
        foreach ($orderData as $country => $orderNo) {
            $orderRet = OrderService::getOrderData($orderNo, $country, Constant::PLATFORM_SERVICE_AMAZON, 1, false);
        }

        return Response::json($orderRet);
//        dd(\App\Services\OrderWarrantyService::handleEmail(5, 35664, 0));
//        exit;

        $config = [
//            'port' => '53456',
//            'database' => 'ptx_db',
//            'username' => 'ptx',
//            'password' => 'ptx123',
//            'host' => '14.21.71.212',//测试数仓：172.16.6.207  8123

//            'port' => '38712',
//            'database' => 'ptx_yw',
//            'username' => 'ptx_readonly',
//            'password' => 'ptx_readonly',
//            'host' => '14.21.71.211',

            'port' => '8123',
            'database' => 'ptx_yw',
            'username' => 'ptx_readonly',
            'password' => 'ptx_readonly',
            'host' => '172.16.6.210',
        ];

        $__nowTime = microtime(true);

        $db = new \ClickHouseDB\Client($config);
        $xcTime = (number_format(microtime(true) - $__nowTime, 8, '.', '') * 1000) . ' ms';
        dump('new===>', $xcTime);

        $_nowTime = microtime(true);
        $db->database('ptx_yw');
        $db->setTimeout(60);       // 10 seconds
        $db->setConnectTimeOut(5); // 5 seconds
        $xcTime = (number_format(microtime(true) - $_nowTime, 8, '.', '') * 1000) . ' ms';
        dump('set===>', $xcTime);

        //dump($db->showTables());

        $_nowTime = microtime(true);
//        $dd = $db->select("SELECT * FROM ptx_yw.ads_or_xc_order_item_report WHERE amazon_order_id='104-4444717-6843425'")->rows();
//        $xcTime = (number_format(microtime(true) - $_nowTime, 8, '.', '') * 1000) . ' ms';
//        dump('select===>', $xcTime);

        $dd = $db->select("SELECT * FROM ptx_yw.ads_or_xc_order_item_report LIMIT 10")->rows();
        $xcTime = (number_format(microtime(true) - $__nowTime, 8, '.', '') * 1000) . ' ms';
        dump('ext===>', $xcTime, $dd);

        return Response::json(...Response::getResponseData([]));
    }

}
