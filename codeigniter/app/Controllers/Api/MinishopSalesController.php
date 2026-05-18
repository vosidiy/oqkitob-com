<?php

namespace App\Controllers\Api;

use App\Models\BookModel;
use App\Models\MinishopProductModel;
use App\Models\MinishopSaleItemModel;
use App\Models\MinishopSaleModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authenticated minishop sales endpoints.
 *
 * Route:
 * GET /api/books/{bookId}/minishop/sales
 * GET /api/books/{bookId}/minishop/sales/{saleId}
 * POST /api/books/{bookId}/minishop/sales
 * PUT /api/books/{bookId}/minishop/sales/{saleId}/payment-summary
 * DELETE /api/books/{bookId}/minishop/sales/{saleId}
 */
class MinishopSalesController extends AuthenticatedApiController
{
    private const ALLOWED_FILTERS = [
        'today',
        'yesterday',
        'last_10_days',
        'last_20_days',
        'last_30_days',
        'previous_month',
        'this_year',
        'all_time',
    ];

    private BaseConnection $db;

    public function __construct(
        private readonly BookModel $books = new BookModel(),
        private readonly MinishopProductModel $products = new MinishopProductModel(),
        private readonly MinishopSaleModel $sales = new MinishopSaleModel(),
        private readonly MinishopSaleItemModel $saleItems = new MinishopSaleItemModel(),
        ?BaseConnection $db = null
    ) {
        $this->db = $db ?? Database::connect();
    }

    public function index(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();

        try {
            $this->requireOwnedMinishopBook($userId, $bookId);
        } catch (RuntimeException $exception) {
            return $this->failNotFound('Book not found.');
        }

        $filter = $this->normalizeFilterTime((string) $this->request->getGet('filter_time'));
        $localNow = $this->parseLocalDateTime((string) $this->request->getGet('local_now')) ?? new DateTimeImmutable();
        $range = $this->makeSalesDateRange($filter, $localNow);

        return $this->respond([
            'sales' => $this->sales->findByBook($bookId, $range['sold_from'], $range['sold_to']),
        ]);
    }

    public function show(string $bookId, string $saleId)
    {
        $userId = $this->currentUserIdAndCloseSession();

        try {
            $this->requireOwnedMinishopBook($userId, $bookId);
        } catch (RuntimeException $exception) {
            return $this->failNotFound('Book not found.');
        }

        $sale = $this->sales->findExistingByIdAndBook($bookId, $saleId);

        if ($sale === null) {
            return $this->failNotFound('Sale not found.');
        }

        return $this->respond([
            'sale' => $sale,
            'items' => $this->saleItems->findBySale($saleId),
        ]);
    }

    public function create(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $payload = $this->getSalePayload();

        try {
            $result = $this->createSale($userId, $bookId, $payload);
        } catch (InvalidArgumentException $exception) {
            return $this->respond([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === 'Book not found.') {
                return $this->failNotFound('Book not found.');
            }

            return $this->failServerError($exception->getMessage());
        }

        return $this->respond([
            'message' => 'Sale created successfully.',
            'sale' => $result['sale'],
            'items' => $result['items'],
        ], 201);
    }

    public function updatePaymentSummary(string $bookId, string $saleId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $payload = $this->getSalePayload();

        try {
            $sale = $this->updateSalePaymentSummary($userId, $bookId, $saleId, $payload);
        } catch (InvalidArgumentException $exception) {
            return $this->respond([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === 'Book not found.') {
                return $this->failNotFound('Book not found.');
            }

            if ($exception->getMessage() === 'Sale not found.') {
                return $this->failNotFound('Sale not found.');
            }

            return $this->failServerError($exception->getMessage());
        }

        return $this->respond([
            'message' => 'Payment summary updated successfully.',
            'sale' => $sale,
        ]);
    }

    public function delete(string $bookId, string $saleId)
    {
        $userId = $this->currentUserIdAndCloseSession();

        try {
            $result = $this->deleteSale($userId, $bookId, $saleId);
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === 'Book not found.') {
                return $this->failNotFound('Book not found.');
            }

            if ($exception->getMessage() === 'Sale not found.') {
                return $this->failNotFound('Sale not found.');
            }

            return $this->failServerError($exception->getMessage());
        }

        return $this->respond([
            'message' => 'Sale deleted successfully.',
            'saleId' => $result['saleId'],
            'deleted_at' => $result['deleted_at'],
        ]);
    }

    private function getSalePayload(): array
    {
        $payload = $this->request->getJSON(true);

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getRawInput();
        }

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }

        return is_array($payload) ? $payload : [];
    }

    private function requireOwnedMinishopBook(string $userId, string $bookId): array
    {
        $book = $this->books->findOwnedActiveBook($userId, $bookId, 'minishop');

        if ($book === null) {
            throw new RuntimeException('Book not found.');
        }

        return $book;
    }

    private function createSale(string $userId, string $bookId, array $payload): array
    {
        $this->requireOwnedMinishopBook($userId, $bookId);

        $currencyCode = strtoupper(trim((string) ($payload['currency_code'] ?? '')));
        $discountAmount = $this->normalizeMoney($payload['discount_amount'] ?? 0);
        $paidAmount = $this->normalizeMoney($payload['paid_amount'] ?? 0);
        $note = $this->normalizeOptionalString($payload['note'] ?? null);
        $items = $payload['items'] ?? null;
        $timestamp = $this->utcNow();
        $soldAt = trim((string) ($payload['sold_at'] ?? ''));

        if ($currencyCode === '' || strlen($currencyCode) !== 3) {
            throw new InvalidArgumentException('Currency code must be a 3-letter code.');
        }

        if (! is_array($items) || $items === []) {
            throw new InvalidArgumentException('At least one sale item is required.');
        }

        if ($discountAmount < 0) {
            throw new InvalidArgumentException('Discount amount cannot be negative.');
        }

        if ($paidAmount < 0) {
            throw new InvalidArgumentException('Paid amount cannot be negative.');
        }

        $normalizedItems = $this->normalizeSaleItems($bookId, $items);
        $subtotalAmount = $this->calculateSubtotal($normalizedItems);

        if ($discountAmount > $subtotalAmount) {
            throw new InvalidArgumentException('Discount amount cannot exceed subtotal.');
        }

        $totalAmount = round($subtotalAmount - $discountAmount, 2);
        $summary = $this->makePaymentSummary($totalAmount, $paidAmount);
        $saleId = $this->newUuid();
        $soldAt = $this->normalizeSoldAt($soldAt) ?? $timestamp;

        $this->db->transException(true)->transStart();

        try {
            $created = $this->sales->insert([
                'id' => $saleId,
                'book_id' => $bookId,
                'created_by' => $userId,
                'customer_id' => null,
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

            $this->db->transComplete();

            $sale = $this->sales->findExistingByIdAndBook($bookId, $saleId);

            if ($sale === null) {
                throw new RuntimeException('Unable to load the new sale right now.');
            }

            return [
                'sale' => $sale,
                'items' => $this->saleItems->findBySale($saleId),
            ];
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    private function deleteSale(string $userId, string $bookId, string $saleId): array
    {
        $this->requireOwnedMinishopBook($userId, $bookId);

        $sale = $this->sales->findExistingByIdAndBook($bookId, $saleId);

        if ($sale === null) {
            throw new RuntimeException('Sale not found.');
        }

        $items = $this->saleItems->findBySale($saleId);
        $timestamp = $this->utcNow();

        $this->db->transException(true)->transStart();

        try {
            foreach ($items as $item) {
                $productId = trim((string) ($item['product_id'] ?? ''));

                if ($productId === '') {
                    continue;
                }

                $product = $this->products->findExistingByIdAndBook($bookId, $productId);

                if ($product === null) {
                    throw new RuntimeException(sprintf(
                        'Unable to restore stock because product "%s" is no longer available.',
                        (string) ($item['product_name'] ?? 'Unknown product')
                    ));
                }

                $restoredQuantity = round(
                    $this->normalizeQuantity($product['quantity'] ?? 0)
                    + $this->normalizeQuantity($item['quantity'] ?? 0),
                    3
                );

                $updated = $this->products->update($productId, [
                    'quantity' => $this->formatQuantity($restoredQuantity),
                    'updated_at' => $timestamp,
                ]);

                if ($updated === false) {
                    throw new RuntimeException('Unable to restore product stock right now.');
                }
            }

            $deleted = $this->sales->update($saleId, [
                'deleted_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            if ($deleted === false) {
                throw new RuntimeException('Unable to delete sale right now.');
            }

            $this->db->transComplete();

            return [
                'saleId' => $saleId,
                'deleted_at' => $timestamp,
            ];
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    private function updateSalePaymentSummary(string $userId, string $bookId, string $saleId, array $payload): array
    {
        $this->requireOwnedMinishopBook($userId, $bookId);

        $sale = $this->sales->findExistingByIdAndBook($bookId, $saleId);

        if ($sale === null) {
            throw new RuntimeException('Sale not found.');
        }

        $subtotalAmount = $this->normalizeMoney($sale['subtotal_amount'] ?? 0);
        $discountAmount = $this->normalizeMoney($payload['discount_amount'] ?? 0);
        $paidAmount = $this->normalizeMoney($payload['paid_amount'] ?? 0);

        if ($discountAmount < 0) {
            throw new InvalidArgumentException('Discount amount cannot be negative.');
        }

        if ($paidAmount < 0) {
            throw new InvalidArgumentException('Paid amount cannot be negative.');
        }

        if ($discountAmount > $subtotalAmount) {
            throw new InvalidArgumentException('Discount amount cannot exceed subtotal.');
        }

        $totalAmount = round($subtotalAmount - $discountAmount, 2);
        $summary = $this->makePaymentSummary($totalAmount, $paidAmount);
        $timestamp = $this->utcNow();

        $updated = $this->sales->update($saleId, [
            'discount_amount' => $this->formatMoney($discountAmount),
            'total_amount' => $this->formatMoney($totalAmount),
            'paid_amount' => $this->formatMoney($summary['paid_amount']),
            'due_amount' => $this->formatMoney($summary['due_amount']),
            'payment_status' => $summary['payment_status'],
            'updated_at' => $timestamp,
        ]);

        if ($updated === false) {
            throw new RuntimeException('Unable to update sale payment summary right now.');
        }

        $updatedSale = $this->sales->findExistingByIdAndBook($bookId, $saleId);

        if ($updatedSale === null) {
            throw new RuntimeException('Unable to load the updated sale right now.');
        }

        return $updatedSale;
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

    private function calculateSubtotal(array $items): float
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += (float) $item['line_total'];
        }

        return round($subtotal, 2);
    }

    private function normalizeFilterTime(string $filter): string
    {
        $normalizedFilter = trim($filter);

        return in_array($normalizedFilter, self::ALLOWED_FILTERS, true)
            ? $normalizedFilter
            : 'today';
    }

    private function makeSalesDateRange(string $filter, DateTimeImmutable $localNow): array
    {
        return match ($filter) {
            'all_time' => [
                'sold_from' => null,
                'sold_to' => null,
            ],
            'yesterday' => [
                'sold_from' => $localNow->modify('-1 day')->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'sold_to' => $localNow->modify('-1 day')->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            'last_10_days' => $this->makeRollingRange($localNow, 'P10D'),
            'last_20_days' => $this->makeRollingRange($localNow, 'P20D'),
            'last_30_days' => $this->makeRollingRange($localNow, 'P30D'),
            'previous_month' => [
                'sold_from' => $localNow->modify('first day of last month')->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'sold_to' => $localNow->modify('last day of last month')->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            'this_year' => [
                'sold_from' => $localNow->setDate((int) $localNow->format('Y'), 1, 1)->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'sold_to' => $localNow->setDate((int) $localNow->format('Y'), 12, 31)->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            default => [
                'sold_from' => $localNow->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'sold_to' => $localNow->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
        };
    }

    private function makeRollingRange(DateTimeImmutable $localNow, string $intervalSpec): array
    {
        return [
            'sold_from' => $localNow->sub(new DateInterval($intervalSpec))->format('Y-m-d H:i:s'),
            'sold_to' => $localNow->format('Y-m-d H:i:s'),
        ];
    }

    private function normalizeSoldAt(string $soldAt): ?string
    {
        $normalizedSoldAt = trim($soldAt);

        if ($normalizedSoldAt === '') {
            return null;
        }

        $parsedSoldAt = $this->parseLocalDateTime($normalizedSoldAt);

        return $parsedSoldAt?->format('Y-m-d H:i:s');
    }

    private function parseLocalDateTime(string $value): ?DateTimeImmutable
    {
        $normalizedValue = trim($value);

        if ($normalizedValue === '') {
            return null;
        }

        $parsedValue = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $normalizedValue);
        $parseErrors = DateTimeImmutable::getLastErrors();

        if (
            $parsedValue instanceof DateTimeImmutable
            && ($parseErrors === false || ($parseErrors['warning_count'] === 0 && $parseErrors['error_count'] === 0))
        ) {
            return $parsedValue;
        }

        try {
            return new DateTimeImmutable($normalizedValue);
        } catch (\Exception) {
            return null;
        }
    }

    private function makePaymentSummary(float $totalAmount, float $paidAmount): array
    {
        $safePaidAmount = min(max(0, round($paidAmount, 2)), round($totalAmount, 2));
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
}
