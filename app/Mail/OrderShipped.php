<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderShipped extends Mailable implements ShouldQueue{//implements ShouldQueue

    use Queueable,SerializesModels;

    /**
     * https://learnku.com/docs/laravel/5.8/mail/3920
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $this->from('service@xmpow.com')
                //->text('emails.test')
                ->view('emails.mpow.coupon.us')
                ->with([
                    'name' => 'test',
                    'start_date' => date('Y-m-d H:i:s'),
                    'end_date' => date('Y-m-d H:i:s'),
                    'type_A' => 'type_A',
                    'link' => 'link',
                    'type_B' => 'type_B',
                    'type_C' => 'type_C',
                    'type_D' => 'type_D',
        ]);

//                        ->attach('/path/to/file', [
//                            'as' => 'name.pdf', //显示名称
//                            'mime' => 'application/pdf', //MIME 类型
//                        ])//要在邮件中加入附件，在 build 方法中使用 attach 方法。attach 方法接受文件的绝对路径作为它的第一个参数：
//                        ->attachData($this->pdf, 'name.pdf', [
//                            'mime' => 'application/pdf',
//                        ])//原始数据附件
        //Mailable 基类的 withSwiftMessage 方法允许你注册一个回调，它将在发送消息之前被调用，原始的 SwiftMailer 消息将作为该回调的参数
//        $this->withSwiftMessage(function ($message) {
//            $message->getHeaders()
//                    ->addTextHeader('Custom-Header', 'HeaderValue');
//        });

        return $this;
    }

}
