<?php

namespace App\Services;

use App\Enums\Currency;
use App\Events\Vouchers\VouchersCreated;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class VoucherService
{
    public function getVouchers(
        int     $page,
        int     $paginate,
        ?string $serie,
        ?string $number,
        ?string $start_date,
        ?string $end_date
    ): LengthAwarePaginator
    {
        $query = Voucher::query();

        if ($serie) {
            $query->where('serie', $serie);
        }
        if ($number) {
            $query->where('number', $number);
        }

        if ($start_date && $end_date) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date]);
        }

        return $query->paginate(perPage: $paginate, page: $page);
    }

    /**
     * @param string[] $xmlContents
     * @param User $user
     * @return Voucher[]
     * @throws Exception
     */
    public function storeVouchersFromXmlContents(array $xmlContents, User $user): array
    {
        $vouchers = [];
        $vouchers_failed = [];
        foreach ($xmlContents as $xmlContent) {
            $voucher = $this->storeVoucherFromXmlContent($xmlContent, $user);
            // voucher failed
            if ($voucher->failed_message){
                $vouchers_failed[] = $voucher;
            }else{
                $vouchers[] = $voucher;
            }
        }
        // started event to notification
        VouchersCreated::dispatch($vouchers, $vouchers_failed, $user);

        return $vouchers;
    }

    /**
     * @throws Exception
     */
    public function storeVoucherFromXmlContent(string $xmlContent, User $user): Voucher
    {
        $voucher = new Voucher();
        try {
            $xml = new SimpleXMLElement($xmlContent);

            $serie_number = (string)$xml
                ->xpath('//cbc:ID')[0];
            $type_code = (string)$xml
                ->xpath('//cbc:InvoiceTypeCode')[0];
            $currency_code = (string)$xml
                ->xpath('//cbc:DocumentCurrencyCode')[0];

            $issuerName = (string)$xml
                ->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name')[0];
            $issuerDocumentType = (string)$xml
                ->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
            $issuerDocumentNumber = (string)$xml
                ->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

            $receiverName = (string)$xml
                ->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0];
            $receiverDocumentType = (string)$xml
                ->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
            $receiverDocumentNumber = (string)$xml
                ->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

            $totalAmount = (string)$xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0];

            $serie_number_parts = explode('-', $serie_number);

            $voucher->fill([
                'serie' => $serie_number_parts[0],
                'number' => $serie_number_parts[1],
                'type_code' => $type_code,
                'currency_code' => $currency_code,
                'issuer_name' => $issuerName,
                'issuer_document_type' => $issuerDocumentType,
                'issuer_document_number' => $issuerDocumentNumber,
                'receiver_name' => $receiverName,
                'receiver_document_type' => $receiverDocumentType,
                'receiver_document_number' => $receiverDocumentNumber,
                'total_amount' => $totalAmount,
                'xml_content' => $xmlContent,
                'user_id' => $user->id,
            ]);
            $voucher->save();

            foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
                $name = (string)$invoiceLine->xpath('cac:Item/cbc:Description')[0];
                $quantity = (float)$invoiceLine->xpath('cbc:InvoicedQuantity')[0];
                $unitPrice = (float)$invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0];

                $voucherLine = new VoucherLine([
                    'name' => $name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'voucher_id' => $voucher->id,
                ]);

                $voucherLine->save();
            }

        } catch (Exception $e) {
            $voucher->failed_message = $e->getMessage();
        } finally {
            return $voucher;
        }
    }

    public function deleteVoucherById(string $id): object
    {
        try {
            $voucher = Voucher::findOrFail($id);
            $voucher->delete();

            return (object)[
                'message' => 'Voucher successfully deleted.',
                'status_code' => ResponseAlias::HTTP_OK,
            ];
        } catch (\Exception $e) {
            return (object)[
                'message' => 'Unable to delete voucher. Error: ' . $e->getMessage(),
                'status_code' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    public function getTotalAmount(): object
    {

        $PEN = Currency::PEN->value;
        $USD = Currency::USD->value;

        $user = auth()->user();

        $vouchers = $user->vouchers
            ->whereIn('currency_code', [$PEN, $USD])
            ->groupBy('currency_code');

        $totals = $vouchers->map(function ($vouchers) {
            return $vouchers->sum('total_amount');
        });

        return (object)[
            'pen_total' => $totals->get($PEN, 0.0),
            'usd_total' => $totals->get($USD, 0.0),
        ];
    }
}
