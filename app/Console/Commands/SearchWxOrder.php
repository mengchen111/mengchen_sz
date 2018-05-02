<?php

namespace App\Console\Commands;

use App\Console\BaseCommand;
use App\Http\Controllers\WeChatPaymentController;

class SearchWxOrder extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:search-wx-order 
                                {orderNo : out_trade_no} 
                                {--condition= : out_trade_no}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '查找微信订单的状态，发放房卡';

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
        $arguments = $this->arguments();
        $options = $this->options();
        app(WeChatPaymentController::class)->searchOrder($arguments['orderNo'], $options['condition']);
        $this->logInfo('查询微信订单，发放房卡成功');
    }
}
