<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/7/17
 * Time: 09:09
 */

namespace App\Http\Controllers\Admin\Game;


use App\Http\Controllers\Controller;
use App\Jobs\SendGameNotification;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\OperationLogs;
use App\Models\GameNotificationLogin;
use App\Exceptions\CustomException;
use Carbon\Carbon;

class LoginNotificationController extends Controller
{
    protected $per_page = 15;
    protected $order = ['id', 'desc'];

    protected $dateFormat = 'Y-m-d H:i:s';  //日期时间格式
    protected $apiAddress = '';             //游戏服开放的接口的url地址
    protected $formData = [                 //游戏服接口必须要填充的数据，不然报错
        'level' => '',
        'diff_time' => '0',                 //一定要有值，不然接口调用失败
        'img' => '',
        'content_url' => '',
    ];

    public function __construct(Request $request)
    {
        $this->apiAddress = config('custom.game_server_api_address') . '?action=notice.systemSendNOticeToAll';
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
    }

    //创建登录公告接口
    public function create(AdminRequest $request)
    {
        $this->validateMarquee($request);

        $data = $this->intersect($request);

        GameNotificationLogin::create($data);

        OperationLogs::add(Auth::id(), $request->path(), $request->method(), '添加登录公告',
            $request->header('User-Agent'), json_encode($data));

        return [
            'message' => '添加登录公告成功'
        ];
    }

    protected function intersect($request)
    {
        return $request->intersect([
            'order', 'title', 'content', 'pop_frequency', 'start_at', 'end_at',
        ]);
    }

    protected function validateMarquee(Request $request)
    {
        $this->validate($request, [
            'order' => 'required|integer',        //消息排序，小的靠前，最小为1
            'title' => 'required|string|max:255',
            'content' => 'required|max:255',
            'pop_frequency' => 'required|in:1,2',   //弹出频率，1-每日首次，2-每次登录
            'start_at' => 'required|date_format:"Y-m-d H:i:s"',
            'end_at' => 'required|date_format:"Y-m-d H:i:s"',
            'switch' => 'integer|in:1,2',           //默认值2
            'failed_description' => 'nullable|string'
        ]);
        if ($request->end_at <= $request->start_at) {   //不用转为时间戳，可以直接比较
            throw new CustomException('结束时间不能小于开始时间');
        }
    }

    //启用登录公告，发送http请求到游戏服接口
    public function enable(AdminRequest $request, GameNotificationLogin $notification)
    {
        $this->validateSyncState($notification);

        $notification->switch = 1;   //开启公告
        $notification->sync_state = 2;
        $notification->save();       //更改同步状态为同步中（写入数据库）

        $this->prepareFormData($notification->toArray());

        //分发任务到队列，异步同步公告状态
        dispatch(new SendGameNotification($notification, $this->formData, $this->apiAddress));

        OperationLogs::add(Auth::id(), $request->path(), $request->method(), '启用登录公告',
            $request->header('User-Agent'));

        return [
            'message' => '开启公告成功，等待同步完成'
        ];
    }

    //停用登录公告，发送http请求到游戏服接口
    public function disable(AdminRequest $request, GameNotificationLogin $notification)
    {
        $this->validateSyncState($notification);

        $notification->switch = 2;   //停用公告
        $notification->sync_state = 2;
        $notification->save();       //更改同步状态为同步中（写入数据库）

        $this->prepareFormData($notification->toArray());

        dispatch(new SendGameNotification($notification, $this->formData, $this->apiAddress));

        OperationLogs::add(Auth::id(), $request->path(), $request->method(), '禁用登录公告',
            $request->header('User-Agent'));

        return [
            'message' => '停用公告成功，等待同步完成'
        ];
    }

    //构建POST需要提交的数据
    protected function prepareFormData($data)
    {
        $this->formData['type'] = 3;                    //登录公告类型
        $this->formData['status'] = $data['switch'];    //公告状态
        $this->formData['id'] = $data['id'];
        $this->formData['no'] = $data['order'];
        $this->formData['title'] = $data['title'];
        $this->formData['content'] = $data['content'];
        $this->formData['pop_rate'] = $data['pop_frequency'];
        $this->formData['stime'] = Carbon::createFromFormat($this->dateFormat, $data['start_at'])->timestamp;
        $this->formData['etime'] = Carbon::createFromFormat($this->dateFormat, $data['end_at'])->timestamp;
    }

    //登录公告列表
    public function show(AdminRequest $request)
    {
        OperationLogs::add(Auth::id(), $request->path(), $request->method(),
            '管理员查看登录公告列表', $request->header('User-Agent'), json_encode($request->all()));

        return GameNotificationLogin::orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }

    //编辑登录公告
    public function update(AdminRequest $request, GameNotificationLogin $notification)
    {
        $this->validateMarquee($request);

        $this->validateSyncState($notification);

        $data = $this->intersect($request);

        $data = array_merge($data, [
            'switch' => 2,              //公告状态改为关闭
            'sync_state' => 1,          //同步状态改为未同步
            'failed_description' => '', //清空错误描述
        ]);

        $notification->update($data);

        OperationLogs::add(Auth::id(), $request->path(), $request->method(), '编辑登录公告',
            $request->header('User-Agent'), json_encode($data));

        return [
            'message' => '更新登录公告成功'
        ];
    }

    public function destroy(AdminRequest $request, GameNotificationLogin $notification)
    {
        $this->validateEnabledState($notification);

        $notification->delete();

        OperationLogs::add(Auth::id(), $request->path(), $request->method(),
            '删除登录公告', $request->header('User-Agent'), $notification->toJson());

        return [
            'message' => '删除成功'
        ];
    }

    protected function validateSyncState(GameNotificationLogin $notification)
    {
        if ($notification->is_syncing) {
            throw new CustomException('此公告正在同步中，禁止操作');
        }
    }

    protected function validateEnabledState(GameNotificationLogin $notification)
    {
        if ($notification->is_enabled) {
            throw new CustomException('此公告已经同步到游戏服，不能删除。请先停用此公告');
        }
    }
}