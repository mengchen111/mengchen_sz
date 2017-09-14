<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/14/17
 * Time: 15:10
 */

namespace App\Models\Game;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FriendRoom extends Model
{
    protected $connection = 'mysql-game';
    protected $table = 'friend_room';     //好友房
    protected $primaryKey = 'keyid';
    public $timestamps = false;
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $visible = [
        'id', 'owner', 'game_type', 'create_time', 'open_id',
    ];

    protected $fillable = [
        'id', 'owner', 'game_type', 'create_time', 'open_id',
    ];

    public function getCreateTimeAttribute($value)
    {
        return Carbon::createFromTimestamp($value)->format($this->dateFormat);
    }
}