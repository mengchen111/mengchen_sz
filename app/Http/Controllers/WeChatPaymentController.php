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
            $model = $model->where('user_id', $request->get('filter'));
        }
        return $model->latest()->paginate($this->per_page);
    }

    /**
     * 获取微信订单列表(带分页)
     *
     * @SWG\Get(
     *     path="/api/wechat/order/agent",
     *     description="代理商获取微信订单列表(带分页)",
     *     operationId="wechat.order.agent.get",
     *     tags={"wx-top-up"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/sort",
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/page",
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/per_page",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回订单信息",
     *     ),
     * )
     */
    public function agentOrder(AgentRequest $request)
    {
        return auth()->user()->wxOrders()
            //->latest()
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }

    public function getAgentOrder(AgentRequest $request, WxOrder $order)
    {
        return $order->append('order_qr_code');
    }

    /**
     * 创建微信订单
     *
     * @SWG\Post(
     *     path="/api/wechat/order",
     *     description="创建微信订单",
     *     operationId="wechat.order.create",
     *     tags={"wx-top-up"},
     *     consumes={"application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="wx_top_up_rule_id",
     *         description="微信充值房卡套餐id",
     *         in="formData",
     *         required=true,
     *         type="integer",
     *     ),
     *
     *     @SWG\Response(
     *         response=422,
     *         description="参数验证错误",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/ValidationError"),
     *             },
     *         ),
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="创建微信订单成功",
     *         @SWG\Property(
     *             type="object",
     *             @SWG\Property(
     *                 property="code_url",
     *                 description="code_url",
     *                 type="string",
     *                 example="weixin://wxpay/bizpayurl?pr=tp8cyui",
     *             ),
     *             @SWG\Property(
     *                 property="message",
     *                 description="接口返回消息",
     *                 type="string",
     *                 example="订单创建成功",
     *             ),
     *             @SWG\Property(
     *                 property="order_qr_code",
     *                 description="订单的二维码图片的base64字符串",
     *                 type="string",
     *                 example="iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAIAAAAiOjnJAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAD/klEQVR4nO3dwW7bOBRA0SaY///koIsCsxAwwrh6l5Tsc9ZN7BgXwjNLUV8/Pz+/YNr37jfAexIWCWGREBYJYZEQFglhkRAWCWGREBYJYZEQFglhkRAWCWGREBYJYZEQFglhkRAWCWGREBYJYZEQFglhkRAWiX/Gf+P396JYzw8HOLyNwZMEzv/Al97V+c/e5JP8O65YJIRFQlgkhEVifng/WDY1vzTqXhnAd43nyz7JmZeoX4DPJCwSwiIhLBL58H7Qjavnc/Hgcvn5Cy1bPd81+P9PrlgkhEVCWCSERWL18N7ZteT90iz/OVyxSAiLhLBICIvE+wzvg6vn3et+jg/9s6kJi4SwSAiLxOrhvVuJvrLkfWWWv/K63ZeG7VyxSAiLhLBICItEPrw/Yum529U+uIvmEZ/kv570XnkQYZEQFglhkfi6+QLuGlf2y+86sfLmXLFICIuEsEgIi8Stz3nvTm7p7kEdPM/yyn8AbL+N1hWLhLBICIuEsEjkK++Dd4oOHrA+6CbPXbrbLO+KRUJYJIRFQlgkVm+bWbZ6fuVLw+ABMt0XjptvyHHFIiEsEsIiISwS88P7rhXwl97GwbJpfdBNPuf/4opFQlgkhEVCWCRWD++7DO4b2TXLD269P7BthscQFglhkRAWifyoyG6T+0F33OP5uxqcqXcdQllwxSIhLBLCIiEsEve6YXXXTH0w+OykXaP99lneFYuEsEgIi4SwSKx+wuq5K6NuN60vO6pl2VNhF7jXu+FtCIuEsEgIi8S9TpsZPF9l8B/vOrjm0Q+HcsUiISwSwiIhLBKbb1i9MghvP8r8L17obuvjf7hhlccQFglhkRAWiVvfsHpwkyNirjxDtdvMc/6zVt55E8IiISwSwiKxec/74H6V8/l01xaUwY35L72r7aO9KxYJYZEQFglhkci3zXT3VS7bmX6w7A8cZOWdNyEsEsIiISwS8yvvL62AL9M90mjX+Y5XvkNYeeephEVCWCSERWJ+eF+29Dy4M+Ruk2/9Qgu+QrlikRAWCWGREBaJ1TesXtE9lmhw9XzQlb/Innfek7BICIuEsEisvmF18NSX7jd3u+nPf3bwa8H256+6YpEQFglhkRAWiXs9YbVzZSf+4C7+wYNrrvx/gJV3nkpYJIRFQlgkPmV4P9h1hHp36v1LrLzzVMIiISwSwiKxenjfddrMue6xqN2k3+0pGuGKRUJYJIRFQlgk8uF917nng2vcVzaqX9k+v+tLwwhXLBLCIiEsEsIiMf+QJvjlikVEWCSERUJYJIRFQlgkhEVCWCSERUJYJIRFQlgkhEVCWCSERUJYJIRFQlgkhEVCWCSERUJYJIRFQlgkhEVCWCSEReI3RqAjeMqZJjYAAAAASUVORK5CYII=",
     *             ),
     *         ),
     *     ),
     * )
     */
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
                'order_qr_code' => $this->generateQrCodeStr($result->code_url),
            ];
        }

        //测试环境无需等待回调，直接发货
        $this->doWxCallbackInTestEnv($request, $order);
        return json_encode($returnResult, JSON_UNESCAPED_SLASHES);
    }

    protected function doWxCallbackInTestEnv(Request $request, WxOrder $order)
    {
        if (env('APP_ENV') === 'local' or env('APP_ENV') === 'test') {
            $request->merge(['out_trade_no' => $order->out_trade_no]);
            $this->getNotification($request);
        }
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
        $data['total_fee'] = $rule->price;
        $data['body'] = $this->orderBodyPrefix . $rule->remark;
        $data['spbill_create_ip'] = $request->getClientIp();
        $data['user_id'] = auth()->id();

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
        if (env('APP_ENV') === 'local' or env('APP_ENV') === 'test') {
            $order = WxOrder::where('out_trade_no', ($request->out_trade_no))->first();
            $notify = new \stdClass();
            $notify->transaction_id = 'test';

            $notify->time_end = Carbon::now()->toDateTimeString();
            return $this->orderPaymentSucceed($order, $notify);
        }

        $response = $this->orderApp->payment->handleNotify(function ($notify, $successful) {
            $order = WxOrder::where('out_trade_no', ($notify->out_trade_no))->first();

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

        // 判断用户是否是首次充值成功
        $userOrders = $order->user->hasOrders()->get();
        if ($userOrders->isEmpty()) {
            $order->is_first_order = 1;
        }
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
                InventoryService::addStock($order->user_id, $this->itemType, $amount);

                //更新发货状态
                $order->item_delivery_status = 1;
                $order->save();
            }
        }, 5);
    }

    public function search(AdminRequest $request, $orderNo = '')
    {
        if (empty($orderNo)){
            return [];
        }
        return $this->transSearch($this->searchQuery($orderNo, 'out_trade_no', 'query', false));
    }

    public function transSearch($data = '')
    {
        return [
            'data' => [$data]
        ];
    }

    /**
     * 查询微信订单
     * @param $orderNo string 订单号
     * @param string $condition string 内部订单号查询 / 微信订单号查询
     */
    public function searchOrder($orderNo, $condition = 'out_trade_no')
    {
        switch ($condition) {
            case 'out_trade_no':
                $data = $this->searchQuery($orderNo, $condition, 'query');
                break;
            case 'transaction_id':
                $data = $this->searchQuery($orderNo, $condition, 'queryByTransactionId');
                break;
            default:
                $data = $this->searchQuery($orderNo, 'out_trade_no', 'query');
        }
        return $data;
    }

    /**
     * 查询微信订单
     * @param $orderNo string 订单号
     * @param $field string 微信订单号 / 系统订单号
     * @param $method string 不同查询方法
     * @param $delivery bool 是否发货
     * @throws CustomException
     */
    public function searchQuery($orderNo, $field, $method, $delivery = true)
    {
        $this->addLog('查询微信支付订单');

        $order = WxOrder::where($field, $orderNo)->first();
        if (!$order) {
            throw new CustomException('内部订单不存在');
        }

        if ($delivery && $order->isFinished()) {
            throw new CustomException('订单已经支付成功，并发货');
        }

        $result = $this->orderApp->payment->{$method}($orderNo);

        if ($result->return_code == 'SUCCESS' && $result->trade_state == 'SUCCESS') {
            //金额相同 发货
            if ($result->total_fee == $order->total_fee) {
                if ($delivery) {
                    return $this->orderPaymentSucceed($order, $result);
                }
                return $result;
            }
            throw new CustomException('内部订单金额与微信订单比对不一致，检查订单号是否有误');
        }
        throw new CustomException($result->return_msg);
    }

}
