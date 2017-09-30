<?php

/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/4/17
 * Time: 15:02
 */

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Player extends Model
{
    public $timestamps = false;     //不使用ORM的时间格式化功能（更新数据时也会更改时间格式）
    protected $connection = 'mysql-game';
    protected $table = 'account';
    protected $primaryKey = 'id';
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $visible = [
        'id', 'unionid', 'nickname', 'headimg', 'city', 'gender', 'ycoins', 'ypoints',
        'state', 'permissions', 'create_time', 'last_time'
    ];

    protected $fillable = [];
}