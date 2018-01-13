<?php

namespace App\Listeners\Wechat;

use Overtrue\LaravelWechat\Events\WeChatUserAuthorized;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Game\GameApiService;
use App\Models\OperationLogs;

class webAuthorized
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  WeChatUserAuthorized  $event
     * @return void
     */
    public function handle(WeChatUserAuthorized $event)
    {
        $user = $event->user->getOriginal();
        OperationLogs::add(0, 'event', 'event', '微信 - 网页授权事件', 'event', json_encode($user, JSON_UNESCAPED_UNICODE));

        //授权了的用户都入库，为后续发红包做准备
        $wechatCreateUnionidOpenidApi = config('custom.game_api_wechat_official-account_unionid-openid_create');
        $openId = $user['openid'];
        $unionId = $user['unionid'];
        GameApiService::request('POST', $wechatCreateUnionidOpenidApi, [
            'unionid' => $unionId,
            'openid' => $openId,
        ]);
    }
}
