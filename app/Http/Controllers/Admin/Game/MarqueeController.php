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
        GameApiService::request('POST', $this->api, $request->all());
        return $this->res('添加成功');
    }

    public function update(AdminRequest $request, $id)
    {
        $this->validator($request);

        $api = $this->api . '/' . $id;

        GameApiService::request('POST', $api, $request->all());
        return $this->res('修改成功');

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
        GameApiService::request('POST', $api);
        return $this->res('删除成功');
    }
}
