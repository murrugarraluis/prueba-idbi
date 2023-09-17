<?php

namespace App\Http\Controllers\Vouchers;

use App\Services\VoucherService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StoreVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $xmlFiles = $request->file('files');

            $xmlContents = [];
            foreach ($xmlFiles as $xmlFile) {
                $xmlContents[] = file_get_contents($xmlFile->getRealPath());
            }

            $user = auth()->user();
            $vouchers = $this->voucherService->storeVouchersFromXmlContents($xmlContents, $user);

            return response([
                'data' => $vouchers,
            ], 201);
        } catch (Exception $exception) {
            return response([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
