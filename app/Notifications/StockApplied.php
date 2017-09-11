<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class StockApplied extends Notification implements ShouldQueue
{
    use Queueable;

    public $stockApplication;    //库存申请模型
    protected $stockApplyUri = '/admin/stock/apply-list';   //管理员审核库存uri地址

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($stockApplication)
    {
        $this->stockApplication = $stockApplication;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('有新的库存申请')
                    ->greeting('管理员：')
                    ->line("有新的库存申请，信息如下：")
                    ->line("用户：{$this->stockApplication->applicant->account}")
                    ->line("道具类型：{$this->stockApplication->item->name}")
                    ->line("申请数量：{$this->stockApplication->amount}")
                    ->line("申请备注：{$this->stockApplication->remark}")
                    ->line("点击如下连接前往审批")
                    ->action('前往审批', url($this->stockApplyUri));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
