<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CalcWxOrderRebate as WxOrderRebate;

class CalcWxOrderRebate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:calc-wx-order-rebate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '计算微信订单返利';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle(WxOrderRebate $orderRebate)
    {
        $orderRebate->syncCalcData();
        $this->info('计算微信订单返利成功');
    }
}
