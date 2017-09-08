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

    public function getStartAtAttribute($value)
    {
        return Carbon::createFromTimestamp($value)->format('Y-m-d H:i:s');
    }

    public function getEndAtAttribute($value)
    {
        return Carbon::createFromTimestamp($value)->format('Y-m-d H:i:s');
    }
}