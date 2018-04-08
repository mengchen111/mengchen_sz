<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RebateRule extends Model
{
    protected $fillable = [
        'price','rate','remark'
    ];
}
