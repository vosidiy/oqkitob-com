<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

final class MinishopSalesOversellTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const USER_ID = '11111111-1111-1111-1111-111111111111';
    private const BOOK_ID = '22222222-2222-2222-2222-222222222222';
    private const PRODUCT_ID = '33333333-3333-3333-3333-333333333333';

    protected function setUp(): void
    {
        parent::setUp();

        config('Session')->driver = ArrayHandler::class;
        Services::reset();

        $db = db_connect('tests');

        $db->query('DROP TABLE IF EXISTS db_minishop_sale_payments');
        $db->query('DROP TABLE IF EXISTS db_minishop_sale_items');
        $db->query('DROP TABLE IF EXISTS db_minishop_sales');
        $db->query('DROP TABLE IF EXISTS db_minishop_products');
        $db->query('DROP TABLE IF EXISTS db_minishop_customers');
        $db->query('DROP TABLE IF EXISTS db_books');
        $db->query('DROP TABLE IF EXISTS db_users');

        $db->query(<<<'SQL'
CREATE TABLE db_users (
    id TEXT PRIMARY KEY,
    email TEXT NOT NULL,
    password_hash TEXT NULL,
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
CREATE TABLE db_minishop_customers (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    created_by TEXT NULL,
    name TEXT NOT NULL,
    phone TEXT NULL,
    note TEXT NULL,
    reminder_at TEXT NULL,
    reminder_note TEXT NULL,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_minishop_products (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    created_by TEXT NOT NULL,
    category_id TEXT NULL,
    name TEXT NOT NULL,
    sku TEXT NULL,
    price TEXT NOT NULL,
    quantity TEXT NOT NULL,
    low_stock_alert TEXT NULL,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_minishop_sales (
    id TEXT PRIMARY KEY,
    book_id TEXT NOT NULL,
    created_by TEXT NOT NULL,
    customer_id TEXT NULL,
    currency_code TEXT NOT NULL,
    subtotal_amount TEXT NOT NULL,
    discount_amount TEXT NOT NULL,
    total_amount TEXT NOT NULL,
    paid_amount TEXT NOT NULL,
    due_amount TEXT NOT NULL,
    payment_status TEXT NOT NULL,
    note TEXT NULL,
    sold_at TEXT NOT NULL,
    created_at TEXT NULL,
    updated_at TEXT NULL,
    deleted_at TEXT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_minishop_sale_items (
    id TEXT PRIMARY KEY,
    sale_id TEXT NOT NULL,
    product_id TEXT NOT NULL,
    product_name TEXT NOT NULL,
    product_sku TEXT NULL,
    quantity TEXT NOT NULL,
    unit_price TEXT NOT NULL,
    line_total TEXT NOT NULL
)
SQL);

        $db->query(<<<'SQL'
CREATE TABLE db_minishop_sale_payments (
    id TEXT PRIMARY KEY,
    sale_id TEXT NOT NULL,
    created_by TEXT NOT NULL,
    currency_code TEXT NOT NULL,
    amount TEXT NOT NULL,
    payment_method TEXT NOT NULL,
    paid_at TEXT NOT NULL,
    note TEXT NULL,
    created_at TEXT NULL
)
SQL);

        $this->seedBookFixture();
    }

    public function testCreateSaleAllowsOversellWhenStockIsZero(): void
    {
        $this->seedProduct('0.000');

        $response = $this->postSale([
            [
                'product_id' => self::PRODUCT_ID,
                'quantity' => 1,
                'unit_price' => 12000,
            ],
        ]);

        $response->assertStatus(201);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $product = $this->findProduct();

        self::assertSame('Sale created successfully.', $payload['message']);
        self::assertSame('-1.000', $product['quantity']);
        self::assertSame('1.000', $payload['items'][0]['quantity']);
    }

    public function testCreateSaleAllowsOversellAndDeleteRestoresOriginalStock(): void
    {
        $this->seedProduct('2.000');

        $createResponse = $this->postSale([
            [
                'product_id' => self::PRODUCT_ID,
                'quantity' => 5,
                'unit_price' => 12000,
            ],
        ]);

        $createResponse->assertStatus(201);

        $createPayload = $this->decodeJsonResponse($createResponse->getJSON());

        self::assertSame('-3.000', $this->findProduct()['quantity']);

        $deleteResponse = $this->withSession([
            'user_id' => self::USER_ID,
        ])->delete('books/' . self::BOOK_ID . '/minishop/sales/' . $createPayload['sale']['id']);

        $deleteResponse->assertStatus(200);

        $deletePayload = $this->decodeJsonResponse($deleteResponse->getJSON());

        self::assertSame('Sale deleted successfully.', $deletePayload['message']);
        self::assertSame('2.000', $this->findProduct()['quantity']);
    }

    public function testCreateSaleRejectsNonPositiveQuantity(): void
    {
        $this->seedProduct('4.000');

        foreach ([0, -1] as $quantity) {
            $response = $this->postSale([
                [
                    'product_id' => self::PRODUCT_ID,
                    'quantity' => $quantity,
                    'unit_price' => 12000,
                ],
            ]);

            $response->assertStatus(422);

            $payload = $this->decodeJsonResponse($response->getJSON());

            self::assertSame('Sale item 1 must have a quantity greater than zero.', $payload['message']);
        }
    }

    public function testCreateSaleUsesBooksCurrencyCodeInsteadOfClientPayload(): void
    {
        $this->seedProduct('4.000');

        $response = $this->postSale([
            [
                'product_id' => self::PRODUCT_ID,
                'quantity' => 1,
                'unit_price' => 12000,
            ],
        ], [
            'paid_amount' => 12000,
        ]);

        $response->assertStatus(201);

        $payload = $this->decodeJsonResponse($response->getJSON());

        self::assertSame('UZS', $payload['sale']['currency_code']);
        self::assertSame('UZS', $payload['payments'][0]['currency_code']);
    }

    public function testCreateSaleAllowsEmptyBooksCurrencyCode(): void
    {
        $this->seedProduct('4.000');
        db_connect('tests')->table('books')
            ->where('id', self::BOOK_ID)
            ->update([
                'currency_code' => null,
            ]);

        $response = $this->postSale([
            [
                'product_id' => self::PRODUCT_ID,
                'quantity' => 1,
                'unit_price' => 12000,
            ],
        ], [
            'paid_amount' => 12000,
        ]);

        $response->assertStatus(201);

        $payload = $this->decodeJsonResponse($response->getJSON());

        self::assertSame('', $payload['sale']['currency_code']);
        self::assertSame('', $payload['payments'][0]['currency_code']);
    }

    private function seedBookFixture(): void
    {
        $db = db_connect('tests');

        $db->table('users')->insert([
            'id' => self::USER_ID,
            'email' => 'seller@example.com',
            'status' => 'active',
            'created_at' => '2026-05-23 10:00:00',
            'updated_at' => '2026-05-23 10:00:00',
            'deleted_at' => null,
        ]);

        $db->table('books')->insert([
            'id' => self::BOOK_ID,
            'user_id' => self::USER_ID,
            'type_key' => 'minishop',
            'currency_code' => 'UZS',
            'title' => 'Shop Book',
            'description' => null,
            'icon' => null,
            'color' => null,
            'settings_json' => null,
            'show_cents' => 1,
            'thousand_separator' => 'comma',
            'is_archived' => 0,
            'sort_order' => 1,
            'last_opened_at' => null,
            'created_at' => '2026-05-23 10:00:00',
            'updated_at' => '2026-05-23 10:00:00',
            'deleted_at' => null,
        ]);
    }

    private function seedProduct(string $quantity): void
    {
        $db = db_connect('tests');

        $db->table('app_minishop_sale_payments')->emptyTable();
        $db->table('app_minishop_sale_items')->emptyTable();
        $db->table('app_minishop_sales')->emptyTable();
        $db->table('app_minishop_products')->emptyTable();

        $db->table('app_minishop_products')->insert([
            'id' => self::PRODUCT_ID,
            'book_id' => self::BOOK_ID,
            'created_by' => self::USER_ID,
            'category_id' => null,
            'name' => 'Demo Product',
            'sku' => 'SKU-1',
            'price' => '12000.00',
            'quantity' => $quantity,
            'low_stock_alert' => '2',
            'is_active' => 1,
            'created_at' => '2026-05-23 10:00:00',
            'updated_at' => '2026-05-23 10:00:00',
            'deleted_at' => null,
        ]);
    }

    private function postSale(array $items, array $overrides = [])
    {
        $payload = array_merge([
            'discount_amount' => 0,
            'paid_amount' => 0,
            'payment_method' => 'cash',
            'sold_at' => '2026-05-23 10:15:00',
            'paid_at' => '2026-05-23 10:15:00',
            'items' => $items,
        ], $overrides);

        return $this->withSession([
            'user_id' => self::USER_ID,
        ])->withBodyFormat('json')
            ->post('books/' . self::BOOK_ID . '/minishop/sales', $payload);
    }

    private function findProduct(): array
    {
        $product = db_connect('tests')->table('app_minishop_products')
            ->where('id', self::PRODUCT_ID)
            ->get()
            ->getRowArray();

        self::assertIsArray($product);

        return $product;
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
