<?php

namespace App\Console\Commands;

use App\Services\Game\ValidCardConsumedService;
use Illuminate\Console\Command;

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
        //缓存数据到redis，这样web端访问的时候就不会卡顿
        ValidCardConsumedService::getAgentTopUpLogsCache();
        $this->logInfo('缓存代理商有效耗卡数据成功');
    }
}
