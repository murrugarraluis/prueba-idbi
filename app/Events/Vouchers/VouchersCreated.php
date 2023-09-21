<?php

namespace App\Events\Vouchers;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VouchersCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param Voucher[] $vouchers
     * @param Voucher[] $vouchers_failed
     * @param User $user
     */
    public function __construct(
        public readonly array $vouchers,
        public readonly array $vouchers_failed,
        public readonly User $user
    ) {
    }
}
