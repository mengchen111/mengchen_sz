<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityInvitationApplication extends Model
{
    public $timestamps = false;
    protected $table = 'community_invitation_application';
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
