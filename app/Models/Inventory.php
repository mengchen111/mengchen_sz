<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/30/17
 * Time: 11:17
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'item_id', 'stock'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function item()
    {
        return $this->hasOne('App\Models\ItemType', 'id', 'item_id');
    }
}