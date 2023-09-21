<?php

namespace App\Jobs;

use App\Services\VoucherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStoreVouchers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $xmlContents;
    private $user;

    /**
     * Create a new job instance.
     */

    public function __construct($xmlContents, $user)
    {
        $this->xmlContents = $xmlContents;
        $this->user = $user;
    }


    /**
     * Execute the job.
     */
    public function handle(VoucherService $voucherService): void
    {
        try {
            $voucherService->storeVouchersFromXmlContents($this->xmlContents, $this->user);
            Log::info('Voucher processed.');
        } catch (\Exception $e) {
            Log::error('Error processing voucher: ' . $e->getMessage());
        }
    }
}
