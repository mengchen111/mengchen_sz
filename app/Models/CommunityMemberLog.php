<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityMemberLog extends Model
{
    public $timestamps = false;
    protected $table = 'community_member_log';
    protected $primaryKey = 'id';

    protected $guarded = [
        //
    ];

    protected $hidden = [
        //
    ];

    public function community()
    {
        return $this->hasOne('App\Models\CommunityList', 'id', 'community_id');
    }
}
