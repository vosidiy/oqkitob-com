<?php

namespace App\Controllers\Api;

use App\Models\BookModel;
use App\Models\ServiceCustomerModel;
use App\Models\ServiceOrderItemModel;
use App\Models\ServiceOrderModel;
use App\Models\ServiceTypeModel;
use App\Services\BookAccessService;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authenticated service order endpoints.
 *
 * Routes:
 * GET  /api/books/{bookId}/service/orders
 * GET  /api/books/{bookId}/service/orders/{orderId}
 * POST /api/books/{bookId}/service/orders
 */
class ServiceOrdersController extends AuthenticatedApiController
{
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_PER_PAGE = 100;
    private const ALLOWED_UNITS = ['qty', 'm2', 'kg'];

    private BaseConnection $db;

    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly BookModel $books = new BookModel(),
        private readonly ServiceCustomerModel $customers = new ServiceCustomerModel(),
        private readonly ServiceTypeModel $serviceTypes = new ServiceTypeModel(),
        private readonly ServiceOrderModel $orders = new ServiceOrderModel(),
        private readonly ServiceOrderItemModel $orderItems = new ServiceOrderItemModel(),
        ?BaseConnection $db = null
    ) {
        $this->db = $db ?? Database::connect();
    }

    public function index(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $page = max(self::DEFAULT_PAGE, (int) $this->request->getGet('page'));
        $perPage = (int) $this->request->getGet('per_page');

        if ($perPage <= 0) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $result = $this->orders->findByBook($bookId, null, null, null, null, '', $page, $perPage);

        return $this->respond([
            'orders' => $result['orders'],
            'pagination' => $result['pagination'],
        ]);
    }

    public function show(string $bookId, string $orderId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $order = $this->orders->findExistingByIdAndBook($bookId, $orderId);

        if ($order === null) {
            return $this->failNotFound('Order not found.');
        }

        return $this->respond([
            'order' => $order,
            'items' => $this->orderItems->findByOrder($orderId),
        ]);
    }

    public function create(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $payload = $this->getOrderPayload();

        try {
            $result = $this->createOrder($userId, $bookId, $payload);
        } catch (InvalidArgumentException $exception) {
            return $this->respond([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        } catch (\Throwable) {
            return $this->failServerError('Unable to create order right now.');
        }

        return $this->respond([
            'message' => 'Order created successfully.',
            'order' => $result['order'],
            'items' => $result['items'],
        ], 201);
    }

    private function getOrderPayload(): array
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

    private function createOrder(string $userId, string $bookId, array $payload): array
    {
        $book = $this->books->findActiveBookById($bookId, 'service');

        if ($book === null) {
            throw new RuntimeException('Unable to load book right now.');
        }

        $currencyCode = strtoupper(trim((string) ($book['currency_code'] ?? '')));

        if ($currencyCode === '') {
            throw new RuntimeException('Book currency is required to create service orders.');
        }

        $customerPayload = $this->normalizeCustomerPayload($payload['customer'] ?? null);
        $items = $payload['items'] ?? null;
        $discountValue = $payload['discount_amount'] ?? 0;
        $paidValue = $payload['paid_amount'] ?? 0;
        $note = $this->normalizeOptionalString($payload['note'] ?? null);

        if (! is_array($items) || $items === []) {
            throw new InvalidArgumentException('At least one order item is required.');
        }

        $this->validateCustomerPayload($customerPayload);

        if (! $this->isNumericValue($discountValue)) {
            throw new InvalidArgumentException('Please enter a valid discount amount.');
        }

        if (! $this->isNumericValue($paidValue)) {
            throw new InvalidArgumentException('Please enter a valid paid amount.');
        }

        $discountAmount = $this->normalizeMoney($discountValue);
        $paidAmount = $this->normalizeMoney($paidValue);

        if ($discountAmount < 0) {
            throw new InvalidArgumentException('Discount amount cannot be negative.');
        }

        if ($paidAmount < 0) {
            throw new InvalidArgumentException('Paid amount cannot be negative.');
        }

        $normalizedItems = $this->normalizeOrderItems($bookId, $items);
        $subtotalAmount = $this->calculateSubtotal($normalizedItems);

        if ($discountAmount > $subtotalAmount) {
            throw new InvalidArgumentException('Discount amount cannot exceed subtotal.');
        }

        $totalAmount = round($subtotalAmount - $discountAmount, 2);

        if ($paidAmount > $totalAmount) {
            throw new InvalidArgumentException('Paid amount cannot exceed the order total.');
        }

        $paymentSummary = $this->makePaymentSummary($totalAmount, $paidAmount);
        $timestamp = $this->utcNow();

        helper('uuid');
        $orderId = uuid_v4();

        $this->db->transException(true)->transStart();

        try {
            $customerId = $this->upsertCustomerForOrder($userId, $bookId, $customerPayload, $timestamp);

            $created = $this->orders->insert([
                'id' => $orderId,
                'book_id' => $bookId,
                'created_by' => $userId,
                'customer_id' => $customerId,
                'currency_code' => $currencyCode,
                'subtotal_amount' => $this->formatMoney($subtotalAmount),
                'discount_amount' => $this->formatMoney($discountAmount),
                'total_amount' => $this->formatMoney($totalAmount),
                'paid_amount' => $this->formatMoney($paymentSummary['paid_amount']),
                'due_amount' => $this->formatMoney($paymentSummary['due_amount']),
                'payment_status' => $paymentSummary['payment_status'],
                'order_status' => 'received',
                'note' => $note,
                'received_at' => $timestamp,
                'ready_at' => null,
                'delivered_at' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            if ($created === false) {
                throw new RuntimeException('Unable to create order right now.');
            }

            foreach ($normalizedItems as $index => $item) {
                $itemCreated = $this->orderItems->insert([
                    'id' => uuid_v4(),
                    'order_id' => $orderId,
                    'service_type_id' => $item['service_type_id'],
                    'object_name' => $item['object_name'],
                    'service_name' => $item['service_name'],
                    'quantity' => $this->formatQuantity($item['quantity']),
                    'unit_code' => $item['unit_code'],
                    'unit_price' => $this->formatMoney($item['unit_price']),
                    'line_total' => $this->formatMoney($item['line_total']),
                    'note' => $item['note'],
                    'attachment_path' => null,
                    'sort_order' => $index,
                ]);

                if ($itemCreated === false) {
                    throw new RuntimeException('Unable to save order items right now.');
                }
            }

            $this->db->transComplete();

            $order = $this->orders->findExistingByIdAndBook($bookId, $orderId);

            if ($order === null) {
                throw new RuntimeException('Unable to load the new order right now.');
            }

            return [
                'order' => $order,
                'items' => $this->orderItems->findByOrder($orderId),
            ];
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    private function normalizeCustomerPayload(mixed $payload): array
    {
        $customer = is_array($payload) ? $payload : [];

        return [
            'name' => trim((string) ($customer['name'] ?? '')),
            'phone' => trim((string) ($customer['phone'] ?? '')),
            'messenger' => $this->normalizeOptionalString($customer['messenger'] ?? null),
            'address' => $this->normalizeOptionalString($customer['address'] ?? null),
            'location' => $this->normalizeOptionalString($customer['location'] ?? null),
        ];
    }

    private function validateCustomerPayload(array $payload): void
    {
        $name = (string) ($payload['name'] ?? '');
        $phone = (string) ($payload['phone'] ?? '');
        $messenger = $payload['messenger'] ?? null;

        if ($name === '') {
            throw new InvalidArgumentException('Customer name is required.');
        }

        if (mb_strlen($name) > 255) {
            throw new InvalidArgumentException('Customer name must be 255 characters or fewer.');
        }

        if ($phone === '') {
            throw new InvalidArgumentException('Customer phone is required.');
        }

        if (mb_strlen($phone) > 50) {
            throw new InvalidArgumentException('Customer phone must be 50 characters or fewer.');
        }

        if ($messenger !== null && mb_strlen((string) $messenger) > 100) {
            throw new InvalidArgumentException('Messenger must be 100 characters or fewer.');
        }
    }

    private function normalizeOrderItems(string $bookId, array $items): array
    {
        $normalizedItems = [];

        foreach ($items as $itemIndex => $row) {
            $item = is_array($row) ? $row : [];
            $objectName = trim((string) ($item['object_name'] ?? ''));
            $serviceTypeId = $this->normalizeOptionalId($item['service_type_id'] ?? null);
            $quantityValue = $item['quantity'] ?? null;
            $unitValue = trim((string) ($item['unit_code'] ?? ''));
            $unitPriceValue = $item['unit_price'] ?? null;
            $note = $this->normalizeOptionalString($item['note'] ?? null);

            if ($objectName === '') {
                throw new InvalidArgumentException(sprintf('Item %d name is required.', $itemIndex + 1));
            }

            if (mb_strlen($objectName) > 255) {
                throw new InvalidArgumentException(sprintf('Item %d name must be 255 characters or fewer.', $itemIndex + 1));
            }

            if ($serviceTypeId === null) {
                throw new InvalidArgumentException(sprintf('Please choose a valid service for item %d.', $itemIndex + 1));
            }

            $serviceType = $this->serviceTypes->findExistingByIdAndBook($bookId, $serviceTypeId);

            if ($serviceType === null) {
                throw new InvalidArgumentException(sprintf('Please choose a valid service for item %d.', $itemIndex + 1));
            }

            if (! $this->isNumericValue($quantityValue)) {
                throw new InvalidArgumentException(sprintf('Please enter a valid quantity for item %d.', $itemIndex + 1));
            }

            $quantity = $this->normalizeQuantity($quantityValue);

            if ($quantity <= 0) {
                throw new InvalidArgumentException(sprintf('Quantity must be greater than zero for item %d.', $itemIndex + 1));
            }

            if (! in_array($unitValue, self::ALLOWED_UNITS, true)) {
                throw new InvalidArgumentException(sprintf('Please choose a valid unit for item %d.', $itemIndex + 1));
            }

            if (! $this->isNumericValue($unitPriceValue)) {
                throw new InvalidArgumentException(sprintf('Please enter a valid unit price for item %d.', $itemIndex + 1));
            }

            $unitPrice = $this->normalizeMoney($unitPriceValue);

            if ($unitPrice < 0) {
                throw new InvalidArgumentException(sprintf('Unit price cannot be negative for item %d.', $itemIndex + 1));
            }

            $normalizedItems[] = [
                'service_type_id' => $serviceTypeId,
                'object_name' => $objectName,
                'service_name' => (string) ($serviceType['name'] ?? ''),
                'quantity' => $quantity,
                'unit_code' => $unitValue,
                'unit_price' => $unitPrice,
                'line_total' => round($quantity * $unitPrice, 2),
                'note' => $note,
            ];
        }

        return $normalizedItems;
    }

    private function upsertCustomerForOrder(string $userId, string $bookId, array $payload, string $timestamp): string
    {
        $existingCustomer = $this->customers->findExistingByPhoneAndBook($bookId, $payload['phone']);

        if ($existingCustomer !== null) {
            $updated = $this->customers->update((string) $existingCustomer['id'], [
                'name' => $payload['name'],
                'phone' => $payload['phone'],
                'messenger' => $payload['messenger'] ?? ($existingCustomer['messenger'] ?? null),
                'address' => $payload['address'] ?? ($existingCustomer['address'] ?? null),
                'location' => $payload['location'] ?? ($existingCustomer['location'] ?? null),
                'updated_at' => $timestamp,
            ]);

            if ($updated === false) {
                throw new RuntimeException('Unable to update customer right now.');
            }

            return (string) $existingCustomer['id'];
        }

        helper('uuid');
        $customerId = uuid_v4();
        $created = $this->customers->insert([
            'id' => $customerId,
            'book_id' => $bookId,
            'created_by' => $userId,
            'name' => $payload['name'],
            'phone' => $payload['phone'],
            'messenger' => $payload['messenger'],
            'address' => $payload['address'],
            'location' => $payload['location'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        if ($created === false) {
            throw new RuntimeException('Unable to create customer right now.');
        }

        return $customerId;
    }

    private function calculateSubtotal(array $items): float
    {
        $subtotalAmount = 0.0;

        foreach ($items as $item) {
            $subtotalAmount += (float) ($item['line_total'] ?? 0);
        }

        return round($subtotalAmount, 2);
    }

    private function makePaymentSummary(float $totalAmount, float $paidAmount): array
    {
        if ($totalAmount <= 0) {
            return [
                'paid_amount' => 0.0,
                'due_amount' => 0.0,
                'payment_status' => 'paid',
            ];
        }

        $dueAmount = round(max(0, $totalAmount - $paidAmount), 2);
        $paymentStatus = $paidAmount <= 0
            ? 'unpaid'
            : ($dueAmount <= 0 ? 'paid' : 'partial');

        return [
            'paid_amount' => round($paidAmount, 2),
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
        ];
    }

    private function normalizeOptionalId(mixed $value): ?string
    {
        $normalizedValue = trim((string) $value);

        return $normalizedValue !== '' ? $normalizedValue : null;
    }

    private function normalizeOptionalString(mixed $value): ?string
    {
        $normalizedValue = trim((string) $value);

        return $normalizedValue !== '' ? $normalizedValue : null;
    }

    private function normalizeMoney(mixed $value): float
    {
        return round((float) $value, 2);
    }

    private function normalizeQuantity(mixed $value): float
    {
        return round((float) $value, 3);
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    private function formatQuantity(float $quantity): string
    {
        return number_format($quantity, 3, '.', '');
    }

    private function isNumericValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        $normalizedValue = trim((string) $value);

        return $normalizedValue !== '' && is_numeric($normalizedValue);
    }
}
