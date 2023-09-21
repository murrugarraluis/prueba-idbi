<?php

use App\Http\Controllers\Vouchers\GetVouchersHandler;
use App\Http\Controllers\Vouchers\StoreVouchersHandler;
use App\Http\Controllers\Vouchers\Voucher\DeleteVoucherHandler;
use App\Http\Controllers\Vouchers\Voucher\GetVoucherHandler;
use Illuminate\Support\Facades\Route;

Route::prefix('vouchers')->group(
    function () {
        Route::get('/', GetVouchersHandler::class);
        Route::get('/total-amount', [GetVouchersHandler::class, 'getTotalAmount']);
        Route::post('/', StoreVouchersHandler::class, 's');
        Route::delete('/{id}', DeleteVoucherHandler::class);
    }
);
