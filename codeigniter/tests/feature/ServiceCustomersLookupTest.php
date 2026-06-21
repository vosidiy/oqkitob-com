<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

final class ServiceCustomersLookupTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const USER_ID = '74000000-0000-0000-0000-000000000001';
    private const OTHER_USER_ID = '74000000-0000-0000-0000-000000000002';
    private const BOOK_ID = '75000000-0000-0000-0000-000000000001';
    private const OTHER_BOOK_ID = '75000000-0000-0000-0000-000000000002';
    private const INACCESSIBLE_BOOK_ID = '75000000-0000-0000-0000-000000000003';
    private const OLDER_CUSTOMER_ID = '76000000-0000-0000-0000-000000000001';
    private const NEWER_CUSTOMER_ID = '76000000-0000-0000-0000-000000000002';
    private const OTHER_BOOK_CUSTOMER_ID = '76000000-0000-0000-0000-000000000003';
    private const INACCESSIBLE_BOOK_CUSTOMER_ID = '76000000-0000-0000-0000-000000000004';

    protected function setUp(): void
    {
        parent::setUp();

        config('Session')->driver = ArrayHandler::class;
        Services::reset();

        $db = db_connect('tests');

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

        $this->seedFixtures();
    }

    public function testLookupReturnsMostRecentlyUpdatedExactPhoneMatchForBook(): void
    {
        $response = $this->lookupCustomer(self::BOOK_ID, ' +998 90 123-45-67 ');
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $customer = $payload['customer'] ?? null;

        self::assertIsArray($customer);
        self::assertSame(self::NEWER_CUSTOMER_ID, $customer['id'] ?? null);
        self::assertSame('Repeat Client New', $customer['name'] ?? null);
        self::assertSame('+998901234567', $customer['phone'] ?? null);
        self::assertSame('@repeat-new', $customer['messenger'] ?? null);
        self::assertSame('Second Address', $customer['address'] ?? null);
        self::assertSame('https://maps.example/new', $customer['location'] ?? null);
    }

    public function testLookupReturnsNullWhenNoMatchExists(): void
    {
        $response = $this->lookupCustomer(self::BOOK_ID, '+998900000000');
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        self::assertArrayHasKey('customer', $payload);
        self::assertNull($payload['customer']);
    }

    public function testLookupReturnsNullWhenPhoneCannotBeNormalized(): void
    {
        $response = $this->lookupCustomer(self::BOOK_ID, '+998-AB-12');
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        self::assertArrayHasKey('customer', $payload);
        self::assertNull($payload['customer']);
    }

    public function testLookupFindsCustomerWhenLocalDigitsAreFormatted(): void
    {
        $response = $this->lookupCustomer(self::OTHER_BOOK_ID, ' 90 777-77-77 ');
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $customer = $payload['customer'] ?? null;

        self::assertIsArray($customer);
        self::assertSame(self::OTHER_BOOK_CUSTOMER_ID, $customer['id'] ?? null);
        self::assertSame('907777777', $customer['phone'] ?? null);
    }

    public function testLookupDoesNotReturnCustomerFromAnotherBook(): void
    {
        $response = $this->lookupCustomer(self::BOOK_ID, '+998907777777');
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        self::assertNull($payload['customer'] ?? null);
    }

    public function testLookupReturns404ForInaccessibleBook(): void
    {
        $response = $this->lookupCustomer(self::INACCESSIBLE_BOOK_ID, '+998908888888');
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

        $db->table('app_service_customers')->insertBatch([
            [
                'id' => self::OLDER_CUSTOMER_ID,
                'book_id' => self::BOOK_ID,
                'created_by' => self::USER_ID,
                'name' => 'Repeat Client Old',
                'phone' => '+998901234567',
                'messenger' => '@repeat-old',
                'address' => 'First Address',
                'location' => 'https://maps.example/old',
                'created_at' => '2026-06-21 10:00:00',
                'updated_at' => '2026-06-21 10:05:00',
                'deleted_at' => null,
            ],
            [
                'id' => self::NEWER_CUSTOMER_ID,
                'book_id' => self::BOOK_ID,
                'created_by' => self::USER_ID,
                'name' => 'Repeat Client New',
                'phone' => '+998901234567',
                'messenger' => '@repeat-new',
                'address' => 'Second Address',
                'location' => 'https://maps.example/new',
                'created_at' => '2026-06-21 10:10:00',
                'updated_at' => '2026-06-21 10:20:00',
                'deleted_at' => null,
            ],
            [
                'id' => self::OTHER_BOOK_CUSTOMER_ID,
                'book_id' => self::OTHER_BOOK_ID,
                'created_by' => self::USER_ID,
                'name' => 'Other Book Client',
                'phone' => '907777777',
                'messenger' => '@other-book',
                'address' => 'Other Book Address',
                'location' => null,
                'created_at' => '2026-06-21 10:30:00',
                'updated_at' => '2026-06-21 10:30:00',
                'deleted_at' => null,
            ],
            [
                'id' => self::INACCESSIBLE_BOOK_CUSTOMER_ID,
                'book_id' => self::INACCESSIBLE_BOOK_ID,
                'created_by' => self::OTHER_USER_ID,
                'name' => 'Private Client',
                'phone' => '+998908888888',
                'messenger' => '@private-client',
                'address' => 'Private Address',
                'location' => null,
                'created_at' => '2026-06-21 10:40:00',
                'updated_at' => '2026-06-21 10:40:00',
                'deleted_at' => null,
            ],
        ]);
    }

    private function lookupCustomer(string $bookId, string $phone)
    {
        return $this->withSession([
            'user_id' => self::USER_ID,
        ])->get('books/' . $bookId . '/service/customers/lookup?phone=' . rawurlencode($phone));
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
