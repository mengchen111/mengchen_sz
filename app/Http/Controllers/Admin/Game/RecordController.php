<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use App\Services\Game\GameApiService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RecordController extends Controller
{
    public function show(AdminRequest $request)
    {
        return GameApiService::request('POST', config('custom.game_api_players'), [
            'uid' => '23',
            'df' => 'sdf',
        ]);
    }
}
