<?php

namespace App\Http\Controllers\Wechat;

use App\Exceptions\CustomException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RedPacketController extends Controller
{
    public function sendRedPacket(Request $request)
    {
        $app = app('wechat');
        $redPacket = $app->lucky_money;
        $data = [
            'mch_billno' => 'aaaa1',    //商户订单号
            'send_name' => '壹壹麻将',           //商户名称（红包发送者名称）
            're_openid' => 'oHbiat7ByBZls5O8EqCA9MqrXZC0',  //红包接收者的openid
            'total_num' => 1,    //红包发送总人数
            'total_amount' => 1,  //红包金额，分为单位
            'wishing' => '祝福语', //红包祝福语
            'client_ip' => '118.31.250.29', //客户端ip，这里填深圳服务器ip
            'act_name' => '活动名称',   //活动名称
            'remark' => '备注',   //备注
        ];

        $result = $redPacket->sendNormal($data);

        return 'send ok';
    }
}
