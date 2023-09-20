<?php

namespace App\Http\Controllers\Vouchers\Voucher;

use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DeleteVoucherHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(string $id): Response
    {
        $response = $this->voucherService->deleteVoucherById($id);
        return response([
            'message' => $response->message
        ], $response->status_code);
    }
}
