<?php

namespace App\Console\Commands;


use App\Services\Game\GameApiService;
use App\Services\Game\PlayerService;
use App\Services\WechatService;

class SyncWxUnionId extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:sync-wx-unionid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步account账号中关注过公众号的玩家的unionid到表unionid_openid中';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $wechatCreateUnionidOpenidApi = config('custom.game_api_wechat_official-account_unionid-openid_create');
        $players = collect(PlayerService::getAllPlayers());

        $wxUserService = app('wechat')->user;
        $openids = WechatService::getUserList();
        foreach ($openids as $openid) {
            $user = $wxUserService->get($openid);
            $player = $players->where('unionid', $user->unionid)->first();
            if (!empty($player)) {
                GameApiService::request('POST', $wechatCreateUnionidOpenidApi, [
                    'unionid' => $player['unionid'],
                    'openid' => $openid,
                ]);
                $this->logInfo('玩家' . $player['id'] . '已关注公众号, openid为:' . $openid . ', 添加到unionid_openid表中');
            }
        }
        $this->info('done');
    }
}
