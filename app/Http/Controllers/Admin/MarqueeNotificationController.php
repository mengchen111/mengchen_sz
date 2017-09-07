<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/7/17
 * Time: 09:09
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\GameNotificationMarquee;
use App\Models\OperationLogs;
use App\Exceptions\CustomException;
use GuzzleHttp\Client;

class MarqueeNotificationController extends Controller
{
    protected $apiAddress = '';     //游戏服开放的接口的url地址
    protected $formData = [         //游戏服接口必须要填充的数据，不然报错
        'title' => '',
        'img' => '',
        'content_url' => '',
        'no' => '',
        'pop_rate' => '',
    ];

    public function __construct()
    {
        $this->apiAddress = env('GAME_SERVER_API_ADDRESS') . '?action=notice.systemSendNOticeToAll';
    }

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

    //启用跑马灯公告，发送http请求到游戏服接口
    public function enable(Request $request, GameNotificationMarquee $marquee)
    {
        $marquee->sync_state = 2;
        $marquee->save();       //更改同步状态为同步中（写入数据库）

        $marquee->switch = 1;   //开启公告（此更新未保存到数据库）

        $this->prepareFormData($marquee->toArray());

        try {
            $response = $this->sendMarqueeRequest($marquee->switch);
        } catch (ConnectException $e) {
            $marquee->sync_state = 4;
            $marquee->failed_description = $e->getMessage();
            $marquee->save();
            return ['error' => '同步失败：' . $e->getMessage() ];
        }

        if (200 == $response->getStatusCode()) {
            if (1 == json_decode($response->getBody()->getContents())->result) {
                $marquee->sync_state = 3;
                $marquee->failed_description = '';
                $marquee->save();
                return ['message' => '跑马灯公告开启成功'];
            }
        }

        $marquee->sync_state = 4;
        $marquee->save();
        return ['error' => '跑马灯公告开启失败'];
    }

    //停用跑马灯公告，发送http请求到游戏服接口
    public function disable(Request $request, GameNotificationMarquee $marquee)
    {
        $marquee->sync_state = 2;
        $marquee->save();       //更改同步状态为同步中（写入数据库）

        $marquee->switch = 2;   //停用公告（此更新未保存到数据库）

        $this->prepareFormData($marquee->toArray());

        try {
            $response = $this->sendMarqueeRequest($marquee->switch);
        } catch (ConnectException $e) {
            $marquee->sync_state = 4;
            $marquee->failed_description = $e->getMessage();
            $marquee->save();
            return ['error' => '同步失败：' . $e->getMessage() ];
        }

        if (200 == $response->getStatusCode()) {
            if (1 == json_decode($response->getBody()->getContents())->result) {
                $marquee->sync_state = 3;
                $marquee->failed_description = '';
                $marquee->save();
                return ['message' => '跑马灯公告关闭成功'];
            }
        }

        $marquee->sync_state = 4;
        $marquee->save();
        return ['error' => '跑马灯公告关闭失败'];
    }

    //构建POST需要提交的数据
    protected function prepareFormData($data)
    {
        $this->formData['type'] = 1;    //跑马灯公告类型
        $this->formData['id'] = $data['id'];
        $this->formData['level'] = $data['priority'];
        $this->formData['diff_time'] = $data['interval'];
        $this->formData['stime'] = $data['start_at'];
        $this->formData['etime'] = $data['end_at'];
        $this->formData['content'] = $data['content'];
    }

    protected function sendMarqueeRequest($status)
    {
        $this->formData['status'] = $status;    //公告状态

        $httpClient = new Client([
            'timeout' => 5,    //设置超时时间
        ]);

        return $httpClient->request('POST', $this->apiAddress, [
            'form_params' => $this->formData,   //发送 application/x-www-form-urlencoded POST请求
        ]);

    }
}