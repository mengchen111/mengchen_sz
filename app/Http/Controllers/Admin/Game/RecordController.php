<?php

namespace App\Http\Controllers\Admin\Game;

use App\Exceptions\CustomException;
use App\Exceptions\GameApiServiceException;
use App\Http\Requests\AdminRequest;
use App\Services\Game\GameApiService;
use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RecordController extends Controller
{
    protected $per_page = 15;
    protected $page = 1;
    protected $positionMap = [
        1 => 'east',
        2 => 'south',
        3 => 'west',
        4 => 'north',
    ];          //牌桌方位的映射关系
    protected $gameTypeMap = [
        4 => '惠州麻将',
    ];

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->page = $request->page ?: $this->page;
    }

    public function show(AdminRequest $request)
    {
        try {
            $api = config('custom.game_api_records');
            $records = GameApiService::request('GET', $api);
            krsort($records);
            return Paginator::paginate($records, $this->per_page, $this->page);
        } catch (GameApiServiceException $exception) {
            throw new CustomException($exception->getMessage());
        }
    }

    //战绩信息
    public function search(AdminRequest $request)
    {
        $searchUid = $this->filterSearchRequest($request);

        if ('0' === $searchUid) {
            return Paginator::paginate([]);
        }

        try {
            $api = config('custom.game_api_records');
            $records = GameApiService::request('POST', $api, [
                'uid' => $searchUid,
            ]);
            krsort($records);

            foreach ($records as &$record) {
                $record['game_type'] = $this->gameTypeMap[$record['infos']['kind']];
                $record['time'] = $record['infos']['ins_time'];

                $recordDetail = json_decode($record['infos']['rec_jstr'], true);
                $record['room_id'] = $recordDetail['room']['room_id'];
                $record['owner_id'] = $recordDetail['room']['owner_uid'];

                unset($record['infos']);
            }

            return Paginator::paginate($records, $this->per_page, $this->page);
        } catch (GameApiServiceException $exception) {
            throw new CustomException($exception->getMessage());
        }
    }

    //查询指定战绩id的战绩流水
    public function getRecordInfo(AdminRequest $request, $recId)
    {
        try {
            $api = config('custom.game_api_record_info');
            $record = GameApiService::request('POST', $api, [
                'rec_id' => $recId,
            ]);
            $recordDetail = json_decode($record['rec_jstr'], true);

            $result['rounds'] = $this->getRounds($recordDetail);    //战绩流水
            $result['ranking'] = $this->getRanking($recordDetail);  //总分排行

            return $result;
        } catch (GameApiServiceException $exception) {
            throw new CustomException($exception->getMessage());
        }
    }

    //获取总分排行数据
    protected function getRanking($recordDetail)
    {
        $ranking = $recordDetail['players'];
        $roomOwnerId = $recordDetail['room']['owner_uid'];
        $roundsCount = count($recordDetail['rounds_info']);
        foreach ($ranking as &$player) {
            $player['is_root_owner'] = $player['uid'] === $roomOwnerId ?: false;
            $player['rounds_count'] = $roundsCount;
        }
        //根据总分排倒序
        $ranking = collect($ranking)->sortByDesc('score')->values()->all();
        return $ranking;
    }

    //获取对局的战绩流水
    protected function getRounds($recordDetail)
    {
        $rounds = $recordDetail['rounds_info'];
        $playersMap = $this->getPlayersMap($recordDetail['players']);
        $newRounds = [];
        foreach ($rounds as $round) {
            foreach ($round as &$player) {
                $roundNum = $player['round'];           //第几局
                $player['headimg'] = $playersMap[$player['uid']]['headimg'];
                $player['nickname'] = $playersMap[$player['uid']]['nickname'];
            }
            //给每局数据生成roundx的key, 每局的玩家信息以方位为key
            $newRounds['round' . $roundNum] = array_combine($this->positionMap, $round);
        }

        return $newRounds;
    }

    //返回已玩家id为key的数组
    protected function getPlayersMap($players)
    {
        return array_combine(array_column($players, 'uid'), $players);
    }

    protected function filterSearchRequest($request)
    {
        $this->validate($request, [
            'uid' => 'required|numeric',
        ]);
        return $request->uid;
    }
}
