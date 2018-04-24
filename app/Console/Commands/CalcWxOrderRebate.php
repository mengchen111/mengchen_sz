<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Services\CalcWxOrderRebate as WxOrderRebate;
use App\Console\BaseCommand;

class CalcWxOrderRebate extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:calc-wx-order-rebate {--date= : date time}';

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
        $options = $this->options();
        $date = $this->transDate($options['date']);
        $orderRebate->syncCalcData($date);
        $this->logInfo('计算微信订单返利成功');
    }
    protected function transDate($date)
    {
        //如果为空则 找上个月的数据
        if (empty($date)) {
            $date = Carbon::parse('-1 month')->format('Y-m-d');
        } else {
            $date = Carbon::parse($date)->format('Y-m-d');
        }
        return $date;
    }
}
