<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use App\Http\Controllers\Controller;
use App\Services\Game\GameApiService;
use Illuminate\Http\Request;

class MarqueeController extends Controller
{
    public $api;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->api = config('custom.game_api_marquee');
    }

    public function index(AdminRequest $request)
    {
        $page = $request->get('page', 1);

        return GameApiService::request('GET', $this->api, ['page' => $page]);
    }

    public function store(AdminRequest $request)
    {
        $this->validator($request);
        $result = GameApiService::request('POST', $this->api, $request->all());
        $notify = $result['notify_game'] === true ? '通知游戏接口成功' : $result['notify_game'];

        return $this->res('添加成功 - ' . $notify);
    }

    public function update(AdminRequest $request, $id)
    {
        $this->validator($request);

        $api = $this->api . '/' . $id;

        $result = GameApiService::request('POST', $api, $request->all());
        $notify = $result['notify_game'] === true ? '通知游戏接口成功' : $result['notify_game'];

        return $this->res('修改成功 - ' . $notify);

    }

    public function validator($request)
    {
        $this->validate($request, [
            'level' => 'required|integer',
            'content' => 'required|string',
            'stime' => 'required|date_format:"Y-m-d H:i:s"',
            'etime' => 'required|date_format:"Y-m-d H:i:s"',
            'diff_time' => 'required|integer',
            'status' => 'required|integer',
            'sync' => 'required|integer',
        ]);
    }

    public function destroy(AdminRequest $request, $id)
    {
        $api = $this->api . '/destroy/' . $id;
        $result = GameApiService::request('POST', $api);
        $notify = $result['notify_game'] === true ? '通知游戏接口成功' : $result['notify_game'];

        return $this->res('删除成功 - ' . $notify);
    }
}
