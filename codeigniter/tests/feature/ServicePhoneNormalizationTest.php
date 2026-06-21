<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

final class ServicePhoneNormalizationTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const USER_ID = '77000000-0000-0000-0000-000000000001';
    private const OTHER_USER_ID = '77000000-0000-0000-0000-000000000002';
    private const BOOK_ID = '78000000-0000-0000-0000-000000000001';
    private const INACCESSIBLE_BOOK_ID = '78000000-0000-0000-0000-000000000002';
    private const SERVICE_TYPE_ID = '79000000-0000-0000-0000-000000000001';
    private const EXISTING_CUSTOMER_ID = '7a000000-0000-0000-0000-000000000001';

    protected function setUp(): void
    {
        parent::setUp();

        config('Session')->driver = ArrayHandler::class;
        Services::reset();

        $db = db_connect('tests');

        $db->query('DROP TABLE IF EXISTS db_app_service_order_items');
        $db->query('DROP TABLE IF EXISTS db_app_service_orders');
        $db->query('DROP TABLE IF EXISTS db_app_service_types');
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
CREATE TABLE db_app_service_types (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    created_by TEXT NULL,
    name TEXT NOT NULL,
    default_unit TEXT NOT NULL,
    default_price TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
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

    public function testCreateServiceCustomerStoresNormalizedPhone(): void
    {
        $response = $this->postCustomer([
            'name' => 'Formatted Client',
            'phone' => '94 111-22-33',
            'messenger' => '@formatted',
            'address' => 'Yunusobod',
            'location' => null,
        ]);
        $response->assertStatus(201);

        $payload = $this->decodeJsonResponse($response->getJSON());

        self::assertSame('941112233', $payload['customer']['phone'] ?? null);
        self::assertSame('941112233', $this->findCustomerById((string) ($payload['customer']['id'] ?? ''))['phone'] ?? null);
    }

    public function testCreateServiceCustomerPreservesLeadingPlusWhenTyped(): void
    {
        $response = $this->postCustomer([
            'name' => 'Plus Client',
            'phone' => '+998 94 111-22-33',
            'messenger' => null,
            'address' => null,
            'location' => null,
        ]);
        $response->assertStatus(201);

        $payload = $this->decodeJsonResponse($response->getJSON());

        self::assertSame('+998941112233', $payload['customer']['phone'] ?? null);
    }

    public function testUpdateServiceCustomerStoresNormalizedPhone(): void
    {
        $response = $this->putCustomer(self::EXISTING_CUSTOMER_ID, [
            'name' => 'Existing Client',
            'phone' => '+998 90 123-45-67',
            'messenger' => '@existing',
            'address' => 'Chilonzor',
            'location' => null,
        ]);
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());

        self::assertSame('+998901234567', $payload['customer']['phone'] ?? null);
        self::assertSame('+998901234567', $this->findCustomerById(self::EXISTING_CUSTOMER_ID)['phone'] ?? null);
    }

    public function testCreateServiceCustomerRejectsPhoneContainingLetters(): void
    {
        $response = $this->postCustomer([
            'name' => 'Invalid Client',
            'phone' => '+998 90 ABC 45 67',
            'messenger' => null,
            'address' => null,
            'location' => null,
        ]);
        $response->assertStatus(422);

        $payload = $this->decodeJsonResponse($response->getJSON());
        self::assertSame('Please enter a valid phone number. Letters are not allowed.', $payload['message'] ?? null);
    }

    public function testCreateServiceOrderReusesExistingCustomerWhenPhoneIsFormatted(): void
    {
        $response = $this->postOrder([
            'customer' => [
                'name' => 'Existing Client Updated',
                'phone' => '90 123-45-67',
                'messenger' => '@updated',
                'address' => 'Updated Address',
                'location' => 'https://maps.example/updated',
            ],
            'items' => [
                [
                    'object_name' => 'Winter Coat',
                    'service_type_id' => self::SERVICE_TYPE_ID,
                    'quantity' => '1',
                    'unit_code' => 'qty',
                    'unit_price' => '35000',
                    'note' => '',
                ],
            ],
            'discount_amount' => '0',
            'note' => 'Formatted phone order',
        ]);
        $response->assertStatus(201);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $order = $payload['order'] ?? null;

        self::assertIsArray($order);
        self::assertSame(self::EXISTING_CUSTOMER_ID, $order['customer_id'] ?? null);
        self::assertSame(1, $this->countCustomersForBook(self::BOOK_ID));

        $customer = $this->findCustomerById(self::EXISTING_CUSTOMER_ID);
        self::assertSame('901234567', $customer['phone'] ?? null);
        self::assertSame('Existing Client Updated', $customer['name'] ?? null);
        self::assertSame('@updated', $customer['messenger'] ?? null);
    }

    public function testCreateServiceOrderRejectsPhoneContainingLetters(): void
    {
        $response = $this->postOrder([
            'customer' => [
                'name' => 'Invalid Order Client',
                'phone' => '+998 90 BAD 45 67',
                'messenger' => null,
                'address' => null,
                'location' => null,
            ],
            'items' => [
                [
                    'object_name' => 'Blanket',
                    'service_type_id' => self::SERVICE_TYPE_ID,
                    'quantity' => '1',
                    'unit_code' => 'qty',
                    'unit_price' => '10000',
                    'note' => '',
                ],
            ],
            'discount_amount' => '0',
            'note' => '',
        ]);
        $response->assertStatus(422);

        $payload = $this->decodeJsonResponse($response->getJSON());
        self::assertSame('Please enter a valid phone number. Letters are not allowed.', $payload['message'] ?? null);
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
            'id' => self::EXISTING_CUSTOMER_ID,
            'book_id' => self::BOOK_ID,
            'created_by' => self::USER_ID,
            'name' => 'Existing Client',
            'phone' => '901234567',
            'messenger' => '@existing',
            'address' => 'Old Address',
            'location' => null,
            'created_at' => '2026-06-21 10:00:00',
            'updated_at' => '2026-06-21 10:00:00',
            'deleted_at' => null,
        ]);

        $db->table('app_service_types')->insert([
            'id' => self::SERVICE_TYPE_ID,
            'book_id' => self::BOOK_ID,
            'created_by' => self::USER_ID,
            'name' => 'Dry Clean',
            'default_unit' => 'qty',
            'default_price' => '35000.00',
            'sort_order' => 0,
            'is_active' => 1,
            'created_at' => '2026-06-21 10:05:00',
            'updated_at' => '2026-06-21 10:05:00',
            'deleted_at' => null,
        ]);
    }

    private function postCustomer(array $payload)
    {
        return $this->withSession([
            'user_id' => self::USER_ID,
        ])->post('books/' . self::BOOK_ID . '/service/customers', $payload);
    }

    private function putCustomer(string $customerId, array $payload)
    {
        return $this->withSession([
            'user_id' => self::USER_ID,
        ])->put('books/' . self::BOOK_ID . '/service/customers/' . $customerId, $payload);
    }

    private function postOrder(array $payload)
    {
        return $this->withSession([
            'user_id' => self::USER_ID,
        ])->post('books/' . self::BOOK_ID . '/service/orders', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function findCustomerById(string $customerId): array
    {
        $customer = db_connect('tests')->table('app_service_customers')
            ->where('id', $customerId)
            ->get()
            ->getRowArray();

        self::assertIsArray($customer);

        return $customer;
    }

    private function countCustomersForBook(string $bookId): int
    {
        return db_connect('tests')->table('app_service_customers')
            ->where('book_id', $bookId)
            ->countAllResults();
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
