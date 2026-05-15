<?php

namespace App\Services;

use App\Models\BookModel;
use App\Models\MinishopCustomerModel;
use App\Models\MinishopProductModel;
use App\Models\MinishopSaleItemModel;
use App\Models\MinishopSaleModel;
use App\Models\MinishopSalePaymentModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;
use RuntimeException;

class MinishopSalesService
{
    private BaseConnection $db;

    public function __construct(
        private readonly BookModel $books = new BookModel(),
        private readonly MinishopProductModel $products = new MinishopProductModel(),
        private readonly MinishopCustomerModel $customers = new MinishopCustomerModel(),
        private readonly MinishopSaleModel $sales = new MinishopSaleModel(),
        private readonly MinishopSaleItemModel $saleItems = new MinishopSaleItemModel(),
        private readonly MinishopSalePaymentModel $payments = new MinishopSalePaymentModel(),
        ?BaseConnection $db = null
    ) {
        $this->db = $db ?? Database::connect();
    }

    public function createSale(string $userId, string $bookId, array $payload): array
    {
        $book = $this->books->findOwnedActiveBook($userId, $bookId, 'minishop');

        if ($book === null) {
            throw new RuntimeException('Book not found.');
        }

        $currencyCode = strtoupper(trim((string) ($payload['currency_code'] ?? '')));
        $discountAmount = $this->normalizeMoney($payload['discount_amount'] ?? 0);
        $soldAt = trim((string) ($payload['sold_at'] ?? ''));
        $note = $this->normalizeOptionalString($payload['note'] ?? null);
        $customerId = trim((string) ($payload['customer_id'] ?? ''));
        $items = $payload['items'] ?? null;
        $initialPayments = $payload['payments'] ?? [];

        if ($currencyCode === '' || strlen($currencyCode) !== 3) {
            throw new InvalidArgumentException('Currency code must be a 3-letter code.');
        }

        if (! is_array($items) || $items === []) {
            throw new InvalidArgumentException('At least one sale item is required.');
        }

        if (! is_array($initialPayments)) {
            throw new InvalidArgumentException('Payments payload must be a valid list.');
        }

        if ($discountAmount < 0) {
            throw new InvalidArgumentException('Discount amount cannot be negative.');
        }

        if ($soldAt === '') {
            throw new InvalidArgumentException('Sale date and time is required.');
        }

        $customer = null;

        if ($customerId !== '') {
            $customer = $this->customers->findExistingByIdAndBook($bookId, $customerId);

            if ($customer === null) {
                throw new InvalidArgumentException('Customer not found.');
            }
        }

        $normalizedItems = $this->normalizeSaleItems($bookId, $items);
        $subtotalAmount = $this->calculateSubtotal($normalizedItems);

        if ($discountAmount > $subtotalAmount) {
            throw new InvalidArgumentException('Discount amount cannot exceed subtotal.');
        }

        $totalAmount = round($subtotalAmount - $discountAmount, 2);
        $paidAmount = $this->sumNormalizedPayments($initialPayments, $currencyCode);
        $summary = $this->makePaymentSummary($totalAmount, $paidAmount);
        $saleId = $this->newUuid();
        $timestamp = $this->utcNow();

        $this->db->transException(true)->transStart();

        try {
            $created = $this->sales->insert([
                'id' => $saleId,
                'book_id' => $bookId,
                'created_by' => $userId,
                'customer_id' => $customer['id'] ?? null,
                'currency_code' => $currencyCode,
                'subtotal_amount' => $this->formatMoney($subtotalAmount),
                'discount_amount' => $this->formatMoney($discountAmount),
                'total_amount' => $this->formatMoney($totalAmount),
                'paid_amount' => $this->formatMoney($summary['paid_amount']),
                'due_amount' => $this->formatMoney($summary['due_amount']),
                'payment_status' => $summary['payment_status'],
                'note' => $note,
                'sold_at' => $soldAt,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            if ($created === false) {
                throw new RuntimeException('Unable to create sale right now.');
            }

            foreach ($normalizedItems as $item) {
                $itemCreated = $this->saleItems->insert([
                    'id' => $this->newUuid(),
                    'sale_id' => $saleId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'],
                    'quantity' => $this->formatQuantity($item['quantity']),
                    'unit_price' => $this->formatMoney($item['unit_price']),
                    'line_total' => $this->formatMoney($item['line_total']),
                ]);

                if ($itemCreated === false) {
                    throw new RuntimeException('Unable to save sale items right now.');
                }

                $newQuantity = round($item['product_quantity'] - $item['quantity'], 3);

                if ($newQuantity < 0) {
                    throw new InvalidArgumentException(sprintf(
                        'Product "%s" does not have enough stock.',
                        $item['product_name']
                    ));
                }

                $productUpdated = $this->products->update($item['product_id'], [
                    'quantity' => $this->formatQuantity($newQuantity),
                    'updated_at' => $timestamp,
                ]);

                if ($productUpdated === false) {
                    throw new RuntimeException('Unable to update product stock right now.');
                }
            }

            foreach ($this->normalizePayments($initialPayments, $currencyCode, $userId, $timestamp) as $payment) {
                $paymentCreated = $this->payments->insert([
                    'id' => $this->newUuid(),
                    'sale_id' => $saleId,
                    'created_by' => $payment['created_by'],
                    'currency_code' => $payment['currency_code'],
                    'amount' => $this->formatMoney($payment['amount']),
                    'paid_at' => $payment['paid_at'],
                    'note' => $payment['note'],
                    'created_at' => $payment['created_at'],
                ]);

                if ($paymentCreated === false) {
                    throw new RuntimeException('Unable to save payment right now.');
                }
            }

            $sale = $this->syncSalePaymentSummary($saleId);

            $this->db->transComplete();

            return [
                'sale' => $sale,
                'items' => $this->saleItems->findBySale($saleId),
                'payments' => $this->payments->findBySale($saleId),
            ];
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    public function syncSalePaymentSummary(string $saleId): array
    {
        $sale = $this->sales->find($saleId);

        if (! is_array($sale)) {
            throw new RuntimeException('Sale not found.');
        }

        $paidAmount = round($this->payments->sumAmountBySale($saleId), 2);
        $summary = $this->makePaymentSummary((float) $sale['total_amount'], $paidAmount);

        $updated = $this->sales->update($saleId, [
            'paid_amount' => $this->formatMoney($summary['paid_amount']),
            'due_amount' => $this->formatMoney($summary['due_amount']),
            'payment_status' => $summary['payment_status'],
            'updated_at' => $this->utcNow(),
        ]);

        if ($updated === false) {
            throw new RuntimeException('Unable to sync sale payment summary right now.');
        }

        $freshSale = $this->sales->find($saleId);

        if (! is_array($freshSale)) {
            throw new RuntimeException('Unable to reload sale after syncing payment summary.');
        }

        return $freshSale;
    }

    private function normalizeSaleItems(string $bookId, array $items): array
    {
        $normalized = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                throw new InvalidArgumentException('Each sale item must be a valid object.');
            }

            $productId = trim((string) ($item['product_id'] ?? ''));
            $quantity = $this->normalizeQuantity($item['quantity'] ?? null);

            if ($productId === '') {
                throw new InvalidArgumentException(sprintf(
                    'Sale item %d is missing a product.',
                    $index + 1
                ));
            }

            if ($quantity <= 0) {
                throw new InvalidArgumentException(sprintf(
                    'Sale item %d must have a quantity greater than zero.',
                    $index + 1
                ));
            }

            $product = $this->products->findExistingByIdAndBook($bookId, $productId);

            if ($product === null || (int) ($product['is_active'] ?? 0) !== 1) {
                throw new InvalidArgumentException(sprintf(
                    'Sale item %d references an unavailable product.',
                    $index + 1
                ));
            }

            $unitPrice = array_key_exists('unit_price', $item)
                ? $this->normalizeMoney($item['unit_price'])
                : $this->normalizeMoney($product['price'] ?? 0);

            if ($unitPrice < 0) {
                throw new InvalidArgumentException(sprintf(
                    'Sale item %d has an invalid unit price.',
                    $index + 1
                ));
            }

            $productQuantity = $this->normalizeQuantity($product['quantity'] ?? 0);

            if ($quantity > $productQuantity) {
                throw new InvalidArgumentException(sprintf(
                    'Product "%s" does not have enough stock.',
                    (string) ($product['name'] ?? 'Unknown product')
                ));
            }

            $normalized[] = [
                'product_id' => $productId,
                'product_name' => (string) ($product['name'] ?? ''),
                'product_sku' => $this->normalizeOptionalString($product['sku'] ?? null),
                'product_quantity' => $productQuantity,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($quantity * $unitPrice, 2),
            ];
        }

        return $normalized;
    }

    private function normalizePayments(array $payments, string $currencyCode, string $userId, string $timestamp): array
    {
        $normalized = [];

        foreach ($payments as $index => $payment) {
            if (! is_array($payment)) {
                throw new InvalidArgumentException('Each payment must be a valid object.');
            }

            $amount = $this->normalizeMoney($payment['amount'] ?? null);

            if ($amount <= 0) {
                throw new InvalidArgumentException(sprintf(
                    'Payment %d must have an amount greater than zero.',
                    $index + 1
                ));
            }

            $paidAt = trim((string) ($payment['paid_at'] ?? ''));

            $normalized[] = [
                'created_by' => $userId,
                'currency_code' => $currencyCode,
                'amount' => $amount,
                'paid_at' => $paidAt !== '' ? $paidAt : $timestamp,
                'note' => $this->normalizeOptionalString($payment['note'] ?? null),
                'created_at' => $timestamp,
            ];
        }

        return $normalized;
    }

    private function sumNormalizedPayments(array $payments, string $currencyCode): float
    {
        $total = 0.0;

        foreach ($this->normalizePayments($payments, $currencyCode, '', $this->utcNow()) as $payment) {
            $total += $payment['amount'];
        }

        return round($total, 2);
    }

    private function calculateSubtotal(array $items): float
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += (float) $item['line_total'];
        }

        return round($subtotal, 2);
    }

    private function makePaymentSummary(float $totalAmount, float $paidAmount): array
    {
        $safePaidAmount = max(0, round($paidAmount, 2));

        if ($safePaidAmount > round($totalAmount, 2)) {
            throw new InvalidArgumentException('Paid amount cannot exceed total amount.');
        }

        $dueAmount = round($totalAmount - $safePaidAmount, 2);
        $paymentStatus = 'unpaid';

        if ($dueAmount <= 0.0) {
            $paymentStatus = 'paid';
        } elseif ($safePaidAmount > 0.0) {
            $paymentStatus = 'partial';
        }

        return [
            'paid_amount' => $safePaidAmount,
            'due_amount' => max(0, $dueAmount),
            'payment_status' => $paymentStatus,
        ];
    }

    private function normalizeMoney(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException('Invalid money amount.');
        }

        return round((float) $value, 2);
    }

    private function normalizeQuantity(mixed $value): float
    {
        if ($value === null || $value === '') {
            throw new InvalidArgumentException('Quantity is required.');
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException('Invalid quantity.');
        }

        return round((float) $value, 3);
    }

    private function normalizeOptionalString(mixed $value): ?string
    {
        $string = trim((string) ($value ?? ''));

        return $string !== '' ? $string : null;
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    private function formatQuantity(float $quantity): string
    {
        return number_format($quantity, 3, '.', '');
    }

    private function newUuid(): string
    {
        helper('uuid');

        return uuid_v4();
    }

    private function utcNow(): string
    {
        return gmdate('Y-m-d H:i:s');
    }
}
