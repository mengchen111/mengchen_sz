<?php

namespace App\Models;

use App\Traits\WeChatPaymentTrait;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 *
 * @SWG\Definition(
 *   definition="WxOrder",
 *   description="微信订单模型",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       description="自增id",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="user_id",
 *       description="订单创建者id",
 *       type="integer",
 *       format="int32",
 *       example=540,
 *   ),
 *   @SWG\Property(
 *       property="wx_top_up_rule_id",
 *       description="道具套餐id",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="out_trade_no",
 *       description="内部订单号",
 *       type="string",
 *       example="b09e1759fa9fcd727c478d44287e17e2",
 *   ),
 *   @SWG\Property(
 *       property="spbill_create_ip",
 *       description="终端ip",
 *       type="string",
 *       example="127.0.0.1",
 *   ),
 *   @SWG\Property(
 *       property="total_fee",
 *       description="订单总金额(分)",
 *       type="integer",
 *       format="int32",
 *       example=8000,
 *   ),
 *   @SWG\Property(
 *       property="body",
 *       description="订单说明",
 *       type="string",
 *       example="壹壹棋牌-100张",
 *   ),
 *   @SWG\Property(
 *       property="order_status",
 *       description="订单状态(1-内部订单创建成功,2-预支付订单创建成功,3-预支付订单创建失败,4-支付成功,5-支付失败,6-取消订单成功,7-取消订单失败)",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="status",
 *       description="申请状态(0-待审核,1-审核通过,2-审核不通过)",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="order_err_msg",
 *       description="订单创建和支付过程中微信返回的错误消息",
 *       type="string",
 *       example="",
 *   ),
 *   @SWG\Property(
 *       property="prepay_id",
 *       description="预支付交易会话标识",
 *       type="string",
 *       example="wx241642519272728d09b6176c4184233788",
 *   ),
 *   @SWG\Property(
 *       property="code_url",
 *       description="扫码支付时的二维码链接",
 *       type="string",
 *       example="weixin://wxpay/bizpayurl?pr=6q7yUjJ",
 *   ),
 *   @SWG\Property(
 *       property="openid",
 *       description="微信用户openid",
 *       type="string",
 *       example="openid",
 *   ),
 *   @SWG\Property(
 *       property="transaction_id",
 *       description="微信支付订单号",
 *       type="string",
 *       example="4200000134201805010368644485",
 *   ),
 *   @SWG\Property(
 *       property="paid_at",
 *       description="支付完成时间",
 *       type="string",
 *       example="2018-05-01 19:17:07",
 *   ),
 *   @SWG\Property(
 *       property="total_fee_yuan",
 *       description="订单金额(元)",
 *       type="string",
 *       example="2018-05-01 19:17:07",
 *   ),
 *   @SWG\Property(
 *       property="order_status_name",
 *       description="订单状态名字",
 *       type="string",
 *       example="支付成功",
 *   ),
 *   @SWG\Property(
 *       property="item_delivery_status_name",
 *       description="道具发货状态名字",
 *       type="string",
 *       example="已发货",
 *   ),
 *   allOf={
 *       @SWG\Schema(ref="#/definitions/CreatedAtUpdatedAt"),
 *   }
 * )
 *
 */
class WxOrder extends Model
{
    use WeChatPaymentTrait;

    protected $fillable = [
        'user_id', 'wx_top_up_rule_id', 'out_trade_no', 'total_fee', 'body', 'spbill_create_ip',
        'order_status', 'order_err_msg', 'prepay_id', 'code_url', 'open_id', 'paid_at', 'is_first_order'
    ];
    protected $appends = [
        'total_fee_yuan',
        'order_status_name',
        'item_delivery_status_name'
    ];

    public function rule()
    {
        return $this->belongsTo(WxTopUpRule::class, 'wx_top_up_rule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getOrderQrCodeAttribute()
    {
        $url = $this->attributes['code_url'];
        if (empty($url)) {
            return null;
        }
        return base64_encode(QrCode::format('png')->size(200)->generate($url));
    }

    public function getTotalFeeYuanAttribute()
    {
        return $this->attributes['total_fee'] / 100;
    }

    public function getOrderStatusNameAttribute()
    {
        return $this->orderStatusMap[$this->attributes['order_status']];
    }

    public function getItemDeliveryStatusNameAttribute()
    {
        return $this->itemDeliveryStatusMap[$this->attributes['item_delivery_status']];
    }

    /**
     * 支付成功，发货成功的订单
     * @param $query
     * @return mixed
     */
    public function scopeFinishedOrder($query)
    {
        return $query->where('order_status',4)->where('item_delivery_status',1)->whereNotNull('paid_at');
    }

    public function isFinished()
    {
        return $this->order_status == 4 && $this->item_delivery_status == 1 && $this->paid_at != '';
    }
}
