<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/7/17
 * Time: 09:09
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\GameNotificationMarquee;
use App\Models\OperationLogs;
use App\Exceptions\CustomException;

class MarqueeNotificationController extends Controller
{
    public function create(Request $request)
    {
        $this->validateMarquee($request);

        $data = $request->intersect([
            'priority', 'interval', 'start_at', 'end_at', 'content', 'switch'
        ]);

        if (GameNotificationMarquee::create($data)) {
            return [
                'message' => '添加跑马灯公告成功'
            ];
        }
    }

    protected function validateMarquee(Request $request)
    {
        $this->validate($request, [
            'priority' => 'required|in:1,2',        //1-高，2-低
            'interval' => 'required|integer',
            'start_at' => 'required|digits:10',     //时间戳
            'end_at' => 'required|digits:10',
            'content' => 'required|max:255',
            'switch' => 'integer|in:1,2',           //默认值2
        ]);
        if ($request->end_at <= $request->start_at) {
            throw new CustomException('结束时间不能小于开始时间');
        }
    }
}