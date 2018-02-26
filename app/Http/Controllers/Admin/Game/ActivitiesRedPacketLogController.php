<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use App\Models\WxRedPacketLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use App\Services\Game\GameApiService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivitiesRedPacketLogController extends Controller
{
    public function getRedPacketLog(AdminRequest $request)
    {
        $data = WxRedPacketLog::when($request->has('filter'), function ($query) use ($request) {
            $searchText = $request->input('filter');
            return $query->where('player_id', 'like', "%{$searchText}%", 'or')
                    ->where('nickname', 'like', "%{$searchText}%", 'or');

            })
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看微信红包发送记录', $request->header('User-Agent'), json_encode($request->all()));

        return $data;
    }

    public function changeStatus(AdminRequest $request, WxRedPacketLog $redPacketLog)
    {
        $this->validate($request, [
            'send_status' => 'required|integer|in:2,3',
        ]);
        $sendStatus = $request->input('send_status');
        $this->doChangeStatus($redPacketLog, $sendStatus);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '更新微信红包发送状态', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '状态更新成功'
        ];
    }

    protected function doChangeStatus(WxRedPacketLog $redPacketLog, $sendStatus)
    {
        DB::transaction(function () use ($redPacketLog, $sendStatus) {
            $redPacketLog->send_status = $sendStatus;
            $redPacketLog->save();

            $updateRedPacketApi = config('custom.game_api_wechat_red-packet_update');
            GameApiService::request('POST', $updateRedPacketApi, [
                'id' => $redPacketLog->log_redbag_id,
                'sent' => (int) $sendStatus === 2 ? $sendStatus : 1,    //本地为补发成功，后端1为已发送，发送失败状态都为2
                'sent_time' => Carbon::now()->toDateTimeString(),
                'error' => $redPacketLog->error_message,
            ]);
        });
    }
}
