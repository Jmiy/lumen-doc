<?php

namespace App\Console\Commands;

use App\Services\MetafieldService;
use App\Util\FunctionHelper;
use App\Services\ActivityProductService;
use App\Util\Constant;
use App\Models\Store;

class SyncProductPrice extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync_product_price {--storeId= : storeId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: sync_product_price';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $storeId = $this->option('storeId') ? $this->option('storeId') : 0;

        $storeIds = $storeId ? [$storeId] : Store::pluck('id');
        foreach ($storeIds as $storeId) {
            $this->handleRequest($storeId);

            //设置时区
            FunctionHelper::setTimezone($storeId);

            $platform = FunctionHelper::getUniqueId(Constant::PLATFORM_SHOPIFY);

            $productModel = ActivityProductService::getModel($storeId, '');
            $productData = $productModel->select('id', 'asin', 'shop_sku', 'country', 'sku', 'act_id', 'business_type')->get(); //->where([['shop_sku', '!=', '']])
            foreach ($productData as $product) {
                $businessType = data_get($product, 'business_type');
                if ($businessType == 1) {
                    $where = [
                        Constant::DB_TABLE_STORE_ID => $storeId,
                        Constant::DB_TABLE_PLATFORM => $platform,
                        Constant::OWNER_RESOURCE => ActivityProductService::getMake(),
                        Constant::OWNER_ID => $product['id'],
                        Constant::NAME_SPACE => 'free_testing',
                        Constant::DB_TABLE_KEY => 'country',
                    ];
                    $select = [Constant::DB_TABLE_VALUE];
                    $counties = MetafieldService::getModel($storeId)->select($select)->buildWhere($where)->get();
                    $country = data_get($counties, '0.value', '');
                    empty($country) && $country = $product['country'];

                    $amazonPriceData = ActivityProductService::getProductPrice($product['asin'], $product['shop_sku'], $country);
                    if (data_get($amazonPriceData, Constant::RESPONSE_CODE_KEY, 0) != 1 || empty(data_get($amazonPriceData, Constant::RESPONSE_DATA_KEY, []))) {//如果拉取价格失败，就返回
                        continue;
                    }
                } else {
                    $amazonPriceData = ActivityProductService::getProductPrice($product['asin'], $product['shop_sku'], $product['country']);
                    if (data_get($amazonPriceData, Constant::RESPONSE_CODE_KEY, 0) != 1 || empty(data_get($amazonPriceData, Constant::RESPONSE_DATA_KEY, []))) {//如果拉取价格失败，就返回
                        continue;
                    }
                }

                $amazonPriceData = data_get($amazonPriceData, Constant::RESPONSE_DATA_KEY, []);
                $isQuerySuccessful = data_get($amazonPriceData, 'isQuerySuccessful', false);
                $queryResults = data_get($amazonPriceData, 'queryResults', 0);
                $priceData = data_get($amazonPriceData, 'priceData', []);
                $data = [
                    'query_at' => data_get($amazonPriceData, 'queryAt', ''), //查询时间
                    'is_query_successful' => $isQuerySuccessful, //是否查询成功 1：成功 0：失败
                    'query_results' => $queryResults, //查询结果 1：查询到价格数据 0：亚马逊和爬虫 价格数据为空
                ];

                if ($isQuerySuccessful && $priceData) {
                    data_set($data, 'listing_price', data_get($priceData, 'listing_price', 0)); //销售价
                    data_set($data, 'regular_price', data_get($priceData, 'regular_price', 0)); //原价
                    data_set($data, 'price_updated_at', data_get($priceData, 'updated_at', '')); //销参产品价格更新时间
                    data_set($data, 'price_source', data_get($priceData, 'price_source', '')); //价格来源
                }

                //更新活动产品价格数据
                ActivityProductService::update($storeId, ['id' => data_get($product, 'id', 0)], $data);
            }

            $this->handleResponse(); //处理响应
        }

        return true;
    }

}
