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
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use GuzzleHttp;
use App\Services\Paginator;
use Illuminate\Support\Facades\Auth;
use App\Models\OperationLogs;

class CoinRoomController extends Controller
{
    protected $per_page = 15;
    protected $page = 1;
    protected $coinRoomListApi;
    protected $coinRoomDismissApi;
    protected $guzzleClientOptions = [
        'timeout' => 3,
    ];

    public function __construct(Request $request)
    {
        $this->coinRoomListApi = config('custom.game_server_api_address') . '?action=Room.getRooms';
        $this->coinRoomDismissApi = config('custom.game_server_api_address') . '?action=Room.dismissRoomById';
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->page = $request->page ?: $this->page;
    }

    //查看金币房列表，后端金币房信息没有放在数据库，需要调接口查询
    public function show(AdminRequest $request)
    {
        $data = $this->getCoinRoomList($this->coinRoomListApi);

        OperationLogs::add(Auth::id(), $request->path(), $request->method(), '查看金币房',
            $request->header('User-Agent'));

        return $this->paginateData($data);
    }

    protected function getCoinRoomList($apiAddress)
    {
        $client = new GuzzleHttp\Client($this->guzzleClientOptions);

        try {
            $res = $client->request('GET', $apiAddress)
                ->getBody()
                ->getContents();
        } catch (\Exception $e) {
            throw new CustomException('调用游戏服接口失败：' . $e->getMessage());
        }

        if (empty(json_decode($res)->result)) {
            throw new CustomException('调用接口成功，但是游戏服返回的结果错误：' . json_encode($res));
        }

        return json_decode($res, true)['data'];     //返回数组
    }

    //解散金币房
    public function dismiss(AdminRequest $request, $roomId)
    {
        $params = [
            'roomid' => $roomId,
        ];

        $msg = $this->sendDismissRequest($this->coinRoomDismissApi, $params);

        OperationLogs::add(Auth::id(), $request->path(), $request->method(), '解散金币房',
            $request->header('User-Agent'), $roomId);

        return [
            'message' => $msg
        ];
    }

    protected function sendDismissRequest($apiAddress, $params)
    {
        $client = new GuzzleHttp\Client($this->guzzleClientOptions);

        try {
            $res = $client->request('POST', $apiAddress, [
                    'form_params' => $params,
                ])
                ->getBody()
                ->getContents();
        } catch (\Exception $e) {
            throw new CustomException('调用游戏服接口失败：' . $e->getMessage());
        }

        if (empty(json_decode($res)->result)) {
            throw new CustomException('调用接口成功，但是游戏服返回的结果错误：' . $res);
        }

        return json_decode($res)->resultMsg;
    }

    protected function paginateData($data)
    {
        $paginator = new Paginator($this->per_page, $this->page);
        return $paginator->paginate($data);
    }
}