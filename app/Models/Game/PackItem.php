<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/4/17
 * Time: 19:55
 */

namespace App\Models\Game;


use Illuminate\Database\Eloquent\Model;

class PackItem extends Model
{
    protected $connection = 'mysql-game';
    protected $table = 'pack_item'; //背包数据
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $cardType = 1030005;  //房卡的道具id

    protected $visible = [
        'id', 'rid', 'item_id', 'expire', 'count', 'sort', 'state'
    ];

    protected $fillable = [
        'rid', 'item_id', 'expire', 'count', 'sort', 'state'
    ];
}