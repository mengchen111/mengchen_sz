<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/6/17
 * Time: 22:03
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GameNotificationMarquee extends Model
{
    protected $table = 'game_notification_marquee';
    protected $primaryKey = 'id';

    protected $fillable = [
        'priority', 'interval', 'start_at', 'end_at', 'content', 'switch', 'sync_state', 'failed_description'
    ];

    public function getIsEnabledAttribute($value)
    {
        //处于同步状态的 或者 开启状态，且同步成功的公告
        return 2 == $this->attributes['sync_state']
            or (1 == $this->attributes['switch'] && 3 == $this->attributes['sync_state']);
    }

    public function getIsSyncingAttribute()
    {
        return 2 == $this->attributes['sync_state'];
    }
}