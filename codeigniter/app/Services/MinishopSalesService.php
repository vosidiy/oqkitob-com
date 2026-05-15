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

/**
 * Coordinates the minishop sale flow so stock, items, and payment summary stay
 * consistent inside one database transaction.
 *
 * The service intentionally keeps the critical minishop write logic in one
 * place so future controllers do not each reimplement sale math, stock
 * deduction, or payment-summary syncing in slightly different ways.
 */
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
        // Allow tests or future callers to inject a connection, while keeping
        // the normal app path simple through CI's shared DB service.
        $this->db = $db ?? Database::connect();
    }

    /**
     * Creates a sale, snapshots its items, deducts stock, and stores optional
     * initial payments as one atomic write.
     *
     * Expected payload shape:
     * - currency_code: 3-letter sale currency snapshot
     * - discount_amount: optional sale-level discount
     * - sold_at: business timestamp for when the sale happened
     * - note: optional sale note
     * - customer_id: optional minishop customer
     * - items: required list of sale items
     * - payments: optional upfront payment list
     *
     * Returns the freshly created sale together with the stored item/payment
     * snapshots so a controller can send a complete response immediately.
     */
    public function createSale(string $userId, string $bookId, array $payload): array
    {
        // Every write must first confirm the authenticated user owns an active
        // book of the expected type.
        $book = $this->books->findOwnedActiveBook($userId, $bookId, 'minishop');

        if ($book === null) {
            throw new RuntimeException('Book not found.');
        }

        // Pull raw client data into local variables up front so validation and
        // later write steps operate on one stable snapshot of the payload.
        $currencyCode = strtoupper(trim((string) ($payload['currency_code'] ?? '')));
        $discountAmount = $this->normalizeMoney($payload['discount_amount'] ?? 0);
        $soldAt = trim((string) ($payload['sold_at'] ?? ''));
        $note = $this->normalizeOptionalString($payload['note'] ?? null);
        $customerId = trim((string) ($payload['customer_id'] ?? ''));
        $items = $payload['items'] ?? null;
        $initialPayments = $payload['payments'] ?? [];

        // Validate the top-level request shape before any DB reads/writes that
        // depend on it.
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
            // Customer linkage is optional, but when present it must belong to
            // the same book as the sale.
            $customer = $this->customers->findExistingByIdAndBook($bookId, $customerId);

            if ($customer === null) {
                throw new InvalidArgumentException('Customer not found.');
            }
        }

        // Convert product references into immutable sale snapshots before
        // writing anything. This ensures totals come from trusted product data
        // and not raw client-calculated values.
        $normalizedItems = $this->normalizeSaleItems($bookId, $items);
        $subtotalAmount = $this->calculateSubtotal($normalizedItems);

        if ($discountAmount > $subtotalAmount) {
            throw new InvalidArgumentException('Discount amount cannot exceed subtotal.');
        }

        // The sale stores a cached payment summary so list/detail screens can
        // render debt state without recalculating from payment history on every
        // request.
        $totalAmount = round($subtotalAmount - $discountAmount, 2);
        $paidAmount = $this->sumNormalizedPayments($initialPayments, $currencyCode);
        $summary = $this->makePaymentSummary($totalAmount, $paidAmount);
        $saleId = $this->newUuid();
        $timestamp = $this->utcNow();

        // The sale, its item snapshots, stock deduction, and optional upfront
        // payments must all succeed or all fail together.
        $this->db->transException(true)->transStart();

        try {
            // Create the parent sale row first so child snapshots and payments
            // have a stable sale ID to reference.
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
                // Item rows snapshot product name/SKU/price at sale time so
                // future product edits do not rewrite sales history.
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

                // Stock is stored as current state only in MVP, so selling a
                // product means immediately reducing its current quantity.
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
                // Payments remain the source-of-truth history even though the
                // sale also caches paid/due/status summary fields.
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

            // Recalculate from payment rows rather than trusting the earlier
            // pre-insert estimate so the cached summary always reflects stored
            // history.
            $sale = $this->syncSalePaymentSummary($saleId);

            $this->db->transComplete();

            // Return the normalized stored rows, not the raw request payload.
            return [
                'sale' => $sale,
                'items' => $this->saleItems->findBySale($saleId),
                'payments' => $this->payments->findBySale($saleId),
            ];
        } catch (\Throwable $exception) {
            // Any failure in sale creation, stock deduction, or payment insert
            // should leave the database untouched for this sale attempt.
            $this->db->transRollback();
            throw $exception;
        }
    }

    /**
     * Keeps the denormalized payment fields on the sale in sync with the
     * payment history table.
     *
     * This should be called after payment creates/updates/deletes so
     * `paid_amount`, `due_amount`, and `payment_status` remain a reliable
     * cached summary for UI lists and filters.
     */
    public function syncSalePaymentSummary(string $saleId): array
    {
        $sale = $this->sales->find($saleId);

        if (! is_array($sale)) {
            throw new RuntimeException('Sale not found.');
        }

        // Always derive the cached values from persisted payment history so the
        // sale row stays consistent even if payment records changed elsewhere.
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

    /**
     * Expands product references into immutable sale-item snapshots and
     * validates stock before any write happens.
     *
     * The returned array contains both:
     * - snapshot fields that will be written to `minishop_sale_items`
     * - helper fields such as `product_quantity` used only during the current
     *   transaction to calculate the remaining stock
     */
    private function normalizeSaleItems(string $bookId, array $items): array
    {
        $normalized = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                throw new InvalidArgumentException('Each sale item must be a valid object.');
            }

            $productId = trim((string) ($item['product_id'] ?? ''));
            $quantity = $this->normalizeQuantity($item['quantity'] ?? null);

            // Keep validation messages item-specific so the UI can point users
            // to the broken row more easily.
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

            // Allow price override per sale item, otherwise fall back to the
            // product's current default selling price.
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

            // Reject overselling before the transaction reaches the stock
            // update step.
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

    /**
     * Normalizes optional payment rows into a consistent shape for both
     * upfront validation and transaction inserts.
     *
     * This accepts a user/timestamp argument so callers can reuse the same
     * function both for pre-insert validation and for the eventual insert data.
     */
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
                // The payment row stores the sale currency snapshot so future
                // book-setting changes do not rewrite old payment history.
                'created_by' => $userId,
                'currency_code' => $currencyCode,
                'amount' => $amount,
                // If the UI omits a payment timestamp, treat it as "paid now"
                // at the same UTC moment used by the current transaction.
                'paid_at' => $paidAt !== '' ? $paidAt : $timestamp,
                'note' => $this->normalizeOptionalString($payment['note'] ?? null),
                'created_at' => $timestamp,
            ];
        }

        return $normalized;
    }

    /**
     * Reuses payment normalization so the same validation rules apply before
     * we persist any rows.
     *
     * This is used to compute the initial cached payment summary before the
     * payment rows are inserted.
     */
    private function sumNormalizedPayments(array $payments, string $currencyCode): float
    {
        $total = 0.0;

        foreach ($this->normalizePayments($payments, $currencyCode, '', $this->utcNow()) as $payment) {
            $total += $payment['amount'];
        }

        return round($total, 2);
    }

    /**
     * Sale totals are derived from line totals, not trusted from raw client
     * input.
     *
     * This keeps the backend as the final source of truth for sale math.
     */
    private function calculateSubtotal(array $items): float
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += (float) $item['line_total'];
        }

        return round($subtotal, 2);
    }

    /**
     * Payment status is cached on the sale for faster list filters and detail
     * responses.
     *
     * Rules:
     * - `unpaid` when nothing has been paid
     * - `partial` when some amount is paid but debt remains
     * - `paid` when due reaches zero
     */
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

    /**
     * Money values are normalized to the schema precision before comparisons
     * and inserts.
     *
     * Empty values are treated as zero so optional monetary inputs such as a
     * missing discount can flow through the same validation path.
     */
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

    /**
     * Stock supports fractional quantities, so quantities are normalized to
     * three decimal places.
     *
     * Unlike money, quantity is required at the item level and cannot silently
     * fall back to zero.
     */
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

    /**
     * Trims optional string inputs and converts empty strings to null so the
     * database stores a cleaner "missing value" shape.
     */
    private function normalizeOptionalString(mixed $value): ?string
    {
        $string = trim((string) ($value ?? ''));

        return $string !== '' ? $string : null;
    }

    /**
     * Formats money exactly to the schema scale before insert/update writes.
     */
    private function formatMoney(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * Formats stock quantities exactly to the schema scale before writes.
     */
    private function formatQuantity(float $quantity): string
    {
        return number_format($quantity, 3, '.', '');
    }

    /**
     * Local helper so this service can generate stable UUID primary keys
     * without pushing that concern to controllers.
     */
    private function newUuid(): string
    {
        helper('uuid');

        return uuid_v4();
    }

    /**
     * Uses UTC explicitly so service-managed audit fields stay aligned with the
     * rest of the app's timestamp strategy.
     */
    private function utcNow(): string
    {
        return gmdate('Y-m-d H:i:s');
    }
}
