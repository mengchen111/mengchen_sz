<?php

namespace App\Console\Commands;

use App\Models\WxRedPacketLog;
use App\Services\Game\GameApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendWxRedPacket extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:send-wx-red-packet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送微信红包';

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
        $redPacketSendList = $this->getRedPacketSendList();          //调用后端接口获取需要发送的openid列表
        foreach ($redPacketSendList as $item) {
            if ($item['player']['openid'] === null) {  //如果未找到此用户的openid，那么忽略之
                $this->logError('id:' . $item['id'] . '未找到玩家' . $item['user_id'] . '的openid，跳过');
                continue;
            }
            if ($item['activity_reward']['name'] !== '红包') {
                $this->logError('id:' . $item['id'] . '玩家' . $item['user_id'] . '的奖励不是红包，跳过');
                continue;
            }
            $redPacketData = $this->buildRedPacketData($item);  //构建发送红包需要的数据
            $redPacket = WxRedPacketLog::create($redPacketData);    //本地数据库创建红包记录
            $this->sendRedPacket($redPacket);
        }
    }

    protected function getRedPacketSendList()
    {
        $redPacketSendListApi = config('custom.game_api_wechat_red-packet_send-list');
        return GameApiService::request('GET', $redPacketSendListApi, [
            'sent' => 0,    //只获取待发送的
        ]);
    }

    protected function buildRedPacketData($item)
    {
        $data = [];
        $data['player_id'] = $item['user_id'];
        $data['nickname'] = $item['player']['nickname'];
        $data['unionid'] = $item['player']['unionid'];
        $data['mch_billno'] = $this->generateMchBillNo($item);   //生成商户订单号
        $data['send_name'] = '壹壹麻将';
        $data['re_openid'] = $item['player']['openid'];
        $data['total_num'] = 1;
        $data['total_amount'] = $item['activity_reward']['goods_count'] * 100;  //单位分
        $data['wishing'] = '感谢您参与壹壹麻将的活动';
        $data['client_ip'] = '118.31.250.29';
        $data['act_name'] = '活动名称';
        $data['remark'] = '备注';
        $data['send_status'] = 0;   //待发送
        return $data;
    }

    //返回用户id + 此item生成时间 + 5位随机数组成的订单号
    protected function generateMchBillNo($item)
    {
        $dateFormat = 'YmdHis';
        $no = $item['user_id'] . Carbon::parse($item['time'])->format($dateFormat)
            . rand(10000, 99999);
        return $no;
    }

    protected function sendRedPacket($redPacket)
    {
        $updateRedPacketApi = config('custom.game_api_wechat_red-packet_update');
        $paramsKey = ['mch_billno', 'send_name', 're_openid', 'total_num', 'total_amount',
            'wishing', 'client_ip', 'act_name', 'remark'];
        $params = collect($redPacket->toArray())->only($paramsKey)->toArray();

        $app = app('wechat');
        $redPacketApp = $app->lucky_money;
        $result = $redPacketApp->sendNormal($params);   //发送红包

        if ($result->return_code === 'SUCCESS') {
            if ($result->result_code === 'SUCCESS') {
                //红包发送结果插入数据库
                $redPacket->send_status = 1;    //将红包状态改为发送成功
                $redPacket->save();

                //更新游戏服务器的红包数据
                GameApiService::request('POST', $updateRedPacketApi, [
                    'id' => $redPacket->id,
                    'sent' => 1,
                    'sent_time' => Carbon::now()->toDateTimeString()
                ]);

                $this->logInfo('红包id：' . $redPacket->id . ' 发送成功');
            } else {
                $redPacket->send_status = 2;    //将红包状态改为发送失败
                $redPacket->error_message = $result->err_code_des;
                $redPacket->save();

                GameApiService::request('POST', $updateRedPacketApi, [
                    'id' => $redPacket->id,
                    'sent' => 2,
                    'sent_time' => Carbon::now()->toDateTimeString(),
                    'error' => $result->err_code_des,
                ]);

                $this->logInfo('红包id：' . $redPacket->id . ' 发送失败, 错误描述：' . $result->err_code_des);
            }
        } else {
            $redPacket->send_status = 2;    //将红包状态改为发送失败
            $redPacket->error_message = '未知错误，result_code不为SUCCESS';
            $redPacket->save();

            GameApiService::request('POST', $updateRedPacketApi, [
                'id' => $redPacket->id,
                'sent' => 2,
                'sent_time' => Carbon::now()->toDateTimeString(),
                'error' => '未知错误，result_code不为SUCCESS',
            ]);

            $this->logInfo('红包id：' . $redPacket->id . ' 发送失败, 未知错误，result_code不为SUCCESS');
        }
    }
}
