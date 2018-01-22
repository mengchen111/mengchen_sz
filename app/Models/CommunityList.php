<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityList extends Model
{
    public $timestamps = true;
    protected $table = 'community_list';
    protected $primaryKey = 'id';

    protected $guarded = [
        //
    ];

    protected $hidden = [
        //
    ];

    protected $appends = [
        'members_count',
    ];

    public function ownerAgent()
    {
        return $this->hasOne('App\Models\User', 'id', 'owner_agent_id');
    }

    public function getMembersCountAttribute()
    {
        $members = $this->attributes['members'];
        return empty($members) ? 0 : count(explode(',', $members));
    }
}
