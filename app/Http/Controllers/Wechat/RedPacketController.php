<?php

namespace App\Http\Controllers\Wechat;

use App\Exceptions\CustomException;
use App\Models\OperationLogs;
use App\Services\WechatService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class RedPacketController extends Controller
{
    public function sendRedPacket(Request $request)
    {
        $app = app('wechat');
        $redPacket = $app->lucky_money;
        $data = [
            'mch_billno' => 'aaaa1',            //商户订单号（每一个红包必须要有不同的商户订单号）
            'send_name' => '壹壹麻将',           //商户名称（红包发送者名称）
            're_openid' => 'oHbiat7ByBZls5O8EqCA9MqrXZC0',  //红包接收者的openid
            'total_num' => 1,                   //红包发送总人数
            'total_amount' => 100,              //红包金额，分为单位, 最少100（1元）
            'wishing' => '祝福语',               //红包祝福语
            'client_ip' => '118.31.250.29',     //客户端ip，这里填深圳服务器ip
            'act_name' => '活动名称',            //活动名称
            'remark' => '备注',                 //备注
        ];

        $result = $redPacket->sendNormal($data);
        if ($result->return_code === 'SUCCESS') {
            if ($result->result_code === 'SUCCESS') {
                //todo 红包发送结果插入数据库
                Log::info('红包发送成功' . ' 微信单号:' . $result->send_listid . ' 用户openid:' . $result->re_openid . ' ' . $result->mch_billno
                 . ' ' . $result->mch_id . ' ' . $result->wxappid . ' ' . $result->total_amount);
            } else {
                Log::info('红包发送失败 ' . 'err_code:' . $result->err_code . ' err_code_des:' . $result->err_code_des);
            }
        } else {
            Log::info('红包请求发送失败');
        }

        return 'send ok';
    }
}
