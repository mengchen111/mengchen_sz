<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/4/17
 * Time: 15:01
 */

namespace App\Http\Controllers\Admin\Game;

use App\Exceptions\GameApiServiceException;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use App\Services\Paginator;
use App\Services\Game\GameServer;
use App\Services\Game\PlayerService;
use App\Exceptions\CustomException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PlayerController extends Controller
{
    protected $per_page = 15;
    protected $page = 1;
    protected $order = ['id', 'desc'];

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
        $this->page = $request->page ?: $this->page;
    }

    //查看玩家列表
    public function show(AdminRequest $request)
    {
        $this->validateUid($request);

        try {
            if ($request->has('filter')) {
                $data = PlayerService::searchPlayers($request->filter);
            } else {
                $cacheKey = config('custom.game_server_cache_players');
                $cacheDuration = config('custom.game_server_cache_duration');

                //玩家列表缓存三分钟
                $data = Cache::remember($cacheKey, $cacheDuration, function () {
                    return PlayerService::getAllPlayers();
                });
                krsort($data);
            }
        } catch (GameApiServiceException $e) {
            throw new CustomException($e->getMessage());
        }

        $result = $this->paginateData($data);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '管理员查看玩家列表', $request->header('User-Agent'), json_encode($request->all()));

        return $result;
    }

    protected function paginateData($data)
    {
        return Paginator::paginate($data, $this->per_page, $this->page);
    }

    protected function validateUid($request)
    {
        try {
            $this->validate($request, [
                'filter' => 'required|numeric',
            ]);
        } catch (ValidationException $exception) {
            throw new CustomException('待查询的玩家id必须为数字');
        }
    }
}