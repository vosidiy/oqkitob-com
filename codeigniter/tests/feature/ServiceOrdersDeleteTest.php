<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

final class ServiceOrdersDeleteTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const USER_ID = '70000000-0000-0000-0000-000000000001';
    private const OTHER_USER_ID = '70000000-0000-0000-0000-000000000002';
    private const BOOK_ID = '71000000-0000-0000-0000-000000000001';
    private const OTHER_BOOK_ID = '71000000-0000-0000-0000-000000000002';
    private const INACCESSIBLE_BOOK_ID = '71000000-0000-0000-0000-000000000003';
    private const CUSTOMER_ID = '72000000-0000-0000-0000-000000000001';
    private const ORDER_ID = '73000000-0000-0000-0000-000000000001';
    private const SECOND_ORDER_ID = '73000000-0000-0000-0000-000000000002';
    private const OTHER_BOOK_ORDER_ID = '73000000-0000-0000-0000-000000000003';
    private const INACCESSIBLE_ORDER_ID = '73000000-0000-0000-0000-000000000004';

    protected function setUp(): void
    {
        parent::setUp();

        config('Session')->driver = ArrayHandler::class;
        Services::reset();

        $db = db_connect('tests');

        $db->query('DROP TABLE IF EXISTS db_app_service_order_items');
        $db->query('DROP TABLE IF EXISTS db_app_service_orders');
        $db->query('DROP TABLE IF EXISTS db_app_service_customers');
        $db->query('DROP TABLE IF EXISTS db_books');
        $db->query('DROP TABLE IF EXISTS db_users');

        $db->query(<<<'SQL'
CREATE TABLE db_users (
    id TEXT PRIMARY KEY,
    email TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_books (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL,
    type_key TEXT NOT NULL,
    currency_code TEXT NULL,
    title TEXT NOT NULL,
    description TEXT NULL,
    icon TEXT NULL,
    color TEXT NULL,
    settings_json TEXT NULL,
    show_cents INTEGER NOT NULL DEFAULT 1,
    thousand_separator TEXT NOT NULL DEFAULT 'comma',
    is_archived INTEGER NOT NULL DEFAULT 0,
    sort_order INTEGER NOT NULL DEFAULT 0,
    last_opened_at TEXT NULL,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_app_service_customers (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    created_by TEXT NULL,
    name TEXT NOT NULL,
    phone TEXT NULL,
    messenger TEXT NULL,
    address TEXT NULL,
    location TEXT NULL,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_app_service_orders (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    created_by TEXT NULL,
    customer_id TEXT NULL,
    currency_code TEXT NOT NULL,
    subtotal_amount TEXT NOT NULL,
    discount_amount TEXT NOT NULL,
    total_amount TEXT NOT NULL,
    order_status TEXT NOT NULL,
    note TEXT NULL,
    received_at TEXT NOT NULL,
    ready_at TEXT NULL,
    delivered_at TEXT NULL,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_app_service_order_items (
    id TEXT PRIMARY KEY,
    order_id TEXT NOT NULL,
    service_type_id TEXT NULL,
    object_name TEXT NOT NULL,
    service_name TEXT NOT NULL,
    quantity TEXT NOT NULL,
    unit_code TEXT NOT NULL,
    unit_price TEXT NOT NULL,
    line_total TEXT NOT NULL,
    note TEXT NULL,
    attachment_path TEXT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0
)
SQL);

        $this->seedFixtures();
    }

    public function testDeleteServiceOrderSoftDeletesAndReturnsPayload(): void
    {
        $response = $this->deleteOrder(self::BOOK_ID, self::ORDER_ID);
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $order = $this->findOrder(self::ORDER_ID);

        self::assertSame('Order deleted successfully.', $payload['message'] ?? null);
        self::assertSame(self::ORDER_ID, $payload['orderId'] ?? null);
        self::assertNotEmpty($payload['deleted_at'] ?? null);
        self::assertSame($payload['deleted_at'], $order['deleted_at'] ?? null);
    }

    public function testDeletedServiceOrderNoLongerAppearsInOrderList(): void
    {
        $this->deleteOrder(self::BOOK_ID, self::ORDER_ID)->assertStatus(200);

        $response = $this->getOrders(self::BOOK_ID);
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $orderIds = array_column($payload['orders'] ?? [], 'id');

        self::assertNotContains(self::ORDER_ID, $orderIds);
        self::assertContains(self::SECOND_ORDER_ID, $orderIds);
    }

    public function testDeletedServiceOrderReturns404FromDetailEndpoint(): void
    {
        $this->deleteOrder(self::BOOK_ID, self::ORDER_ID)->assertStatus(200);

        $response = $this->getOrder(self::BOOK_ID, self::ORDER_ID);
        $response->assertStatus(404);

        $payload = $this->decodeJsonResponse($response->getJSON());
        self::assertSame('Order not found.', $payload['messages']['error'] ?? null);
    }

    public function testDeleteMissingServiceOrderReturns404(): void
    {
        $response = $this->deleteOrder(self::BOOK_ID, '73000000-0000-0000-0000-000000000099');
        $response->assertStatus(404);

        $payload = $this->decodeJsonResponse($response->getJSON());
        self::assertSame('Order not found.', $payload['messages']['error'] ?? null);
    }

    public function testDeleteServiceOrderReturns404WhenOrderBelongsToAnotherOwnedBook(): void
    {
        $response = $this->deleteOrder(self::BOOK_ID, self::OTHER_BOOK_ORDER_ID);
        $response->assertStatus(404);

        $payload = $this->decodeJsonResponse($response->getJSON());
        self::assertSame('Order not found.', $payload['messages']['error'] ?? null);
    }

    public function testDeleteServiceOrderReturns404ForInaccessibleBook(): void
    {
        $response = $this->deleteOrder(self::INACCESSIBLE_BOOK_ID, self::INACCESSIBLE_ORDER_ID);
        $response->assertStatus(404);

        $payload = $this->decodeJsonResponse($response->getJSON());
        self::assertSame('Book not found.', $payload['messages']['error'] ?? null);
    }

    private function seedFixtures(): void
    {
        $db = db_connect('tests');

        $db->table('users')->insertBatch([
            [
                'id' => self::USER_ID,
                'email' => 'owner@example.com',
                'status' => 'active',
                'created_at' => '2026-06-21 09:00:00',
                'updated_at' => '2026-06-21 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => self::OTHER_USER_ID,
                'email' => 'other@example.com',
                'status' => 'active',
                'created_at' => '2026-06-21 09:05:00',
                'updated_at' => '2026-06-21 09:05:00',
                'deleted_at' => null,
            ],
        ]);

        $db->table('books')->insertBatch([
            [
                'id' => self::BOOK_ID,
                'user_id' => self::USER_ID,
                'type_key' => 'service',
                'currency_code' => 'UZS',
                'title' => 'Main Service Book',
                'description' => null,
                'icon' => null,
                'color' => null,
                'settings_json' => null,
                'show_cents' => 1,
                'thousand_separator' => 'comma',
                'is_archived' => 0,
                'sort_order' => 1,
                'last_opened_at' => null,
                'created_at' => '2026-06-21 09:10:00',
                'updated_at' => '2026-06-21 09:10:00',
                'deleted_at' => null,
            ],
            [
                'id' => self::OTHER_BOOK_ID,
                'user_id' => self::USER_ID,
                'type_key' => 'service',
                'currency_code' => 'UZS',
                'title' => 'Secondary Service Book',
                'description' => null,
                'icon' => null,
                'color' => null,
                'settings_json' => null,
                'show_cents' => 1,
                'thousand_separator' => 'comma',
                'is_archived' => 0,
                'sort_order' => 2,
                'last_opened_at' => null,
                'created_at' => '2026-06-21 09:20:00',
                'updated_at' => '2026-06-21 09:20:00',
                'deleted_at' => null,
            ],
            [
                'id' => self::INACCESSIBLE_BOOK_ID,
                'user_id' => self::OTHER_USER_ID,
                'type_key' => 'service',
                'currency_code' => 'USD',
                'title' => 'Private Service Book',
                'description' => null,
                'icon' => null,
                'color' => null,
                'settings_json' => null,
                'show_cents' => 1,
                'thousand_separator' => 'comma',
                'is_archived' => 0,
                'sort_order' => 1,
                'last_opened_at' => null,
                'created_at' => '2026-06-21 09:30:00',
                'updated_at' => '2026-06-21 09:30:00',
                'deleted_at' => null,
            ],
        ]);

        $db->table('app_service_customers')->insert([
            'id' => self::CUSTOMER_ID,
            'book_id' => self::BOOK_ID,
            'created_by' => self::USER_ID,
            'name' => 'Client One',
            'phone' => '+998901234567',
            'messenger' => null,
            'address' => null,
            'location' => null,
            'created_at' => '2026-06-21 10:00:00',
            'updated_at' => '2026-06-21 10:00:00',
            'deleted_at' => null,
        ]);

        $this->insertOrderFixture(
            self::ORDER_ID,
            self::BOOK_ID,
            self::USER_ID,
            self::CUSTOMER_ID,
            'received',
            '2026-06-21 10:15:00',
            '11.00'
        );
        $this->insertOrderFixture(
            self::SECOND_ORDER_ID,
            self::BOOK_ID,
            self::USER_ID,
            self::CUSTOMER_ID,
            'working',
            '2026-06-21 10:45:00',
            '22.00'
        );
        $this->insertOrderFixture(
            self::OTHER_BOOK_ORDER_ID,
            self::OTHER_BOOK_ID,
            self::USER_ID,
            null,
            'received',
            '2026-06-21 11:15:00',
            '33.00'
        );
        $this->insertOrderFixture(
            self::INACCESSIBLE_ORDER_ID,
            self::INACCESSIBLE_BOOK_ID,
            self::OTHER_USER_ID,
            null,
            'received',
            '2026-06-21 11:45:00',
            '44.00'
        );
    }

    private function insertOrderFixture(
        string $orderId,
        string $bookId,
        string $userId,
        ?string $customerId,
        string $status,
        string $receivedAt,
        string $totalAmount
    ): void {
        $db = db_connect('tests');

        $db->table('app_service_orders')->insert([
            'id' => $orderId,
            'book_id' => $bookId,
            'created_by' => $userId,
            'customer_id' => $customerId,
            'currency_code' => 'UZS',
            'subtotal_amount' => $totalAmount,
            'discount_amount' => '0.00',
            'total_amount' => $totalAmount,
            'order_status' => $status,
            'note' => null,
            'received_at' => $receivedAt,
            'ready_at' => null,
            'delivered_at' => null,
            'created_at' => $receivedAt,
            'updated_at' => $receivedAt,
            'deleted_at' => null,
        ]);

        $db->table('app_service_order_items')->insert([
            'id' => 'item-' . $orderId,
            'order_id' => $orderId,
            'service_type_id' => null,
            'object_name' => 'Demo Item ' . substr($orderId, -4),
            'service_name' => 'Dry Clean',
            'quantity' => '1.000',
            'unit_code' => 'qty',
            'unit_price' => $totalAmount,
            'line_total' => $totalAmount,
            'note' => null,
            'attachment_path' => null,
            'sort_order' => 0,
        ]);
    }

    private function deleteOrder(string $bookId, string $orderId)
    {
        return $this->withSession([
            'user_id' => self::USER_ID,
        ])->delete('books/' . $bookId . '/service/orders/' . $orderId);
    }

    private function getOrders(string $bookId)
    {
        return $this->withSession([
            'user_id' => self::USER_ID,
        ])->get('books/' . $bookId . '/service/orders?exclude_order_status=delivered');
    }

    private function getOrder(string $bookId, string $orderId)
    {
        return $this->withSession([
            'user_id' => self::USER_ID,
        ])->get('books/' . $bookId . '/service/orders/' . $orderId);
    }

    /**
     * @return array<string, mixed>
     */
    private function findOrder(string $orderId): array
    {
        $order = db_connect('tests')->table('app_service_orders')
            ->where('id', $orderId)
            ->get()
            ->getRowArray();

        self::assertIsArray($order);

        return $order;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonResponse(?string $json): array
    {
        self::assertIsString($json);

        /** @var array<string, mixed> $payload */
        $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return $payload;
    }
}
