<?php

namespace App\Console\Commands;

use App\Models\Erp\Amazon\AmazonOrderItem;
use App\Services\Platform\OrderItemService;
use App\Util\FunctionHelper;
use App\Util\Constant;

class PullAmazonOrderMetafields extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pull_amazon_order_metafields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: pull_amazon_order_metafields';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $storeId = 2;
        $this->handleRequest($storeId);

        //还原获取店铺失败的数据，防止修复停留在无效订单item
        $defaultShopName = 'shopName';
        OrderItemService::update(0,[Constant::SHOP_NAME => $defaultShopName],[Constant::SHOP_NAME => '']);

        //获取需要修复店铺名称的订单item
        $where = [
            Constant::DB_TABLE_PLATFORM => FunctionHelper::getUniqueId(Constant::PLATFORM_SERVICE_AMAZON), //平台
            Constant::SHOP_NAME => Constant::PARAMETER_STRING_DEFAULT, //平台
        ];
        $orderItemSelect=['id','platform_order_item_id','country','asin','sku'];
        $orderItems = OrderItemService::getModel()->buildWhere($where)->select($orderItemSelect)->limit(10)->get();
        while ($orderItems->isNotEmpty()){
            foreach ($orderItems as $orderItem){
                $id = data_get($orderItem,Constant::DB_TABLE_PRIMARY,0);
                $platform_order_item_id = data_get($orderItem,'platform_order_item_id',0);
                $country = strtolower(data_get($orderItem,'country'));
                $asin = data_get($orderItem,'asin');
                $sku = data_get($orderItem,'sku');

                $select = [
                    Constant::DB_TABLE_ACCOUNT . ' as '.Constant::SHOP_NAME, //店铺名称
                    'asin',
                    'sku'
                ];

                $dataStructure = 'one';
                $flatten = true;
                $handleData = [
                ];

                $joinData = [];
                $with = [];
                $unset = [];
                $exePlan = FunctionHelper::getExePlan(
                    'default_connection_1',
                    null,
                    'AmazonOrderItem',
                    (AmazonOrderItem::$tablePrefix . '_' . $country),
                    $select,
                    [Constant::DB_TABLE_PRIMARY => $platform_order_item_id],
                    [],
                    1,
                    null,
                    false,
                    [],
                    false,
                    $joinData, [],
                    $handleData,
                    $unset
                );

                $dbExecutionPlan = [
                    Constant::DB_EXECUTION_PLAN_PARENT => $exePlan,
                ];

                $orderItemData = FunctionHelper::getResponseData(null, $dbExecutionPlan, $flatten, false, $dataStructure);
                if (empty($orderItemData)) {
                    continue;
                }

                $shopName = $defaultShopName;
                if($asin == data_get($orderItemData,'asin','') && $sku == data_get($orderItemData,'sku','')){
                    $shopName = data_get($orderItemData,Constant::SHOP_NAME,'');
                }

                OrderItemService::update(0,[Constant::DB_TABLE_PRIMARY => $id],[Constant::SHOP_NAME=>$shopName]);
            }

            $orderItems = OrderItemService::getModel()->buildWhere($where)->select($orderItemSelect)->limit(100)->get();
        }

        $this->handleResponse(); //处理响应

        return true;
    }

}
