<?php

namespace App\Models;

use App\Models\BaseModel as Model;
//use App\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Util\Constant;

class CustomerLog extends Model {

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'customer_log';

    /**
     * Indicates if the model should be timestamped.
     * 时间戳
     * 默认情况下，Eloquent 预期你的数据表中存在 created_at 和 updated_at 。如果你不想让 Eloquent 自动管理这两个列， 请将模型中的 $timestamps 属性设置为 false：
     *
     * @var bool
     */
    public $timestamps = false;

    const CREATED_AT = Constant::DB_TABLE_OLD_CREATED_AT;
    const UPDATED_AT = null;

    //可插入表单字段
//    protected $fillable = [
//        'user_id', 'status', 'department_id', 'domain', 'logo', 'title',
//        'description', 'keywords', 'themes', 'lang', 'deleted_at', 'created_at', 'updated_at'
//    ];

    /**
     * 不可被批量赋值的属性。
     * $guarded 属性包含的是不想被批量赋值的属性的数组。即所有不在数组里面的属性都是可以被批量赋值的。也就是说，$guarded 从功能上讲更像是一个「黑名单」。而在使用的时候，也要注意只能是 $fillable 或 $guarded 二选一
     * 如果想让所有的属性都可以被批量赋值，就把 $guarded 定义为空数组。
     *
     * @var array
     */
    protected $guarded = [];

    public static function add($data) {
        $nowTime = Carbon::now()->toDateTimeString();
        $_data = [
            'store_id' => Arr::get($data, 'store_id', 0),
            'customer_id' => Arr::get($data, 'customer_id', 0),
            'type' => Arr::get($data, 'type', 'com'),
            'action' => Arr::get($data, 'action', ''),
            'content' => Arr::get($data, 'content', ''),
            Constant::DB_TABLE_OLD_CREATED_AT => Arr::get($data, Constant::DB_TABLE_OLD_CREATED_AT, $nowTime),
            'from' => Arr::get($data, 'from', ''),
            'api' => Arr::get($data, 'api', ''),
            'created_at' => Arr::get($data, 'created_at', $nowTime),
            'updated_at' => Arr::get($data, 'updated_at', $nowTime),
            'account' => Arr::get($data, 'account', ''),
            'ip' => Arr::get($data, 'ip', ''),
        ];

        return static::insertGetId($_data);
    }

}
