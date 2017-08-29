<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopUpPlayer extends Model
{
    protected $table = 'top_up_player';
    protected $primaryKey = 'id';

    protected $fillable = [
        'provider', 'player', 'type', 'amount'
    ];



}