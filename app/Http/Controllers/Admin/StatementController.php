<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/11/17
 * Time: 15:59
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\Models\Group;
use App\Models\OperationLogs;
use App\Models\TopUpAdmin;
use App\Models\User;
use App\Services\Paginator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StatementController extends Controller
{
    protected $cardTypeId = 1;
    protected $coinTypeId = 2;
    protected $cardPurchasedKey = 'card_purchased';     //获取汇总数据时使用的key(代理商购买的)
    protected $coinPurchasedKey = 'coin_purchased';
    protected $cardConsumedKey = 'card_consumed';       //给玩家充值消耗的
    protected $coinConsumedKey = 'coin_consumed';
    protected $adminId = 1;

    protected $per_page = 15;   //每页数据
    protected $page = 1;        //当前页

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->page = $request->page ?: $this->page;
    }

    public function hourly(AdminRequest $request)
    {
        $dateFormat = 'Y-m-d H:00';

        $result = $this->prepareData($dateFormat);

        OperationLogs::add(Auth::id(), $request->path(), $request->method(),
            '查看每小时流水报表', $request->header('User-Agent'));

        return $this->paginateData($result);
    }

    public function daily(AdminRequest $request)
    {
        $dateFormat = 'Y-m-d';

        $result = $this->prepareData($dateFormat);

        OperationLogs::add(Auth::id(), $request->path(), $request->method(),
            '查看每日流水报表', $request->header('User-Agent'));

        return $this->paginateData($result);
    }

    public function monthly(AdminRequest $request)
    {
        $dateFormat = 'Y-m';

        $result = $this->prepareData($dateFormat);

        OperationLogs::add(Auth::id(), $request->path(), $request->method(),
            '查看每月流水报表', $request->header('User-Agent'));

        return $this->paginateData($result);
    }

    protected function prepareData($dateFormat)
    {

        $agentPurchasedData = $this->fetchAgentPurchasedData($dateFormat);  //获取总的道具购买量
        $playerConsumedData = $this->fetchPlayerConsumedData($dateFormat);  //获取给玩家充值的消耗量

        $mergedData = $this->mergeData($agentPurchasedData, $playerConsumedData);   //数据合并
        $sortedData = $this->sortData($mergedData);     //数据排序，以时间倒序
        return $this->fillData($sortedData);         //填充数据，将需要的key补满，数据补0
    }

    protected function fetchAgentPurchasedData($dateFormat)
    {
        $cardData = $this->fetChData('App\Models\TopUpAdmin', $this->cardTypeId, $this->cardPurchasedKey, $dateFormat);
        $coinData = $this->fetchData('App\Models\TopUpAdmin', $this->coinTypeId, $this->coinPurchasedKey, $dateFormat);

        return $this->mergeData($cardData, $coinData);
    }

    protected function fetchPlayerConsumedData($dateFormat)
    {
        $cardData = $this->fetChData('App\Models\TopUpPlayer', $this->cardTypeId, $this->cardConsumedKey, $dateFormat);
        $coinData = $this->fetchData('App\Models\TopUpPlayer', $this->coinTypeId, $this->coinConsumedKey, $dateFormat);

        return $this->mergeData($cardData, $coinData);
    }

    //从数据库拿数据，以时间为key，以数组返回
    protected function fetchData($db, $itemType, $keyName, $dateFormat)
    {
        /* sql查询的形式
        //select SUM(amount) as total, DATE_FORMAT(created_at, "%Y-%m-%d %H:00") as create_date
        //from `top_up_admin` where `type` = ? group by `create_date`
        $coinData = DB::table('top_up_admin')
            ->select(DB::raw('SUM(amount) as coin_total, DATE_FORMAT(created_at, "%Y-%m-%d %H:00") as create_date'))
            ->where('type', $this->coinTypeId)
            ->groupBy('create_date')
            ->get()
            ->keyBy('create_date');
        */

        //集合查询的形式，由php来做计算，降低mysql压力
        return $db::get()
            ->where('type', $itemType)
            ->filter(function ($value, $key) {
                //过滤管理员自己给自己充值的。有可能用户被删除了，但是充值记录还在，此情况也得判断
                if ($value->receiver_id && User::find($value->receiver_id)) {
                    return User::find($value->receiver_id)->group->id != $this->adminId;
                }
                return $value;
            })
            ->groupBy(function($date) use ($dateFormat) {
                return Carbon::parse($date->created_at)->format($dateFormat);
            })
            ->map(function ($item, $key) use ($keyName) {
                return [
                    $keyName => $item->sum('amount'),
                    'date' => $key,
                ];
            })->toArray();
    }

    //把两个数组的数据做合并
    protected function mergeData($firstData, $lastData)
    {
        $firstDataCopy = $firstData;  //需要有个中间变量，不然闭包里面直接更改原数组的元素会出问题
        array_walk($firstData, function ($value, $key) use (&$lastData, &$firstDataCopy) {
            if (array_key_exists($key, $lastData)) {
                $lastData[$key] = array_merge($lastData[$key], $value);
                unset($firstDataCopy[$key]);
            }
        });

        $result =  array_merge($lastData, $firstDataCopy);
        return $result;
    }

    protected function sortData($data)
    {
        krsort($data);                  //按照时间倒序排序
        return array_values($data);     //返回索引数组
    }

    protected function fillData($data)
    {
        $requiredKeys = [                    //每个单元数据中必须的key名
            $this->cardPurchasedKey,
            $this->coinPurchasedKey,
            $this->cardConsumedKey,
            $this->coinConsumedKey,
            'date',
        ];

        return array_map(function ($item) use ($requiredKeys) {
            $existKeys = array_keys($item);                          //此单元中已经存在的key
            $shouldFilledKeys = array_diff($requiredKeys, $existKeys);    //待填充0的key名
            array_walk($shouldFilledKeys, function ($shouldFilledKey) use (&$item) {
                $item[$shouldFilledKey] = 0;
            });
            return $item;
        }, $data);
    }

    //准备分页数据
    protected function paginateData($data)
    {
        $paginator = new Paginator($this->per_page, $this->page);
        return $paginator->paginate($data);
    }

    //每小时流水的图表数据
    public function hourlyChart(AdminRequest $request)
    {
        $dateFormat = 'Y-m-d H:00';

        $result = $this->prepareData($dateFormat);
        $result = array_combine(array_column($result, 'date'), $result);
        ksort($result);
        return $result;
    }
}