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

class FriendRoomController extends Controller
{
    protected $per_page = 15;
    protected $order = ['owner', 'desc'];

    protected $apiAddress = '';

    public function __construct(Request $request)
    {
        $this->apiAddress = env('GAME_SERVER_API_ADDRESS') . '?action=FriendRoom.forceClearRoom';
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
    }

    //查看好友房列表
    public function show(AdminRequest $request)
    {
        //只搜索房间ID
        return FriendRoom::where('id', 'like', "%{$request->filter}%")
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }

    //解散好友房
    public function dismiss(AdminRequest $request, $ownerId)
    {
        $client = new GuzzleHttp\Client([
            'timeout' => 3
        ]);

        try {
            $res = $client->request('GET', "{$this->apiAddress}&id={$ownerId}")
                            ->getBody()
                            ->getContents();
        } catch (\Exception $e) {
            throw new CustomException('调用游戏服接口失败：' . $e->getMessage());
        }

        if (empty(json_decode($res)->result)) {
            throw new CustomException('调用接口成功，但是游戏服返回的结果错误：' . $res);
        }

        return [
            'message' => '解散好友房成功'
        ];
    }
}