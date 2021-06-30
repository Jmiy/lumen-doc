<?php


namespace App\Models;

use App\Models\Activity as Model;
use App\Database\Eloquent\SoftDeletes;
use App\Services\CustomerInfoService;
use App\Util\Constant;

class ActivityGuessNumber extends Model
{

    use SoftDeletes;

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    //protected $connection = 'db_mpow';

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = [];


    /**
     * Indicates if the model should be timestamped.
     * 时间戳
     * 默认情况下，Eloquent 会认为在你的数据库表有 created_at 和 updated_at 字段。如果你不希望让 Eloquent 来自动维护这两个字段，可在模型内将 $timestamps 属性设置为 false
     *
     * @var bool
     */
    public $timestamps = false;

    const IS_RECORD_PUBLIC_DATA = true;//是否记录公共数据 true：记录  false：否

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'act_id', 'id');
    }

    public function prize()
    {
        return $this->belongsTo(ActivityPrize::class, 'prize_id', 'id');
    }

    /**
     * 订单用户 一对一
     * @return
     */
    public function customer_info() {
        return CustomerInfoService::getModel($this->getStoreId())->hasOne(CustomerInfo::class, Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::DB_TABLE_CUSTOMER_PRIMARY);
    }

}
