<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/30/17
 * Time: 11:20
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @SWG\Definition(
 *   definition="ItemType",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       description="道具id",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="name",
 *       description="道具名称",
 *       type="string",
 *       example="房卡"
 *   ),
 * )
 *
 */
class ItemType extends Model
{
    protected $table = 'item_type';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name'
    ];
}