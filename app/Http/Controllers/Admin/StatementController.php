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
use App\Models\OperationLogs;
use App\Models\TopUpAdmin;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class StatementController extends Controller
{
    protected $cardTypeId = 1;
    protected $coinTypeId = 2;
    protected $cardTotalKey = 'card_total';     //获取汇总数据时使用的key
    protected $coinTotalKey = 'coin_total';

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

        $cardData = $this->fetChData($this->cardTypeId, $this->cardTotalKey, $dateFormat);
        $coinData = $this->fetchData($this->coinTypeId, $this->coinTotalKey, $dateFormat);

        $data = $this->prepareData($cardData, $coinData);
        $paginatedData = $this->paginateData($data);

        OperationLogs::add(Auth::id(), $request->path(), $request->method(),
            '查看每小时流水报表', $request->header('User-Agent'));

        return $paginatedData;
    }

    public function daily(AdminRequest $request)
    {
        $dateFormat = 'Y-m-d';

        $cardData = $this->fetChData($this->cardTypeId, $this->cardTotalKey, $dateFormat);
        $coinData = $this->fetchData($this->coinTypeId, $this->coinTotalKey, $dateFormat);

        $data = $this->prepareData($cardData, $coinData);
        $paginatedData = $this->paginateData($data);

        OperationLogs::add(Auth::id(), $request->path(), $request->method(),
            '查看每日流水报表', $request->header('User-Agent'));

        return $paginatedData;
    }

    public function monthly(AdminRequest $request)
    {
        $dateFormat = 'Y-m';

        $cardData = $this->fetChData($this->cardTypeId, $this->cardTotalKey, $dateFormat);
        $coinData = $this->fetchData($this->coinTypeId, $this->coinTotalKey, $dateFormat);

        $data = $this->prepareData($cardData, $coinData);
        $paginatedData = $this->paginateData($data);

        OperationLogs::add(Auth::id(), $request->path(), $request->method(),
            '查看每月流水报表', $request->header('User-Agent'));

        return $paginatedData;
    }

    protected function fetchData($itemType, $keyName, $dateFormat)
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
        return TopUpAdmin::get()
            ->where('type', $itemType)
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

    protected function prepareData($cardData, $coinData)
    {
        $cardDataCopy = $cardData;  //需要有个中间变量，不然闭包里面直接更改cardData数组的元素会出问题
        array_walk($cardData, function ($value, $key) use (&$coinData, &$cardDataCopy) {
            if (array_key_exists($key, $coinData)) {
                $coinData[$key] = array_merge($coinData[$key], $value);
                unset($cardDataCopy[$key]);
            }
        });

        $result =  array_merge($coinData, $cardDataCopy);
        krsort($result);                //按照时间倒序排序
        return array_values($result);   //返回索引数组
    }

    protected function paginateData($data)
    {
        $offset = $this->per_page * ($this->page - 1);
        $currentPageData = array_slice($data, $offset, $this->per_page);
        $paginatedData = new LengthAwarePaginator($currentPageData, count($data), $this->per_page, $this->page);
        return $paginatedData;
    }
}