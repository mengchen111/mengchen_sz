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

    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'group_id');
    }

    //一对一拿到的是模型，一对多拿到的是集合，一个代理商只能有一种道具类型的库存
    public function inventory()
    {
        return $this->hasOne('App\Models\Inventory', 'user_id', 'id');
    }
}
