<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id','amount','wechat','phone','status','remark'
    ];
    protected $appends = [
        'withdrawal_status'
    ];
    public $status = [
        '待审核','待发放','已发放','审核拒绝',
    ];

    public function getWithdrawalStatusAttribute()
    {
        return $this->status[$this->attributes['status']];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
