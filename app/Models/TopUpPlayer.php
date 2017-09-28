<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopUpPlayer extends Model
{
    protected $table = 'top_up_player';
    protected $primaryKey = 'id';

    protected $fillable = [
        'provider_id', 'player', 'type', 'amount', 'created_at'
    ];

    public function provider()
    {
        return $this->hasOne('App\Models\User', 'id', 'provider_id');
    }

    public function item()
    {
        return $this->hasOne('App\Models\ItemType', 'id', 'type');
    }
}