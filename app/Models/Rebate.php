<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Rebate extends Model
{
    protected $fillable = [
        'user_id', 'children_id', 'total_amount', 'rebate_at', 'rebate_price', 'rebate_rule_id', 'remark'
    ];
    protected $appends = [
      'rebate_at'
    ];
    public function children()
    {
        return $this->belongsTo(User::class, 'children_id');
    }

    public function rule()
    {
        return $this->belongsTo(RebateRule::class, 'rebate_rule_id');
    }

    public function getRebateAtAttribute()
    {
        return Carbon::parse($this->attributes['rebate_at'])->format('Y-m');
    }
}
