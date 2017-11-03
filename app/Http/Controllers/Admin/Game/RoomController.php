<?php

namespace App\Http\Controllers\Admin\Game;

use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use App\Http\Controllers\Controller;

class RoomController extends Controller
{
    public function create(Request $request)
    {
        return 'create';
    }

    public function getRoomType(Request $request)
    {
        return 'getRoomtype';
        //todo 获取roomtype
    }
}
