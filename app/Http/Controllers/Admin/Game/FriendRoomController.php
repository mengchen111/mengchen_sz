<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/14/17
 * Time: 16:28
 */

namespace App\Http\Controllers\Admin\Game;

use App\Http\Controllers\Controller;
use App\Models\Game\FriendRoom;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;

class FriendRoomController extends Controller
{
    protected $per_page = 15;
    protected $order = ['keyid', 'desc'];

    public function __construct(Request $request)
    {
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
}