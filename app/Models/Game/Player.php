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
    protected $connection = 'mysql-game';
    protected $table = 'role';
    protected $primaryKey = 'rid';
    public $timestamps = false;     //不使用ORM的时间格式化功能（更新数据时也会更改时间格式）
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $visible = [
        'rid', 'nick', 'sex', 'level', 'exp', 'gold', 'online', 'server_id', 'create_time',
        'last_login_time', 'last_offline_time', 'last_login_ip',
    ];

    protected $fillable = [
        'gold',
    ];

    public function getCreateTimeAttribute($value)
    {
        return Carbon::createFromTimestamp($value)->format('Y-m-d H:i:s');
    }

    public function getLastLoginTimeAttribute($value)
    {
        return Carbon::createFromTimestamp($value)->format('Y-m-d H:i:s');
    }

    public function getLastOfflineTimeAttribute($value)
    {
        return Carbon::createFromTimestamp($value)->format('Y-m-d H:i:s');
    }
}