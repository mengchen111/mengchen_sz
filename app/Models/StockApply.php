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
        return $this->belongsTo('App\Models\User', 'id', 'applicant_id');
    }

    public function approver()
    {
        return $this->belongsTo('App\Models\User', 'id', 'approver_id');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\ItemType', 'id', 'item_id');
    }
}