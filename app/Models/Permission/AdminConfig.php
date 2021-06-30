<?php

namespace App\Models\Permission;

use App\Models\BaseModel as Model;
use App\Database\Eloquent\SoftDeletes;

class AdminConfig extends Model {

    use SoftDeletes;

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'db_permission';

    const TABLE_ALIAS = 'pac';
}
