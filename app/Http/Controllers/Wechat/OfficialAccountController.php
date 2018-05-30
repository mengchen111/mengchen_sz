<?php

namespace App\Http\Controllers\Wechat;

use App\Services\Game\GameApiService;
use App\Services\WechatService;
use EasyWeChat\Message\Text;
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
                        return $this->sendTextMessage(
                            '欢迎来到《壹壹麻将》
                            这是一款专门为江西人民量身打造的亲朋邻里约麻游戏
                            *反作弊提示，玩的安心*
                            *IP精准定位，玩的放心*
                            
                            欢迎您和亲朋邻里一同体验。
                            
                            本游戏仅供娱乐，禁止赌博！
                            发现抽水等赌博行为，请联系官方举报！'
                        );
                        break;
                    case 'unsubscribe': //取消关注
                        $this->handleUnsubscribeEvent($message);
                        break;
                    default:
                        # code...
                        break;
                }
            } elseif ($message->MsgType === 'text') {   //将公众号用户消息转发到客服
                switch ($message->Content){
                    case '客服':
                        return new Transfer();
                        break;
                    default:
                       return $this->sendTextMessage(
                            '《壹壹麻将》欢迎您！
                            客服工作时间：
                            上午8：00-凌晨2：00，
                            请输入“客服”或详细描述您要咨询的问题，客服接入后会帮您解答！（人工客服需逐一接入，给您带来不便还望谅解！）'
                       );
                }
            }
        });

        $response = $this->wechatServer->serve();

        //微信服务器如果
        return $response;
    }
    protected function sendTextMessage($text){
        return new Text([$text]);
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
