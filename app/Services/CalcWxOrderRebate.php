<?php
/**
 * Created by PhpStorm.
 * User: wangjun
 * Date: 2018/4/9
 * Time: 10:31
 */

namespace App\Services;

use App\Models\Rebate;
use App\Models\RebateRule;
use App\Models\User;
use App\Models\WxOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalcWxOrderRebate
{
    protected $order;
    protected $user;
    protected $rebate;

    public function __construct(WxOrder $order, User $user)
    {
        $this->order = $order;
        $this->user = $user;
    }

    /**
     * 可传入时间 指定具体某个月
     * @param string $date
     * @return bool
     */
    public function syncCalcData($date = '')
    {
        $date = $this->transDate($date);

        //跑订单表，求当月或上月金额总和
        $orders = $this->getDateOrders($date);
        if (!$orders) {
            return false;
        }
        //获取返利规则表数据
        $rules = $this->getRebateRules();
        //foreach循环订单用户 大于等于( >= )返利规则(price) 求出每人返利金额
        $users = $this->getUserRebate($orders, $rules);
        //用户上级代理商 调用$this->rebate
        $this->getHigherAgent($users, $date);
        //入库返利
        $this->saveRebate();
        return $this->rebate;
    }

    /**
     * 跑订单表，求当月或上月金额总和
     * @param string $date
     * @return array
     */
    protected function getDateOrders($date)
    {
        $model = $this->order->select('user_id', DB::raw('sum(total_fee) as total'))->finishedOrder();
        list($year, $month, $day) = explode('-', $date);

        $model = $model->whereYear('paid_at', $year)->whereMonth('paid_at', $month)->groupBy('user_id')->get();
        $orders = [];
        if ($model) {
            foreach ($model as $val) {
                $orders[] = [
                    'user_id' => $val->user_id,
                    'total' => $val->total / 100
                ];
            }
        }
        return $orders;
    }

    /**
     * 用户上级代理商
     * @param $users
     * @param $date
     */
    protected function getHigherAgent($users, $date)
    {
        foreach ($users as $user) {
            if ($result = $this->user->find($user['user_id'])->toArray()) {
                $this->rebate[] = [
                    'user_id' => $result['id'],
                    'children_id' => $result['id'],
                    'total_amount' => $user['total'],
                    'rebate_at' => $date,
                    'rebate_price' => $user['total'] * ($user['rebate_rate'] / 100),
                    'rebate_rule_id' => $user['rebate_rule_id'],
                ];
                $this->findAgent($result, $user, $date);
            }
        }
    }

    /**
     * 递归找 该用户所有的上级
     * @param $user array 用户数据库信息
     * @param $fdata array foreach 找出来的订单用户
     * @param $date
     */
    protected function findAgent($user, $fdata, $date)
    {
        //当 父类id <= 1(也就是管理员) || 异常情况 id == parent_id 跳出
        if ($user['parent_id'] <= 1 || $user['id'] == $user['parent_id']) {
            return;
        } else {
            $result = $this->user->find($user['parent_id'])->toArray();
            $this->rebate[] = [
                'user_id' => $result['id'],
                'children_id' => $fdata['user_id'],
                'total_amount' => $fdata['total'],
                'rebate_at' => $date,
                'rebate_price' => $fdata['total'] * ($fdata['rebate_rate'] / 100),
                'rebate_rule_id' => $fdata['rebate_rule_id'],
            ];
            $this->findAgent($result, $fdata, $date);
        }
    }

    /**
     * foreach循环订单用户 大于等于( >= )返利规则(price) 求出每人返利金额
     * @param $orders
     * @param $rules
     * @return array
     */
    protected function getUserRebate($orders, $rules)
    {
        //循环找用户返利情况
        foreach ($orders as $k => $order) {
            foreach ($rules as $rule) {
                if ($order['total'] >= $rule->price) {
                    $orders[$k]['rebate_rate'] = $rule->rate;
                    $orders[$k]['rebate_rule_id'] = $rule->id;
                }
            }
        }
        //返回 达到返利规则 的用户
        return array_filter($orders, function ($val) {
            return isset($val['rebate_rate']) && isset($val['rebate_rule_id']);
        });
    }

    protected function transDate($date)
    {
        //如果为空则 找上个月的数据
        if (empty($date)) {
            $date = Carbon::parse('-1 month')->format('Y-m-d');
        } else {
            $date = Carbon::parse($date)->format('Y-m-d');
        }
        return $date;
    }

    /**
     * 找规则 根据价格升序 ，不然会出现找返利错误
     * @return mixed
     */
    protected function getRebateRules()
    {
        return RebateRule::all()->sortBy('price');
    }

    /**
     * 为防止重复调用 入库多条重复的数据，所以判断一下是否存在
     */
    protected function saveRebate()
    {
        $rebates = $this->rebate;
        if ($rebates) {
            foreach ($rebates as $rebate) {
                list($year, $month, $day) = explode('-', $rebate['rebate_at']);
                //相同年月的 更新，否则 新增
                $result = Rebate::where('user_id', $rebate['user_id'])
                    ->where('children_id', $rebate['children_id'])
                    ->whereYear('rebate_at', $year)->whereMonth('rebate_at', $month)->first();
                if ($result) {
                    $result->update($rebate);
                } else {
                    Rebate::create($rebate);
                }
            }
        }
    }
}