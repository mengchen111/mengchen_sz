<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/5/17
 * Time: 15:03
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class StockApply extends Model
{
    protected $table = 'stock_apply';
    protected $primaryKey = 'id';

    protected $fillable = [
        'applicant_id', 'item_id', 'amount', 'remark', 'state', 'approver_id', 'approver_remark'
    ];

    public function applicant()
    {
        return $this->belongsTo('App\Models\User', 'applicant_id', 'id');
    }

    public function approver()
    {
        return $this->belongsTo('App\Models\User', 'approver_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\ItemType', 'item_id', 'id');
    }

    public function scopeApplyList($query)
    {
        return $query->where('state', 1);
    }

    public function scopeApplyHistory($query)
    {
        return $query->where('state', '!=', 1);
    }
}