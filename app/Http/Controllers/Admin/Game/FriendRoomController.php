<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/14/17
 * Time: 16:28
 */

namespace App\Http\Controllers\Admin\Game;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Models\Game\FriendRoom;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use GuzzleHttp;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\Auth;
use App\Services\GameServer;

class FriendRoomController extends Controller
{
    protected $per_page = 15;
    protected $order = ['owner', 'desc'];

    protected $apiAddress = '';

    public function __construct(Request $request)
    {
        $this->apiAddress = config('custom.game_server_api_address') . '?action=FriendRoom.forceClearRoom';
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
    }

    //查看好友房列表
    public function show(AdminRequest $request)
    {
        OperationLogs::add(Auth::id(), $request->path(), $request->method(), '查看好友房',
            $request->header('User-Agent'));

        //只搜索房间ID
        return FriendRoom::where('id', 'like', "%{$request->filter}%")
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }

    //解散好友房
    public function dismiss(AdminRequest $request, $ownerId)
    {
        $gameServer = new GameServer("{$this->apiAddress}&id={$ownerId}");

        try {
            $data = $gameServer->request('GET');
        } catch (\Exception $exception) {
            throw new CustomException($exception->getMessage());
        }

        OperationLogs::add(Auth::id(), $request->path(), $request->method(), '解散好友房',
            $request->header('User-Agent'), $ownerId);

        return [
            'message' => '解散好友房成功'
        ];
    }
}