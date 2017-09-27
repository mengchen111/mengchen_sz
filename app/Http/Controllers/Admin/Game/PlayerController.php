<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/4/17
 * Time: 15:01
 */

namespace App\Http\Controllers\Admin\Game;

use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use App\Services\Paginator;
use App\Services\GameServer;
use App\Exceptions\CustomException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PlayerController extends Controller
{
    protected $per_page = 15;
    protected $page = 1;
    protected $order = ['uid', 'desc'];
    protected $userListUri = 'users.php';
    protected $userSearchUri = 'user.php';

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
        $this->page = $request->page ?: $this->page;
    }

    //查看玩家列表
    public function show(AdminRequest $request)
    {
        if ($request->has('filter')) {
            $data[] = $this->getOneUser($request->filter)['account'];
        } else {
            //玩家列表缓存三分钟
            $data = Cache::remember('player:accounts', 3, function () {
                return $this->getAllUsers()['accounts'];
            });
            krsort($data);
        }

        $result = $this->paginateData($data);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '管理员查看玩家列表', $request->header('User-Agent'), json_encode($request->all()));

        return $result;
    }

    protected function getAllUsers()
    {
        $gameServer = new GameServer();

        try {
            return $gameServer->request('GET', 'users.php');
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function getOneUser($uid)
    {
        $gameServer = new GameServer();

        try {
            return $gameServer->request('POST', 'user.php', [
                'uid' => $uid,
                'timestamp' => Carbon::now()->timestamp
            ]);
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function paginateData($data)
    {
        $paginator = new Paginator($this->per_page, $this->page);
        return $paginator->paginate($data);
    }
}