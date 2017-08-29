<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopUpAgent extends Model
{
    protected $table = 'top_up_agent';
    protected $primaryKey = 'id';

    protected $fillable = [
        'provider_id', 'receiver_id', 'type', 'amount'
    ];

    public function provider()
    {
        return $this->hasOne('App\Models\User', 'id', 'provider_id');
    }

    public function receiver()
    {
        return $this->hasOne('App\Models\User', 'id', 'receiver_id');
    }
}