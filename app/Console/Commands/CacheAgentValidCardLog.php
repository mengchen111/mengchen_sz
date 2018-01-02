<?php

namespace App\Console\Commands;

use App\Services\Game\ValidCardConsumedService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheAgentValidCardLog extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:cache-agent-valid-card-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取包含了有效耗卡数据的代理商给玩家充值记录的缓存';

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
        $cacheKeyForWeb = config('custom.game_server_cache_valid_card_agent_log');
        if (! Cache::has($cacheKeyForWeb)) {  //web访问时使用的key
            $data = ValidCardConsumedService::getAgentTopUpLogs();
            //永久缓存代理商有效耗卡数据，这样web端访问就不会超时
            Cache::forever($cacheKeyForWeb, $data);
            return $this->logInfo('未找到cache key for web，获取数据并永久缓存');
        }

        //获取新的数据，缓存到新的key中（保存三分钟），同时刷新老的key的数据
        //这种机制防止缓存数据失效，而此command在运行时前端web刚好访问页面时获取数据超时
        $cacheKeyNew = config('custom.game_server_cache_valid_card_agent_log') . '_new';
        $cacheDuration = config('custom.game_server_cache_duration');   //最新的数据缓存三分钟

        if (Cache::has($cacheKeyNew)) {
            $this->logInfo('最新数据未失效，不操作');
        } else {
            $timeConsumed = 0;

            Cache::remember($cacheKeyNew, $cacheDuration, function () use ($cacheKeyForWeb, &$timeConsumed) {
                //获取最新的数据，并记录执行时间
                $startTime = Carbon::now()->timestamp;
                $data = ValidCardConsumedService::getAgentTopUpLogs();
                $endTime = Carbon::now()->timestamp;
                $timeConsumed = $endTime - $startTime;

                Cache::forever($cacheKeyForWeb, $data);     //更新for web的数据
                return $data;
            });

            $this->logInfo('缓存代理商有效耗卡最新数据成功，耗时：' . $timeConsumed . '秒');
        }
    }
}
