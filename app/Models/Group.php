<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/30/17
 * Time: 16:33
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    public $timestamps = false;
    protected $table = 'groups';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'uri_access', 'view_access'
    ];

    public function users()
    {
        return $this->hasMany('App\Models\User', 'group_id', 'id');
    }
}