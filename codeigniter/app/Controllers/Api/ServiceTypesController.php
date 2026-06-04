<?php

namespace App\Controllers\Api;

use App\Models\ServiceTypeModel;
use App\Services\BookAccessService;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authenticated service-type endpoints.
 *
 * Routes:
 * GET    /api/books/{bookId}/service/types
 * POST   /api/books/{bookId}/service/types
 * PUT    /api/books/{bookId}/service/types/{serviceTypeId}
 * DELETE /api/books/{bookId}/service/types/{serviceTypeId}
 */
class ServiceTypesController extends AuthenticatedApiController
{
    private const ALLOWED_UNITS = ['qty', 'm2', 'kg'];

    public function __construct(
        private readonly BookAccessService $bookAccess = new BookAccessService(),
        private readonly ServiceTypeModel $serviceTypes = new ServiceTypeModel()
    ) {
    }

    public function index(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        return $this->respond([
            'serviceTypes' => $this->serviceTypes->findByBook($bookId),
        ]);
    }

    public function create(string $bookId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $payload = $this->getServiceTypePayload();

        try {
            $serviceType = $this->createServiceType($userId, $bookId, $payload);
        } catch (InvalidArgumentException $exception) {
            return $this->respond(['message' => $exception->getMessage()], 422);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        } catch (\Throwable) {
            return $this->failServerError('Unable to create service type right now.');
        }

        return $this->respond([
            'message' => 'Service type created successfully.',
            'serviceType' => $serviceType,
        ], 201);
    }

    public function update(string $bookId, string $serviceTypeId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $serviceType = $this->serviceTypes->findExistingByIdAndBook($bookId, $serviceTypeId);

        if ($serviceType === null) {
            return $this->failNotFound('Service type not found.');
        }

        $payload = $this->getServiceTypePayload();

        try {
            $serviceType = $this->updateServiceType($bookId, $serviceTypeId, $payload);
        } catch (InvalidArgumentException $exception) {
            return $this->respond(['message' => $exception->getMessage()], 422);
        } catch (RuntimeException $exception) {
            return $this->failServerError($exception->getMessage());
        } catch (\Throwable) {
            return $this->failServerError('Unable to update service type right now.');
        }

        return $this->respond([
            'message' => 'Service type updated successfully.',
            'serviceType' => $serviceType,
        ]);
    }

    public function delete(string $bookId, string $serviceTypeId)
    {
        $userId = $this->currentUserIdAndCloseSession();
        $permission = $this->bookAccess->getUserBookPermission($userId, $bookId, 'service');

        if ($permission === 'none') {
            return $this->failNotFound('Book not found.');
        }

        $serviceType = $this->serviceTypes->findExistingByIdAndBook($bookId, $serviceTypeId);

        if ($serviceType === null) {
            return $this->failNotFound('Service type not found.');
        }

        $timestamp = $this->utcNow();
        $deleted = $this->serviceTypes->update($serviceTypeId, [
            'deleted_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        if ($deleted === false) {
            return $this->failServerError('Unable to delete service type right now.');
        }

        return $this->respond([
            'message' => 'Service type deleted successfully.',
            'serviceTypeId' => $serviceTypeId,
            'deleted_at' => $timestamp,
        ]);
    }

    private function getServiceTypePayload(): array
    {
        $payload = $this->request->getJSON(true);

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getRawInput();
        }

        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $defaultUnit = trim((string) ($payload['default_unit'] ?? ''));
        $defaultPrice = trim((string) ($payload['default_price'] ?? ''));

        return [
            'name' => $name,
            'default_unit' => $defaultUnit !== '' ? $defaultUnit : null,
            'default_price' => $defaultPrice !== '' ? $defaultPrice : null,
        ];
    }

    private function createServiceType(string $userId, string $bookId, array $payload): array
    {
        helper('uuid');

        $this->validateServiceTypePayload($payload);

        $serviceTypeId = uuid_v4();
        $timestamp = $this->utcNow();
        $created = $this->serviceTypes->insert([
            'id' => $serviceTypeId,
            'book_id' => $bookId,
            'created_by' => $userId,
            'name' => $payload['name'],
            'default_unit' => $payload['default_unit'],
            'default_price' => $this->formatMoney($this->normalizeMoney($payload['default_price'])),
            'sort_order' => $this->serviceTypes->findNextSortOrderByBook($bookId),
            'is_active' => 1,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        if ($created === false) {
            throw new RuntimeException('Unable to create service type right now.');
        }

        $serviceType = $this->serviceTypes->findListRowByIdAndBook($bookId, $serviceTypeId);

        if ($serviceType === null) {
            throw new RuntimeException('Unable to load the new service type right now.');
        }

        return $serviceType;
    }

    private function updateServiceType(string $bookId, string $serviceTypeId, array $payload): array
    {
        $this->validateServiceTypePayload($payload);

        $updated = $this->serviceTypes->update($serviceTypeId, [
            'name' => $payload['name'],
            'default_unit' => $payload['default_unit'],
            'default_price' => $this->formatMoney($this->normalizeMoney($payload['default_price'])),
            'updated_at' => $this->utcNow(),
        ]);

        if ($updated === false) {
            throw new RuntimeException('Unable to update service type right now.');
        }

        $serviceType = $this->serviceTypes->findListRowByIdAndBook($bookId, $serviceTypeId);

        if ($serviceType === null) {
            throw new RuntimeException('Unable to load the updated service type right now.');
        }

        return $serviceType;
    }

    private function validateServiceTypePayload(array $payload): void
    {
        $name = (string) ($payload['name'] ?? '');

        if ($name === '') {
            throw new InvalidArgumentException('Service name is required.');
        }

        if (mb_strlen($name) > 255) {
            throw new InvalidArgumentException('Service name must be 255 characters or fewer.');
        }

        $defaultUnit = $payload['default_unit'] ?? null;

        if ($defaultUnit === null || ! in_array((string) $defaultUnit, self::ALLOWED_UNITS, true)) {
            throw new InvalidArgumentException('Please choose a valid service unit.');
        }

        $defaultPrice = $payload['default_price'] ?? null;

        if (! $this->isNumericValue($defaultPrice)) {
            throw new InvalidArgumentException('Please enter a valid default price.');
        }

        if ($this->normalizeMoney($defaultPrice) < 0) {
            throw new InvalidArgumentException('Default price cannot be negative.');
        }
    }

    private function normalizeMoney(mixed $value): float
    {
        return round((float) $value, 2);
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 2, '.', '');
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
