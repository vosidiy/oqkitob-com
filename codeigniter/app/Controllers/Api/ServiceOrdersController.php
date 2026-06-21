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
use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authenticated service order endpoints.
 *
 * Routes:
 * GET  /api/books/{bookId}/service/orders
 * GET  /api/books/{bookId}/service/orders/{orderId}
 * POST /api/books/{bookId}/service/orders
 * DELETE /api/books/{bookId}/service/orders/{orderId}
 * POST /api/books/{bookId}/service/orders/{orderId}/status
 */
class ServiceOrdersController extends AuthenticatedApiController
{
    use NormalizesPhoneNumbers;

    private const DEFAULT_PAGE = 1;
    private const DEFAULT_PER_PAGE = 25;
    private const ALLOWED_UNITS = ['qty', 'm2', 'kg'];
    private const ALLOWED_ORDER_STATUSES = ['received', 'working', 'ready', 'delivered'];
    private const ALLOWED_REPORT_FILTERS = ['today', 'yesterday', 'last_10_days', 'last_30_days'];

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
        $search = trim((string) $this->request->getGet('search'));

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $page = max(self::DEFAULT_PAGE, (int) $this->request->getGet('page'));
        $perPage = (int) $this->request->getGet('per_page');
        $orderStatus = $this->normalizeOptionalOrderStatus($this->request->getGet('order_status'));
        $excludeOrderStatus = $this->normalizeOptionalOrderStatus($this->request->getGet('exclude_order_status'));
        $receivedOn = $this->normalizeOptionalDateOnly($this->request->getGet('received_on'));

        if ($perPage <= 0) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $range = $this->makeReceivedOnRange($receivedOn);
        $result = $this->orders->findByBook(
            $bookId,
            $range['received_from'],
            $range['received_to'],
            $orderStatus,
            $search,
            $page,
            $perPage,
            $excludeOrderStatus
        );

        return $this->respond([
            'orders' => $result['orders'],
            'pagination' => $result['pagination'],
        ]);
    }

    public function analytics(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $filter = $this->normalizeReportFilter((string) $this->request->getGet('filter_time'));
        $localNow = $this->parseLocalDateTime((string) $this->request->getGet('local_now')) ?? new DateTimeImmutable();
        $range = $this->makeReportDateRange($filter, $localNow);

        return $this->respond([
            'summary' => $this->orders->findAnalyticsSummaryByBook(
                $bookId,
                $range['received_from'],
                $range['received_to'],
                'delivered'
            ),
            'services' => $this->orderItems->findServiceAnalyticsByBook(
                $bookId,
                $range['received_from'],
                $range['received_to'],
                'delivered'
            ),
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

    public function delete(string $bookId, string $orderId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        try {
            $result = $this->deleteOrder($bookId, $orderId);
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === 'Order not found.') {
                return $this->failNotFound('Order not found.');
            }

            return $this->failServerError($exception->getMessage());
        }

        return $this->respond([
            'message' => 'Order deleted successfully.',
            'orderId' => $result['orderId'],
            'deleted_at' => $result['deleted_at'],
        ]);
    }

    public function updateStatus(string $bookId, string $orderId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $payload = $this->getOrderPayload();

        try {
            $order = $this->updateOrderStatus($bookId, $orderId, $payload);
        } catch (InvalidArgumentException $exception) {
            return $this->respond([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (RuntimeException $exception) {
            if ($exception->getMessage() === 'Order not found.') {
                return $this->failNotFound('Order not found.');
            }

            return $this->failServerError($exception->getMessage());
        }

        return $this->respond([
            'message' => 'Order status updated successfully.',
            'order' => $order,
        ]);
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

        $selectedCustomerId = $this->normalizeOptionalId($payload['customer_id'] ?? null);
        $selectedCustomer = null;
        $customerPayload = null;
        $items = $payload['items'] ?? null;
        $discountValue = $payload['discount_amount'] ?? 0;
        $note = $this->normalizeOptionalString($payload['note'] ?? null);

        if (! is_array($items) || $items === []) {
            throw new InvalidArgumentException('At least one order item is required.');
        }

        if ($selectedCustomerId !== null) {
            $selectedCustomer = $this->customers->findExistingByIdAndBook($bookId, $selectedCustomerId);

            if ($selectedCustomer === null) {
                throw new InvalidArgumentException('Please choose a valid customer.');
            }
        } else {
            $customerPayload = $this->normalizeCustomerPayload($payload['customer'] ?? null);
            $this->validateCustomerPayload($customerPayload);
        }

        if (! $this->isNumericValue($discountValue)) {
            throw new InvalidArgumentException('Please enter a valid discount amount.');
        }

        $discountAmount = $this->normalizeMoney($discountValue);

        if ($discountAmount < 0) {
            throw new InvalidArgumentException('Discount amount cannot be negative.');
        }

        $normalizedItems = $this->normalizeOrderItems($bookId, $items);
        $subtotalAmount = $this->calculateSubtotal($normalizedItems);

        if ($discountAmount > $subtotalAmount) {
            throw new InvalidArgumentException('Discount amount cannot exceed subtotal.');
        }

        $totalAmount = round($subtotalAmount - $discountAmount, 2);
        $timestamp = $this->utcNow();

        helper('uuid');
        $orderId = uuid_v4();

        $this->db->transException(true)->transStart();

        try {
            $customerId = $selectedCustomer !== null
                ? (string) $selectedCustomer['id']
                : $this->upsertCustomerForOrder($userId, $bookId, $customerPayload ?? [], $timestamp);

            $created = $this->orders->insert([
                'id' => $orderId,
                'book_id' => $bookId,
                'created_by' => $userId,
                'customer_id' => $customerId,
                'currency_code' => $currencyCode,
                'subtotal_amount' => $this->formatMoney($subtotalAmount),
                'discount_amount' => $this->formatMoney($discountAmount),
                'total_amount' => $this->formatMoney($totalAmount),
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

    private function deleteOrder(string $bookId, string $orderId): array
    {
        $order = $this->orders->findExistingByIdAndBook($bookId, $orderId);

        if ($order === null) {
            throw new RuntimeException('Order not found.');
        }

        $timestamp = $this->utcNow();
        $deleted = $this->orders->update($orderId, [
            'deleted_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        if ($deleted === false) {
            throw new RuntimeException('Unable to delete order right now.');
        }

        return [
            'orderId' => $orderId,
            'deleted_at' => $timestamp,
        ];
    }

    private function updateOrderStatus(string $bookId, string $orderId, array $payload): array
    {
        $order = $this->orders->findExistingByIdAndBook($bookId, $orderId);

        if ($order === null) {
            throw new RuntimeException('Order not found.');
        }

        $nextOrderStatus = $this->normalizeRequiredOrderStatus($payload['order_status'] ?? null);

        if ($nextOrderStatus === (string) ($order['order_status'] ?? '')) {
            return $order;
        }

        $timestamp = $this->utcNow();
        $readyAt = $order['ready_at'] ?? null;
        $deliveredAt = $order['delivered_at'] ?? null;

        if ($nextOrderStatus === 'received' || $nextOrderStatus === 'working') {
            $readyAt = null;
            $deliveredAt = null;
        } elseif ($nextOrderStatus === 'ready') {
            $readyAt = $timestamp;
            $deliveredAt = null;
        } elseif ($nextOrderStatus === 'delivered') {
            $readyAt = $readyAt ?: $timestamp;
            $deliveredAt = $timestamp;
        }

        $updated = $this->orders->update($orderId, [
            'order_status' => $nextOrderStatus,
            'ready_at' => $readyAt,
            'delivered_at' => $deliveredAt,
            'updated_at' => $timestamp,
        ]);

        if ($updated === false) {
            throw new RuntimeException('Unable to update order status right now.');
        }

        $updatedOrder = $this->orders->findExistingByIdAndBook($bookId, $orderId);

        if ($updatedOrder === null) {
            throw new RuntimeException('Unable to load updated order right now.');
        }

        return $updatedOrder;
    }

    private function normalizeCustomerPayload(mixed $payload): array
    {
        $customer = is_array($payload) ? $payload : [];

        $rawPhone = trim((string) ($customer['phone'] ?? ''));
        $normalizedPhone = $rawPhone !== '' ? $this->normalizeInternationalPhone($rawPhone) : null;

        return [
            'name' => trim((string) ($customer['name'] ?? '')),
            'phone' => $normalizedPhone,
            'phone_is_invalid' => $rawPhone !== '' && $normalizedPhone === null,
            'messenger' => $this->normalizeOptionalString($customer['messenger'] ?? null),
            'address' => $this->normalizeOptionalString($customer['address'] ?? null),
            'location' => $this->normalizeOptionalString($customer['location'] ?? null),
        ];
    }

    private function validateCustomerPayload(array $payload): void
    {
        $name = (string) ($payload['name'] ?? '');
        $phone = $payload['phone'] ?? null;
        $messenger = $payload['messenger'] ?? null;

        if ($name === '') {
            throw new InvalidArgumentException('Customer name is required.');
        }

        if (mb_strlen($name) > 255) {
            throw new InvalidArgumentException('Customer name must be 255 characters or fewer.');
        }

        if (($payload['phone_is_invalid'] ?? false) === true) {
            throw new InvalidArgumentException('Please enter a valid phone number. Letters are not allowed.');
        }

        if (! is_string($phone) || $phone === '') {
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

    private function normalizeOptionalOrderStatus(mixed $value): ?string
    {
        $normalizedValue = trim((string) $value);

        if ($normalizedValue === '') {
            return null;
        }

        return in_array($normalizedValue, self::ALLOWED_ORDER_STATUSES, true)
            ? $normalizedValue
            : null;
    }

    private function normalizeReportFilter(string $filter): string
    {
        $normalizedFilter = trim($filter);

        return in_array($normalizedFilter, self::ALLOWED_REPORT_FILTERS, true)
            ? $normalizedFilter
            : 'today';
    }

    private function makeReportDateRange(string $filter, DateTimeImmutable $localNow): array
    {
        return match ($filter) {
            'yesterday' => [
                'received_from' => $localNow->modify('-1 day')->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'received_to' => $localNow->modify('-1 day')->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
            'last_10_days' => [
                'received_from' => $localNow->sub(new DateInterval('P10D'))->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'received_to' => $localNow->format('Y-m-d H:i:s'),
            ],
            'last_30_days' => [
                'received_from' => $localNow->sub(new DateInterval('P30D'))->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'received_to' => $localNow->format('Y-m-d H:i:s'),
            ],
            default => [
                'received_from' => $localNow->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
                'received_to' => $localNow->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
            ],
        };
    }

    private function normalizeOptionalDateOnly(mixed $value): ?string
    {
        $normalizedValue = trim((string) $value);

        if ($normalizedValue === '') {
            return null;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalizedValue)) {
            return null;
        }

        $parsedValue = DateTimeImmutable::createFromFormat('Y-m-d', $normalizedValue);
        $parseErrors = DateTimeImmutable::getLastErrors();

        if (
            ! ($parsedValue instanceof DateTimeImmutable)
            || ! ($parseErrors === false || ($parseErrors['warning_count'] === 0 && $parseErrors['error_count'] === 0))
        ) {
            return null;
        }

        return $parsedValue->format('Y-m-d');
    }

    private function makeReceivedOnRange(?string $receivedOn): array
    {
        if ($receivedOn === null) {
            return [
                'received_from' => null,
                'received_to' => null,
            ];
        }

        $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $receivedOn);
        $parseErrors = DateTimeImmutable::getLastErrors();

        if (
            ! ($parsedDate instanceof DateTimeImmutable)
            || ! ($parseErrors === false || ($parseErrors['warning_count'] === 0 && $parseErrors['error_count'] === 0))
        ) {
            return [
                'received_from' => null,
                'received_to' => null,
            ];
        }

        return [
            'received_from' => $parsedDate->setTime(0, 0, 0)->format('Y-m-d H:i:s'),
            'received_to' => $parsedDate->setTime(23, 59, 59)->format('Y-m-d H:i:s'),
        ];
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

    private function normalizeRequiredOrderStatus(mixed $value): string
    {
        $normalizedValue = trim((string) $value);

        if (! in_array($normalizedValue, self::ALLOWED_ORDER_STATUSES, true)) {
            throw new InvalidArgumentException('Please choose a valid order status.');
        }

        return $normalizedValue;
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
