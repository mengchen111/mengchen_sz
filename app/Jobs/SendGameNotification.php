<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\GameNotificationMarquee;
use GuzzleHttp\Client;
use App\Models\OperationLogs;

class SendGameNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationModel;    //公告模型实例
    protected $formData;        //POST数据
    protected $apiAddress;      //游戏服地址

    public $tries = 3;          //最大重试次数
    public $timeout = 10;       //任务执行的最长时间

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($notificationModel, $formData, $apiAddress)
    {
        $this->notificationModel = $notificationModel;
        $this->formData = $formData;
        $this->apiAddress = $apiAddress;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $httpClient = new Client([
            'timeout' => 6,    //设置超时时间
        ]);

        $response = $httpClient->request('POST', $this->apiAddress, [
            'form_params' => $this->formData,   //发送 application/x-www-form-urlencoded POST请求
        ]);

        if (200 == $response->getStatusCode()) {
            if (1 == json_decode($response->getBody()->getContents())->result) {
                $this->notificationModel->sync_state = 3;
                $this->notificationModel->failed_description = '';
                $this->notificationModel->save();

                OperationLogs::add(1, $this->apiAddress, 'POST', '后台队列同步跑马灯公告成功',
                    'Guzzle', json_encode($this->formData));
                return true;
            }

            throw new \Exception('请求发送成功，但同步失败');
        }

        //抛出异常，继续重试，return false 不会继续重试
        throw new \Exception('状态返回码非200，同步失败');
    }

    //如果请求过程中Guzzle抛出异常，则记录在notificationModel模型表中
    public function failed(\Exception $e)
    {
        $this->notificationModel->sync_state = 4;
        $this->notificationModel->failed_description = $e->getMessage();
        $this->notificationModel->save();

        throw $e;   //将异常重新抛出
    }
}
