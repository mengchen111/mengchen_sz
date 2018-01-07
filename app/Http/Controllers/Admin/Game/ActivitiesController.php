<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Game\GameApiService;
use App\Services\Paginator;
use App\Models\OperationLogs;

class ActivitiesController extends Controller
{
    public function getActivitiesList(AdminRequest $request)
    {
        $api = config('custom.game_api_activities_activities-list');
        $records = GameApiService::request('GET', $api);
        krsort($records);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查询活动列表', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($records, $this->per_page, $this->page);
    }
}
