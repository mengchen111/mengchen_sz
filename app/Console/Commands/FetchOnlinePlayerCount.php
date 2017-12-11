<?php

namespace App\Console\Commands;

use App\Models\StatisticOnlinePlayer;
use App\Services\Game\PlayerService;
use Carbon\Carbon;

class FetchOnlinePlayerCount extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:fetch-online-player-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每十分钟一次获取在线人数';

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
        $onlinePlayerCount = PlayerService::getOnlinePlayersAmount();
        $inGamePlayerCount = PlayerService::getInGamePlayersCount();
        StatisticOnlinePlayer::create([
            'online_count' => $onlinePlayerCount,
            'playing_count' => $inGamePlayerCount,
        ]);
        return $this->LogInfo('在线玩家数量入库成功');
    }
}
