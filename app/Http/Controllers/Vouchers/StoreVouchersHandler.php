<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Resources\Vouchers\VoucherResource;
use App\Jobs\ProcessStoreVouchers;
use App\Services\VoucherService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class StoreVouchersHandler
{
    public function __construct()
    {
        //blank
    }

    public function __invoke(Request $request): Response
    {
        try {
            $xmlFiles = $request->file('files');

            if (!is_array($xmlFiles)) {
                $xmlFiles = [$xmlFiles];
            }

            $xmlContents = [];
            foreach ($xmlFiles as $xmlFile) {
                $xmlContents[] = file_get_contents($xmlFile->getRealPath());
            }
            $user = auth()->user();
            // Started Job
            ProcessStoreVouchers::dispatch($xmlContents, $user);

            return response([
                'message' => 'The data upload is being processed in the background.',
            ], ResponseAlias::HTTP_OK);
        } catch (Exception $exception) {
            return response([
                'message' => $exception->getMessage(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
