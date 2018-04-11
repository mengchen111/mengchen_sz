<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
use App\Http\Requests\AgentRequest;
use App\Models\WxOrder;
use App\Models\WxTopUpRule;
use App\Services\InventoryService;
use App\Traits\WeChatPaymentTrait;
use Carbon\Carbon;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WeChatPaymentController extends Controller
{
    use WeChatPaymentTrait;
    protected $orderApp;
    protected $notifyUrl;
    protected $orderBodyPrefix = '壹壹棋牌-';
    protected $itemType = 1; //房卡

    public function __construct(Request $request)
    {
        $this->orderApp = new Application(config('wechat'));
        $this->notifyUrl = config('wechat.notify_url');

        parent::__construct($request);
    }

    public function index(AdminRequest $request)
    {
        $model = app(WxOrder::class);
        if ($request->has('filter')) {
            $model->where('user_id', $request->get('filter'));
        }
        return $model->latest()->paginate($this->per_page);
    }

    public function agentOrder(AgentRequest $request)
    {
        return auth()->user()->wxOrders()->latest()->paginate($this->per_page);
    }

    public function getAgentOrder(AgentRequest $request, WxOrder $order)
    {
        return $order->append('order_qr_code');
    }

    public function store(Request $request)
    {
        $data = $this->validateCreateOrderRequest($request);
        //创建内部订单
        $order = $this->initializeOrder($data, $request);

        //发起预支付请求
        try {
            $result = $this->preparePayment($order);
        } catch (Exception $exception) {
            //更新订单状态，将异常重新抛出
            $this->orderPreparationFailed($order, $exception->getMessage());
        }
        $this->addLog('创建微信支付订单');

        //如果支付类型为扫码支付，那么额外返回二维码图片的base64编码字符串
        $trade_type = $result->trade_type;
        if (isset($trade_type) && $trade_type == 'NATIVE') {
            $returnResult = [
                'message' => '订单创建成功',
                //'prepay_id' => $result->prepay_id,
                'code_url' => $result->code_url,
                'qr_code' => $this->generateQrCodeStr($result->code_url),
            ];
        }
        return json_encode($returnResult, JSON_UNESCAPED_SLASHES);

    }

    protected function generateQrCodeStr($code_url)
    {
        return base64_encode(QrCode::format('png')->size(200)->generate($code_url));
    }

    protected function validateCreateOrderRequest(Request $request)
    {
        $this->validate($request, [
//            'order_creator_id' => 'required|integer',
            'wx_top_up_rule_id' => 'required|exists:wx_top_up_rules,id',
        ]);
        return $request->intersect([
            'wx_top_up_rule_id'
        ]);
    }

    //创建内部订单
    protected function initializeOrder($data, $request)
    {
        $rule = WxTopUpRule::find($data['wx_top_up_rule_id']);
        $data['out_trade_no'] = $this->createOutTradeNumber();
        $data['total_fee'] = $rule->price * 100;
        $data['body'] = $this->orderBodyPrefix . $rule->remark;
        $data['spbill_create_ip'] = $request->getClientIp();
        $data['user_id'] = auth()->id();

        $orders = auth()->user()->wxOrders;
        if ($orders->isEmpty()) {
            $data['is_first_order'] = 1;
        }
        return WxOrder::create($data);
    }

    //发起预支付请求
    protected function preparePayment($order)
    {
        $attributes = [
            'trade_type' => 'NATIVE', // JSAPI，NATIVE，APP...
            'body' => $order->body,
            'out_trade_no' => $order->out_trade_no,
            'total_fee' => $order->total_fee, // 单位：分
            'spbill_create_ip' => $order->spbill_create_ip,
            'notify_url' => $this->notifyUrl, // 支付结果通知网址，如果不设置则会使用配置里的默认地址
        ];

        $wxorder = new Order($attributes);
        $result = $this->orderApp->payment->prepare($wxorder);

        if ($result->return_code == 'SUCCESS') {
            if ($result->result_code == 'SUCCESS') {
                $this->orderPreparationSucceed($order, $result);
                return $result;
            }

            if ($result->result_code == 'FAIL') {
                $error_msg = $result->err_code . ' - ' . $result->err_code_des;
                $this->orderPreparationFailed($order, $error_msg);
            }
        }

        if ($result->return_code == 'FAIL') {
            $this->orderPreparationFailed($order, $result->return_msg);
        }

        $this->orderPreparationFailed($order, '发起预支付失败');
    }

    //创建订单号（内部订单号，非微信返回的交易id号）
    protected function createOutTradeNumber()
    {
        return md5(mt_rand() . time());
    }

    //更改订单状态为预支付失败
    protected function orderPreparationFailed($order, $msg)
    {
        $order->order_status = 3;
        $order->order_err_msg = $msg;
        $order->save();

        throw new CustomException($msg);
    }

    protected function orderPreparationSucceed($order, $result)
    {
        $order->order_status = 2;
        $order->prepay_id = $result->prepay_id;
        if ($result->trade_type === 'NATIVE') {
            $order['code_url'] = $result->code_url;
        }

        $order->save();
    }

    //微信支付结果通知回调函数
    public function getNotification(Request $request)
    {
        $this->addLog('微信支付订单回调接口');

        //测试环境手动测试发货流程
        if (env('APP_ENV') === 'local' or env('APP_ENV') === 'test)') {
            $order = WxOrder::find($request->out_trade_no);
            $notify = new \stdClass();
            $notify->transaction_id = 'test';

            $notify->time_end = Carbon::now()->toDateTimeString();
            return $this->orderPaymentSucceed($order, $notify);
        }

        $response = $this->orderApp->payment->handleNotify(function ($notify, $successful) {
            $order = WxOrder::find($notify->out_trade_no);

            if (!$order) {
                return 'Order not exist';
            }

            // 如果订单存在
            // 检查订单是否已经更新过支付状态
            if ($order->paid_at) {
                return true;
            }
            // 用户是否支付成功
            if ($successful) {
                $this->orderPaymentSucceed($order, $notify);
            } else {
                $this->orderPaymentFailed($order, $notify);
            }

            return true;
        });
        return $response;
    }

    protected function orderPaymentSucceed($order, $notify)
    {
        $order->order_status = 4;
        $order->transaction_id = $notify->transaction_id;
        $order->paid_at = $notify->time_end;

        $order->save();

        //库存增加
        $this->deliveryItem($order);
    }

    protected function orderPaymentFailed($order, $notify)
    {
        $errMsg = $notify->err_code . '-' . $notify->err_code_des;
        $order->order_status = 5;
        $order->order_err_msg = $errMsg;
        $order->save();
    }

    //发放房卡
    protected function deliveryItem($order)
    {
        DB::transaction(function () use ($order) {
            if (!$order->item_delivery_status) {
                $rule = $order->rule;
                if ($order->is_first_order) {
                    //首充 房卡数 + 赠送 + (房卡数 * 首次赠送 % )
                    $amount = $rule->amount + $rule->give + ($rule->amount * ($rule->first_give / 100));
                } else {
                    $amount = $rule->amount + $rule->give;
                }
                //充值
                InventoryService::addStock(auth()->id(), $this->itemType, $amount);

                //更新发货状态
                $order->item_delivery_status = 1;
                $order->save();
            }
        }, 5);
    }
}
