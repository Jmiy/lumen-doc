<?php

namespace App\Services\Activity\LuckyNumber\Console\Commands;

use App\Console\Commands\BaseCommand;
use App\Services\Activity\Factory;
use App\Util\Constant;
use App\Util\FunctionHelper;
use Carbon\Carbon;

class Draw extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lucky_number_draw {storeId : The ID of the store}  {actId : The ID of the act} {--delay= : delay} {--l|limit= : limit} {--appEnv= : appEnv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: lucky_number_draw';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle()
    {

        $storeId = $this->argument('storeId'); //商城id
        $actId = $this->argument('actId'); //平台
        $limit = $this->option(Constant::ACT_LIMIT_KEY) ? $this->option(Constant::ACT_LIMIT_KEY) : 1000;

        $this->handleRequest($storeId);

        $isValidAct = Factory::handle($storeId, $actId, 'isValidAct', [$storeId, $actId]);
        $actValidCode = data_get($isValidAct, Constant::RESPONSE_CODE_KEY);
        if ($actValidCode != 1) {//如果活动无效，就直接返回

            if ($actValidCode != 69999) {
                $this->handleResponse(); //处理响应
                return $isValidAct;
            }

            $endAt = data_get($isValidAct, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::DB_TABLE_END_AT, null);
            if ($endAt !== null && Carbon::yesterday()->toDateTimeString() > $endAt) {//活动已经结束，就直接返回
                $this->handleResponse(); //处理响应
                return true;//活动过期
            }
        }

        $time = $this->option('delay') ?? (Carbon::tomorrow()->timestamp - Carbon::now()->timestamp);
        $parameters = [$storeId, $actId, 'lucky', [$storeId, $actId]];
        FunctionHelper::laterQueue($time, FunctionHelper::getJobData(Factory::getNamespaceClass(), 'handle', $parameters));

//        $dd = Factory::handle($storeId, $actId, 'lucky', [$storeId, $actId]);
//        dump($dd);

        $this->handleResponse(); //处理响应

        return true;
    }

}
