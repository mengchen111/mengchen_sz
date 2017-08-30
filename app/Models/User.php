<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'account', 'password', 'email', 'phone', 'group_id', 'parent_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //所属组
    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'group_id');
    }

    //上级代理商
    public function parent()
    {
        return $this->hasOne('App\Models\User', 'id', 'parent_id');
    }

    //一对一拿到的是模型，一对多拿到的是集合，拿此关系时需要在道具类型上面做约束
    public function inventory()
    {
        return $this->hasOne('App\Models\Inventory', 'user_id', 'id');
    }

    //代理商下所有类型的道具的库存
    public function inventorys()
    {
        return $this->hasMany('App\Models\Inventory', 'user_id', 'id');
    }
}
