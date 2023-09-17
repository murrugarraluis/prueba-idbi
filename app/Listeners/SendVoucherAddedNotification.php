<?php

namespace App\Listeners;

use App\Events\Vouchers\VouchersCreated;
use App\Mail\VouchersCreatedMail;
use Illuminate\Support\Facades\Mail;

class SendVoucherAddedNotification
{
    public function handle(VouchersCreated $event): void
    {
        $mail = new VouchersCreatedMail($event->vouchers, $event->user);
        Mail::to($event->user->email)->send($mail);
    }
}
