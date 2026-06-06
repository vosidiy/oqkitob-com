<?php

namespace App\Controllers\Api;

use App\Models\ServiceCustomerModel;
use App\Models\ServiceOrderModel;
use App\Services\BookAccessService;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authenticated service customer endpoints.
 *
 * Routes:
 * GET  /api/books/{bookId}/service/customers
 * GET  /api/books/{bookId}/service/customers/{customerId}
 * POST /api/books/{bookId}/service/customers
 * PUT  /api/books/{bookId}/service/customers/{customerId}
 */
class ServiceCustomersController extends AuthenticatedApiController
{
    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly ServiceCustomerModel $customers = new ServiceCustomerModel(),
        private readonly ServiceOrderModel $orders = new ServiceOrderModel()
    ) {
    }

    public function index(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');
        $search = trim((string) $this->request->getGet('search'));

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        return $this->respond([
            'customers' => $this->customers->findCustomerListByBook($bookId, $search),
        ]);
    }

    public function show(string $bookId, string $customerId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $customer = $this->customers->findOneCustomerByBook($bookId, $customerId);

        if ($customer === null) {
            return $this->failNotFound('Customer not found.');
        }

        return $this->respond([
            'customer' => $customer,
            'orders' => $this->orders->findByBookAndCustomer($bookId, $customerId),
        ]);
    }

    public function create(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $payload = $this->getCustomerPayload();

        try {
            $customer = $this->createCustomer($userId, $bookId, $payload);
        } catch (InvalidArgumentException $exception) {
            return $this->respond(['message' => $exception->getMessage()], 422);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        } catch (\Throwable) {
            return $this->failServerError('Unable to create customer right now.');
        }

        return $this->respond([
            'message' => 'Customer created successfully.',
            'customer' => $customer,
        ], 201);
    }

    public function update(string $bookId, string $customerId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $customer = $this->customers->findExistingByIdAndBook($bookId, $customerId);

        if ($customer === null) {
            return $this->failNotFound('Customer not found.');
        }

        $payload = $this->getCustomerPayload();

        try {
            $customer = $this->updateCustomer($bookId, $customerId, $payload);
        } catch (InvalidArgumentException $exception) {
            return $this->respond(['message' => $exception->getMessage()], 422);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        } catch (\Throwable) {
            return $this->failServerError('Unable to update customer right now.');
        }

        return $this->respond([
            'message' => 'Customer updated successfully.',
            'customer' => $customer,
        ]);
    }

    private function getCustomerPayload(): array
    {
        $payload = $this->request->getJSON(true);

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getRawInput();
        }

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }

        return [
            'name' => trim((string) ($payload['name'] ?? '')),
            'phone' => trim((string) ($payload['phone'] ?? '')),
            'messenger' => $this->normalizeOptionalString($payload['messenger'] ?? null),
            'address' => $this->normalizeOptionalString($payload['address'] ?? null),
            'location' => $this->normalizeOptionalString($payload['location'] ?? null),
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

    private function createCustomer(string $userId, string $bookId, array $payload): array
    {
        helper('uuid');

        $this->validateCustomerPayload($payload);

        $customerId = uuid_v4();
        $timestamp = $this->utcNow();
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

        $customer = $this->customers->findOneCustomerByBook($bookId, $customerId);

        if ($customer === null) {
            throw new RuntimeException('Unable to load the new customer right now.');
        }

        return $customer;
    }

    private function updateCustomer(string $bookId, string $customerId, array $payload): array
    {
        $this->validateCustomerPayload($payload);

        $updated = $this->customers->update($customerId, [
            'name' => $payload['name'],
            'phone' => $payload['phone'],
            'messenger' => $payload['messenger'],
            'address' => $payload['address'],
            'location' => $payload['location'],
            'updated_at' => $this->utcNow(),
        ]);

        if ($updated === false) {
            throw new RuntimeException('Unable to update customer right now.');
        }

        $customer = $this->customers->findOneCustomerByBook($bookId, $customerId);

        if ($customer === null) {
            throw new RuntimeException('Unable to load the updated customer right now.');
        }

        return $customer;
    }

    private function normalizeOptionalString(mixed $value): ?string
    {
        $normalizedValue = trim((string) $value);

        return $normalizedValue !== '' ? $normalizedValue : null;
    }
}
