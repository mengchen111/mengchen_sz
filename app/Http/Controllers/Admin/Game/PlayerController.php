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
use App\Services\Game\GameServer;
use App\Exceptions\CustomException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PlayerController extends Controller
{
    protected $per_page = 15;
    protected $page = 1;
    protected $order = ['uid', 'desc'];

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
            $data[] = $this->getOneUser($request->filter);
        } else {
            //玩家列表缓存三分钟
            $data = Cache::remember('player:accounts', 3, function () {
                return $this->getAllUsers();
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
            $result = $gameServer->request('GET', config('custom.game_server_api_users'));
            return $this->decodeNickname($result['accounts']);
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function getOneUser($uid)
    {
        $gameServer = new GameServer();

        try {
            $result =  $gameServer->request('POST', config('custom.game_server_api_user'), [
                'uid' => $uid,
                'timestamp' => Carbon::now()->timestamp
            ]);

            return $this->decodeNickname($result['account']);
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    protected function decodeNickname($data)
    {
        //获取一个用户时
        if (isset($data['nickname'])) {
            $data['nickname'] = mb_convert_encoding(base64_decode($data['nickname']), 'UTF-8');;
        } else {
            //获取所有用户时
            foreach ($data as &$user) {
                //必须要将base64解码之后的字符串转码成utf8格式，不然无法序列化成json字符串
                $user['nickname'] = mb_convert_encoding(base64_decode($user['nickname']), 'UTF-8');
            }
        }

        return $data;
    }

    protected function paginateData($data)
    {
        return Paginator::paginate($data, $this->per_page, $this->page);
    }
}