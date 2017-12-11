<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminRequest;
use App\Models\StatisticOnlinePlayer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\OperationLogs;

class OnlinePlayerController extends Controller
{
    public function getOnlinePlayersChartData(AdminRequest $request)
    {
        $this->validate($request, [
            'date' => 'required|date_format:"Y-m-d"'
        ]);

        $data = StatisticOnlinePlayer::whereDate('created_at', $request->input('date'))
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->created_at)->format('H:i');
            });

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取在线玩家图表数据', $request->header('User-Agent'), json_encode($request->all()));

        return $data;
    }
}
