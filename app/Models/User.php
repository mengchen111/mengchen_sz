<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use GroupIdMap;

    protected $table = 'users';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'account', 'password', 'email', 'phone', 'group_id', 'parent_id', 'created_at', 'active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'email', 'phone'
    ];

    /**
     * 自动更新created_at和updated_at字段
     *
     * @var bool
     */
    public $timestamps = true;

    //所属组
    public function group()
    {
        return $this->hasOne('App\Models\Group', 'id', 'group_id');
    }

    //上级代理商
    public function parent()
    {
        if ($this->parent_id == -1) {
            //如果不存在上级则parent返回它自身
            return $this->hasOne('App\Models\User', 'id', 'id');
        }
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

    //定义访问器
    public function getIsLowestAgentAttribute()
    {
        return $this->attributes['group_id'] >= $this->lowestAgentGid;
    }

    public function getIsAdminAttribute()
    {
        return (string)$this->attributes['group_id'] === $this->adminGid;
    }

    public function getIsAgentAttribute()
    {
        return in_array($this->attributes['group_id'], $this->agentGids);
    }

    //指定mail通知channel的地址（默认就为email字段）
    public function routeNotificationForMail()
    {
        return $this->email;
    }

    //查询是否是给定的用户id的子代理商
    public function isChild($parentId)
    {
        return $parentId == $this->parent_id;
    }

    //是否存在子代理商
    public function hasChild()
    {
        return User::where('parent_id', $this->id)->get()->count();
    }

    //钻石代理和黄金代理不能申请库存(只有管理员(和其分离出来的角色)和总代可以申请库存)
    public function isNotValidStockApplicant()
    {
        return in_array($this->group_id, [3, 4]);
    }

    //此代理商给其它代理商的充卡记录(驼峰式的写法，输出给前端会自动转成下划线形式)
    public function agentTopUpRecords()
    {
        return $this->hasMany('App\Models\TopUpAgent', 'provider_id', 'id');
    }

    //此代理商给玩家的充卡记录
    public function playerTopUpRecords()
    {
        return $this->hasMany('App\Models\TopUpPlayer', 'provider_id', 'id');
    }

    //下级代理商
    public function children()
    {
        return $this->hasMany('App\Models\User', 'parent_id', 'id');
    }

    //此代理商所拥有的牌艺馆
    public function communities($status = 1) //默认已审核的
    {
        return CommunityList::where('owner_agent_id', $this->id)
            ->where('status', $status)
            ->get();
    }

    public function wxOrders()
    {
        return $this->hasMany(WxOrder::class);
    }

    /**
     * 判断订单是否完成支付
     * @return mixed
     */
    public function hasOrders()
    {
        return $this->wxOrders()->finishedOrder();
    }

    public function rebates()
    {
        return $this->hasMany(Rebate::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

}
