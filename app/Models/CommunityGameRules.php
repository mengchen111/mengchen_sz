<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityGameRules extends Model
{
    public $timestamps = true;
    protected $table = 'community_game_rules';
    protected $primaryKey = 'id';

    protected $guarded = [
        //
    ];

    protected $hidden = [
        //
    ];

    protected $casts = [
        'rule' => 'array',
    ];

    public function community()
    {
        return $this->hasOne('App\Models\CommunityList', 'id', 'community_id');
    }
}
