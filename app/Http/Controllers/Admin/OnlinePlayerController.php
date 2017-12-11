<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminRequest;
use App\Models\StatisticOnlinePlayer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class OnlinePlayerController extends Controller
{
    public function getOnlinePlayersChartData(AdminRequest $request)
    {
        $this->validate($request, [
            'date' => 'required|date_format:"Y-m-d"'
        ]);

        return StatisticOnlinePlayer::whereDate('created_at', $request->input('date'))
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->created_at)->format('H:i');
            });
    }
}
