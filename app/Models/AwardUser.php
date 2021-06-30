<?php


namespace App\Models;

use App\Models\BaseModel as Model;
use App\Database\Eloquent\SoftDeletes;

class AwardUser extends Model
{
    use SoftDeletes;

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    //protected $connection = 'db_mpow';

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
    protected $table = 'award_user';
}
