<?php

namespace App\Http\Controllers\Agent;

use App\Http\Requests\AgentRequest;
use App\Services\Game\GameApiService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;

class CommunityGameRecordController extends Controller
{
    /**
     *
     * @SWG\Get(
     *     path="/agent/api/community/game-record/{player_id}",
     *     description="查询玩家的战绩",
     *     operationId="agent.community.game-record.get",
     *     tags={"community"},
     *
     *     @SWG\Parameter(
     *         name="player_id",
     *         description="玩家id",
     *         in="path",
     *         required=true,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="start_time",
     *         description="开始时间",
     *         in="query",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="end_time",
     *         description="结束时间",
     *         in="query",
     *         required=true,
     *         type="string",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回战绩详情",
     *     ),
     * )
     */
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
            '查询牌艺馆玩家战绩', $request->header('User-Agent'), json_encode($request->all()));

        return $result;
    }

    protected function formatRecords($records)
    {
        $count = 0;
        foreach ($records['records'] as &$record) {
            unset($record['options_jstr']); //不显示玩法详情
            if (!empty($record['record_info'])) {
                unset($record['record_info']['rec_jstr']);  //不现实战绩详情
                if ((int)$record['record_info']['if_read'] === 0) {
                    $count += 1;
                }
            }
            //大赢家（得分最高者）标识
            array_reduce([2,3,4], function (Array $v1, $v2) use (&$record) {
                //$record['player' . $v2]['is_winner'] = true;    //先将第一个玩家设置为赢家
                if ($record['score_' . $v1[0]] < $record['score_' . $v2]) {
                    //只有当第二个玩家的分数大于第一个玩家的时候才将第二个玩家设置为大赢家
                    $record['player' . $v2]['is_winner'] = true;
                    //将第一个玩家赢家标识取消(可能存在多个大赢家)
                    array_walk($v1, function ($value) use (&$record) {
                        $record['player' . $value]['is_winner'] = false;
                    });
                    return [$v2];
                } elseif ($record['score_' . $v1[0]] === $record['score_' . $v2]) {
                    $record['player' . $v2]['is_winner'] = true;
                    array_walk($v1, function ($value) use (&$record) {
                        $record['player' . $value]['is_winner'] = true;
                    });
                    array_push($v1, $v2);
                    return $v1;
                } else {
                    $record['player' . $v2]['is_winner'] = false;
                    array_walk($v1, function ($value) use (&$record) {
                        $record['player' . $value]['is_winner'] = true;
                    });
                    return $v1;
                }
            }, [1]);
        }
        $result['unread_records'] = $count;
        $result['records'] = $records['records'];
        $result['total_unread_record_cnt'] = $records['total_unread_record_cnt'];
        return $result;
    }

    /**
     *
     * @SWG\Put(
     *     path="/agent/api/community/game-record/mark/{record_info_id}",
     *     description="审查战绩(标记战绩为已读)",
     *     operationId="agent.community.info.put",
     *     tags={"community"},
     *
     *     @SWG\Parameter(
     *         name="record_info_id",
     *         description="战绩id",
     *         in="path",
     *         required=true,
     *         type="integer",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="审查成功",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Success"),
     *             },
     *         ),
     *     ),
     * )
     */
    public function markRecord(AgentRequest $request, $recordInfoId)
    {
        $api = config('custom.game_api_community_record_mark');
        $params['record_info_id'] = $recordInfoId;
        $params['if_read'] = 1;
        GameApiService::request('POST', $api, $params);

        return [
            'message' => '查看成功',
        ];
    }
}
