<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/30/17
 * Time: 16:33
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @SWG\Definition(
 *   definition="Group",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       description="组id",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="name",
 *       description="组名称",
 *       type="string",
 *       example="总代",
 *   ),
 * )
 *
 */
class Group extends Model
{
    use GroupIdMap;

    public $timestamps = false;
    protected $table = 'groups';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'uri_access', 'view_access'
    ];

    protected $hidden = [
        'uri_access',
    ];

    public function users()
    {
        return $this->hasMany('App\Models\User', 'group_id', 'id');
    }

    public function getViewAccessAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getIsAdminGroupAttribute()
    {
        return (string) $this->attributes['id'] === $this->adminGid;
    }

    public function hasMember()
    {
        return $this->users->count();
    }
}