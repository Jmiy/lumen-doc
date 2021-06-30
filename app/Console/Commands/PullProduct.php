<?php

/**
 * 拉取产品
 * User: Jmiy
 * Date: 2020-10-09
 * Time: 15:01
 */

namespace App\Console\Commands;

use App\Util\Constant;
use Carbon\Carbon;
use App\Util\FunctionHelper;
use App\Services\Platform\ProductService as PlatformProductService;
use App\Services\Platform\ProductCategoryService;

class PullProduct extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string php /mnt/hgfs/patozon/VipApi/artisan pull_product Amazon 1 --limit=100
     */
    protected $signature = 'pull_product {platform : The platform of the store} {storeId : The ID of the store} {--l|limit= : limit} {--appEnv= : appEnv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: pull_product';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $platform = $this->argument('platform'); //平台
        $storeId = $this->argument('storeId'); //商城id
        $limit = $this->option(Constant::ACT_LIMIT_KEY) ? $this->option(Constant::ACT_LIMIT_KEY) : 1000;
        if (empty($platform)) {
            return true;
        }

        ini_set('memory_limit', '1024M'); // 设置PHP临时允许内存大小
        $this->handleRequest($storeId);
        FunctionHelper::setTimezone($storeId); //设置时区
        //拉取产品类目
        $isDo = true;
        $parameters = [
            Constant::DB_TABLE_PLATFORM => $platform,
            Constant::ACT_LIMIT_KEY => $limit,
        ];
        while ($isDo) {

            $data = ProductCategoryService::handlePull(0, $platform, $parameters);

            $categoryData = data_get($data, '0.categorys', []);

            $_categoryData = collect($categoryData);
            $_categoryData = $_categoryData->sortByDesc(Constant::DB_TABLE_PRIMARY)->values()->all();
            $categoryItem = current($_categoryData);

            $parameters[Constant::DB_TABLE_PRIMARY] = [
                Constant::DB_TABLE_PRIMARY => data_get($categoryItem, Constant::DB_TABLE_PRIMARY, 0),
            ];

            $isDo = count($categoryData) >= $limit;
        }

        //拉取产品数据
        $isDo = true;
        $platformProduct = PlatformProductService::getModel($storeId);
        while ($isDo) {
            $_platform = FunctionHelper::getUniqueId($platform);
            $where = [
                Constant::DB_TABLE_PLATFORM => $_platform, //平台
            ];

            $maxUpdatedAt = $platformProduct->buildWhere($where)->max(Constant::DB_TABLE_PLATFORM_UPDATED_AT);
            $parameters = [
                Constant::DB_TABLE_PLATFORM_UPDATED_AT => $maxUpdatedAt,
                Constant::DB_TABLE_PRODUCT_ID => $platformProduct->buildWhere([
                    Constant::DB_TABLE_PLATFORM => $_platform, //平台
                    Constant::DB_TABLE_PLATFORM_UPDATED_AT => $maxUpdatedAt,
                ])->max(Constant::DB_TABLE_PRODUCT_ID),
                Constant::ACT_LIMIT_KEY => $limit,
            ];

            $data = PlatformProductService::handlePull(0, $platform, $parameters);

            $isDo = count(data_get($data, Constant::RESPONSE_DATA_KEY, [])) >= $limit;
        }

        $this->handleResponse(); //处理响应

        return true;
    }

}
