<?php

namespace App\Http\Controllers\Wechat;

use App\Services\Game\GameApiService;
use App\Services\WechatService;
use EasyWeChat\Message\Transfer;
use Illuminate\Http\Request;
use GuzzleHttp;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;

class OfficialAccountController extends Controller
{
    protected $wechat;
    protected $wechatServer;
    protected $http;
    protected $wechatApi = 'https://api.weixin.qq.com/';

    public function __construct()
    {
        $this->wechat = app('wechat');
        $this->wechatServer = $this->wechat->server;
        $this->http = new GuzzleHttp\Client([
            'base_uri' => $this->wechatApi,
            'connect_timeout' => 5,
        ]);
    }

    public function callback(Request $request)
    {
        $this->wechatServer->setMessageHandler(function ($message) use ($request) {
            // 注意，这里的 $message 不仅仅是用户发来的消息，也可能是事件
            // 当 $message->MsgType 为 event 时为事件
            if ($message->MsgType === 'event') {
                OperationLogs::add(0, $request->path(), $request->method(),
                    '微信回调 - 事件:' . $message->Event . ' openid:' . $message->FromUserName,
                    $request->header('User-Agent'), json_encode($request->all()));

                switch ($message->Event) {
                    case 'subscribe':   //关注公众号事件
                        $this->handleSubscribeEvent($message);
                        break;
                    case 'unsubscribe': //取消关注
                        $this->handleUnsubscribeEvent($message);
                        break;
                    default:
                        # code...
                        break;
                }
            } elseif ($message->MsgType === 'text') {   //将公众号用户消息转发到客服
                return new Transfer();
            }
        });

        $response = $this->wechatServer->serve();

        //微信服务器如果
        return $response;
    }

    //如果是关注公众号的事件，就往数据库插入条目(调用接口)
    protected function handleSubscribeEvent($message)
    {
        $wechatCreateUnionidOpenidApi = config('custom.game_api_wechat_official-account_unionid-openid_create');
        $openId = $message->FromUserName;
        $unionId = $this->getSubscriberUnionId($message->FromUserName);
        GameApiService::request('POST', $wechatCreateUnionidOpenidApi, [
            'unionid' => $unionId,
            'openid' => $openId,
        ]);
    }

    protected function getSubscriberUnionId($openId)
    {
        $accessToken = $this->wechat->access_token;
        $token = $accessToken->getToken();
        $res = WechatService::getUnionId($token, $openId);
        return $res['unionid'];
    }

    //如果用户取消关注之后，删除数据库对应条目(调用接口)
    protected function handleUnsubscribeEvent($message)
    {
        $wechatDeleteUnionidOpenidApi = config('custom.game_api_wechat_official-account_unionid-openid_delete');
        $openId = $message->FromUserName;
        GameApiService::request('POST', $wechatDeleteUnionidOpenidApi, [
            'openid' => $openId,
        ]);
    }
}
