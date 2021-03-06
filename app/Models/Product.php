<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use App\Database\Eloquent\SoftDeletes;
use App\Models\Platform\Product as platformProduct;
use App\Util\Constant;

class Product extends Model {

    use SoftDeletes;

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    //protected $connection = 'db_victsing';

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
    protected $table = 'product';

    /**
     * Indicates if the model should be timestamped.
     * 时间戳
     * 默认情况下，Eloquent 预期你的数据表中存在 created_at 和 updated_at 。如果你不想让 Eloquent 自动管理这两个列， 请将模型中的 $timestamps 属性设置为 false：
     *
     * @var bool
     */
    public $timestamps = false;

    const CREATED_AT = Constant::DB_TABLE_OLD_CREATED_AT;
    const UPDATED_AT = Constant::DB_TABLE_OLD_UPDATED_AT;

    /**
     * 不可被批量赋值的属性。
     * $guarded 属性包含的是不想被批量赋值的属性的数组。即所有不在数组里面的属性都是可以被批量赋值的。也就是说，$guarded 从功能上讲更像是一个「黑名单」。而在使用的时候，也要注意只能是 $fillable 或 $guarded 二选一
     * 如果想让所有的属性都可以被批量赋值，就把 $guarded 定义为空数组。
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function platform_product() {
        return \App\Services\Platform\ProductService::getModel($this->getStoreId())->hasOne(platformProduct::class, Constant::DB_TABLE_UNIQUE_ID, Constant::DB_TABLE_PRODUCT_UNIQUE_ID);
    }

    /**
     * 属性数据
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metafields() {
        return \App\Services\MetafieldService::getModel($this->getStoreId())->hasMany(Metafield::class, Constant::OWNER_ID, Constant::DB_TABLE_UNIQUE_ID);
    }

}
