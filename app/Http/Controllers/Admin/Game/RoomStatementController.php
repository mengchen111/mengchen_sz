<?php

namespace App\Http\Controllers\Admin\Game;

use App\Exceptions\CustomException;
use App\Exceptions\RoomStatementServiceException;
use App\Http\Requests\AdminRequest;
use App\Services\Game\RoomStatementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class RoomStatementController extends Controller
{
    public function getRoomStatement(AdminRequest $request)
    {
        $this->validate($request, [
            'date' => 'required|date_format:"Y-m-d"',
            //'game_kind' => 'integer',
        ]);

        $date = $request->input('date');
        $gameKind = $request->has('game_kind') ? $request->input('game_kind') : '';

        //今天之前的数据缓存一份到redis，今天的数据实时计算获取
        try {
            if (!Carbon::parse($date)->isToday()) {
                $cacheKey = config('custom.cache_room_statement') . ':' . $date . ':' . $gameKind;
                $data = Cache::rememberForever($cacheKey, function () use ($date, $gameKind) {
                    $roomStatementService = new RoomStatementService($date, $gameKind);
                    return $roomStatementService->computeData();
                });
            } else {
                $roomStatementService = new RoomStatementService($date, $gameKind);
                $data = $roomStatementService->computeData();
            }
        } catch (RoomStatementServiceException $exception) {
            throw new CustomException($exception->getMessage());
        }

        return $data;
    }
}
