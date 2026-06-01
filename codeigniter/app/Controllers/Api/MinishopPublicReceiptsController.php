<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BookModel;
use App\Models\MinishopSaleItemModel;
use App\Models\MinishopSaleModel;
use App\Models\MinishopSalePaymentModel;
use App\Services\BookSettingsService;
use Mpdf\Mpdf;

class MinishopPublicReceiptsController extends BaseController
{
    private const DEFAULT_MONEY_DISPLAY = [
        'thousand_separator' => 'comma',
        'show_cents' => true,
    ];

    public function __construct(
        private readonly BookModel $books = new BookModel(),
        private readonly MinishopSaleModel $sales = new MinishopSaleModel(),
        private readonly MinishopSaleItemModel $saleItems = new MinishopSaleItemModel(),
        private readonly MinishopSalePaymentModel $salePayments = new MinishopSalePaymentModel(),
        private readonly BookSettingsService $bookSettings = new BookSettingsService()
    ) {
    }

    public function show(string $bookId, string $saleId)
    {
        $receipt = $this->loadReceipt($bookId, $saleId);

        if ($receipt === null) {
            return $this->notFoundResponse();
        }

        return view('api/minishop_receipt', $this->buildReceiptViewData($receipt, false));
    }

    public function pdf(string $bookId, string $saleId)
    {
        $receipt = $this->loadReceipt($bookId, $saleId);

        if ($receipt === null) {
            return $this->notFoundResponse();
        }

        $html = view('api/minishop_receipt', $this->buildReceiptViewData($receipt, true));
        $tempDir = WRITEPATH . 'mpdf';

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'tempDir' => $tempDir,
        ]);
        $mpdf->SetCompression(false);
        $mpdf->WriteHTML($html);

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="receipt-' . $saleId . '.pdf"')
            ->setBody($mpdf->Output('', 'S'));
    }

    private function loadReceipt(string $bookId, string $saleId): ?array
    {
        $book = $this->books->findActiveBookById($bookId, 'minishop');

        if ($book === null) {
            return null;
        }

        $sale = $this->sales->findOneForReporting($bookId, $saleId);

        if ($sale === null) {
            return null;
        }

        return [
            'book' => $book,
            'sale' => $sale,
            'items' => $this->saleItems->findBySale($saleId),
            'payments' => $this->salePayments->findBySale($saleId),
        ];
    }

    private function notFoundResponse()
    {
        return $this->response->setStatusCode(404)->setBody('Receipt not found.');
    }

    private function buildReceiptViewData(array $receipt, bool $isPdf): array
    {
        $settingsSchema = $this->bookSettings->getSchemaForBookType([
            'requires_currency' => 1,
        ]);
        $settings = $this->bookSettings->normalizeStoredSettings(
            $settingsSchema,
            $receipt['book']['settings_json'] ?? null
        );

        return [
            'book' => $receipt['book'],
            'sale' => $receipt['sale'],
            'items' => $receipt['items'],
            'payments' => $receipt['payments'],
            'isPdf' => $isPdf,
            'moneyDisplay' => is_array($settings['money_display'] ?? null)
                ? $settings['money_display']
                : self::DEFAULT_MONEY_DISPLAY,
        ];
    }
}
