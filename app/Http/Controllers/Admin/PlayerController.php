<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/4/17
 * Time: 15:01
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game\Player;
use App\Models\OperationLogs;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    protected $per_page = 15;
    protected $order = ['rid', 'desc'];

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
    }

    //查看玩家列表
    public function show(Request $request)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '管理员查看玩家列表', $request->header('User-Agent'), json_encode($request->all()));

        //搜索玩家
        if ($request->has('filter')) {
            $players = array_column(Player::where('rid', 'like', "%{$request->filter}%")->get()->toArray(), 'rid');
            if (empty($players)) {
                return null;
            }
            return  Player::with(['card', 'items'])
                ->whereIn('rid', $players)
                ->orderBy($this->order[0], $this->order[1])
                ->paginate($this->per_page);
        }

        return Player::with(['card', 'items'])->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }
}