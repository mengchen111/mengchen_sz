<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/30/17
 * Time: 11:17
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @SWG\Definition(
 *   definition="Inventory",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       description="id",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="user_id",
 *       description="用户id",
 *       type="integer",
 *       format="int32",
 *       example=1
 *   ),
 *   @SWG\Property(
 *       property="item_id",
 *       description="道具id",
 *       type="integer",
 *       format="int32",
 *       example=1
 *   ),
 *   @SWG\Property(
 *       property="stock",
 *       description="库存数量",
 *       type="integer",
 *       format="int32",
 *       example=987
 *   ),
 *   @SWG\Property(
 *       property="item",
 *       description="道具类型",
 *       type="object",
 *       allOf={
 *           @SWG\Schema(ref="#/definitions/ItemType"),
 *       },
 *   ),
 *   allOf={
 *       @SWG\Schema(ref="#/definitions/CreatedAtUpdatedAt"),
 *   }
 * )
 *
 */
class Inventory extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'item_id', 'stock'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'id', 'user_id');
    }

    public function item()
    {
        return $this->hasOne('App\Models\ItemType', 'id', 'item_id');
    }
}