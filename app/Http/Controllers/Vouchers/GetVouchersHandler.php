<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Requests\Vouchers\GetVouchersRequest;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Services\VoucherService;
use Illuminate\Http\Response;

class GetVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(GetVouchersRequest $request): Response
    {
        $page = intval($request->query('page'));
        $paginate = intval($request->query('page'));

        $serie = $request->query('serie');
        $number = $request->query('number');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $vouchers = $this->voucherService->getVouchers(
            $page,
            $paginate,
            $serie,
            $number,
            $start_date,
            $end_date
        );

        return response([
            'data' => VoucherResource::collection($vouchers),
        ], 200);
    }
}
