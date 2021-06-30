<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RankService;

class Rank extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rank {storeId : The ID of the store} {type : type of rank} {--l|limit= : limit} {--createdAtMin= : createdAtMin} {--createdAtMax= : createdAtMax}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: rank';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        RankService::clearRankCache();

        return true;
    }

}
