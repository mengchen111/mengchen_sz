<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use App\Models\WxRedPacketLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;

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
}
