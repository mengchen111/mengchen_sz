<?php

namespace App\Http\Controllers\Agent;

use App\Http\Requests\AgentRequest;
use App\Services\Game\GameApiService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;

class CommunityGameRecordController extends Controller
{
    public function search(AgentRequest $request, $communityId)
    {
        $this->validate($request, [
            'start_time' => 'required|date_format:"Y-m-d H:i:s"',
            'end_time' => 'required|date_format:"Y-m-d H:i:s"',
            //'community_id' => 'required|integer',
            'player_id' => 'integer'
        ]);
        $params = $request->intersect(['start_time', 'end_time', 'player_id']);
        $params['community_id'] = $communityId;

        $api = config('custom.game_api_community_record_search');
        $records = GameApiService::request('POST', $api, $params);

        $result = $this->formatRecords($records);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查询牌艺馆玩家战绩', $request->header('User-Agent'));

        return $result;
    }

    protected function formatRecords($records)
    {
        $count = 0;
        foreach ($records as &$record) {
            unset($record['options_jstr']); //不显示玩法详情
            if (!empty($record['record_info'])) {
                unset($record['record_info']['rec_jstr']);  //不现实战绩详情
                if ((int)$record['record_info']['if_read'] === 0) {
                    $count += 1;
                }
            }
        }
        $result['unread_records'] = $count;
        $result['records'] = $records;
        return $result;
    }

    public function markRecord(AgentRequest $request, $ruid)
    {
        $api = config('custom.game_api_community_record_mark');
        $params['ruid'] = $ruid;
        $params['if_read'] = 1;
        GameApiService::request('POST', $api, $params);

        return [
            'message' => '查看成功',
        ];
    }
}
