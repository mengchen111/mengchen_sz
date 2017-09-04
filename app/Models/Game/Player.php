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

    protected $cardId = 1030005;    //房卡的道具类型id号

    protected $visible = [
        'rid', 'nick', 'sex', 'level', 'exp', 'gold', 'online', 'server_id', 'create_time',
        'last_login_time', 'last_offline_time', 'last_login_ip', 'card', 'items'
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

    //在关系之上再做约束，返回指定的模型
    public function card()
    {
        return  $this->hasOne('App\Models\Game\PackItem', 'rid', 'rid')
                    ->where('pack_item.item_id', $this->cardId);
    }

    //拿pack_item表里面的所有关联道具，返回集合
    public function items()
    {
        return $this->hasMany('\App\Models\Game\PackItem', 'rid', 'rid');
    }
}