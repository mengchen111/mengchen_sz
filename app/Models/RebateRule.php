<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @SWG\Definition(
 *   definition="RebateRule",
 *   description="返利规则模型",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       description="id",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="price",
 *       description="金额(单位元)",
 *       type="integer",
 *       format="int32",
 *       example=3000,
 *   ),
 *   @SWG\Property(
 *       property="rate",
 *       description="返利比例单位 %",
 *       type="string",
 *       example="5",
 *   ),
 *   @SWG\Property(
 *       property="remark",
 *       description="备注",
 *       type="string",
 *       example="remark",
 *   ),
 *   allOf={
 *       @SWG\Schema(ref="#/definitions/CreatedAtUpdatedAt"),
 *   }
 * )
 *
 */
class RebateRule extends Model
{
    protected $fillable = [
        'price','rate','remark'
    ];
}
