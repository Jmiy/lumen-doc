<?php

namespace App\Models;

use App\Models\Activity as Model;
use App\Database\Eloquent\SoftDeletes;

class ActivityVirtualAccount extends Model
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

    const STATUS_AT = 'status';
    const DELETED_AT = 'deleted_at';

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'act_id', 'id');
    }

}