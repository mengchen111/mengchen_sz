<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopUpAgent extends Model
{
    protected $table = 'top_up_agent';
    protected $primaryKey = 'id';

    protected $fillable = [
        'provider', 'receiver', 'type', 'amount'
    ];



}