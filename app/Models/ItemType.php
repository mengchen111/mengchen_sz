<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/30/17
 * Time: 11:20
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    protected $table = 'item_type';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name'
    ];
}