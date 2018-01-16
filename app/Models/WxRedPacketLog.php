<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WxRedPacketLog extends Model
{
    protected $table = 'wx_red_packet_log';
    protected $primaryKey = 'id';

    protected $guarded = [
        //
    ];

    protected $hidden = [
        //
    ];
}
