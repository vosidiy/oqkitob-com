<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

final class MinishopSalesDateFilterTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const USER_ID = '10000000-0000-0000-0000-000000000001';
    private const BOOK_ID = '20000000-0000-0000-0000-000000000002';
    private const LOCAL_NOW = '2026-05-26 11:29:07';

    private const SALE_CURRENT_ID = '30000000-0000-0000-0000-000000000003';
    private const SALE_10_INCLUDED_ID = '30000000-0000-0000-0000-000000000010';
    private const SALE_10_EXCLUDED_ID = '30000000-0000-0000-0000-000000000011';
    private const SALE_20_INCLUDED_ID = '30000000-0000-0000-0000-000000000020';
    private const SALE_20_EXCLUDED_ID = '30000000-0000-0000-0000-000000000021';
    private const SALE_30_INCLUDED_ID = '30000000-0000-0000-0000-000000000030';
    private const SALE_30_EXCLUDED_ID = '30000000-0000-0000-0000-000000000031';

    protected function setUp(): void
    {
        parent::setUp();

        config('Session')->driver = ArrayHandler::class;
        Services::reset();

        $db = db_connect('tests');

        $db->query('DROP TABLE IF EXISTS db_minishop_sale_items');
        $db->query('DROP TABLE IF EXISTS db_minishop_sales');
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
    title TEXT NOT NULL,
    description TEXT NULL,
    icon TEXT NULL,
    color TEXT NULL,
    settings_json TEXT NULL,
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
    product_id TEXT NULL,
    product_name TEXT NOT NULL,
    product_sku TEXT NULL,
    quantity TEXT NOT NULL,
    unit_price TEXT NOT NULL,
    line_total TEXT NOT NULL
)
SQL);

        $this->seedFixtures();
    }

    public function testSalesListLast10DaysStartsAtBeginningOfBoundaryDay(): void
    {
        $response = $this->getSales('last_10_days');
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $saleIds = array_column($payload['sales'] ?? [], 'id');

        self::assertContains(self::SALE_CURRENT_ID, $saleIds);
        self::assertContains(self::SALE_10_INCLUDED_ID, $saleIds);
        self::assertNotContains(self::SALE_10_EXCLUDED_ID, $saleIds);
        self::assertNotContains(self::SALE_20_INCLUDED_ID, $saleIds);
    }

    public function testSalesAnalyticsLast10DaysIncludesBoundaryStartAndExcludesPreviousSecond(): void
    {
        $response = $this->getAnalytics('last_10_days');
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $productNames = array_column($payload['products'] ?? [], 'product_name');

        self::assertSame(2, $payload['summary']['sale_count'] ?? null);
        self::assertSame('110.00', $payload['summary']['total_amount'] ?? null);
        self::assertContains('Boundary 10 Included', $productNames);
        self::assertNotContains('Boundary 10 Excluded', $productNames);
    }

    public function testSalesListLast20DaysStartsAtBeginningOfBoundaryDay(): void
    {
        $response = $this->getSales('last_20_days');
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $saleIds = array_column($payload['sales'] ?? [], 'id');

        self::assertContains(self::SALE_20_INCLUDED_ID, $saleIds);
        self::assertNotContains(self::SALE_20_EXCLUDED_ID, $saleIds);
    }

    public function testSalesAnalyticsLast20DaysIncludesBoundaryStartAndExcludesPreviousSecond(): void
    {
        $response = $this->getAnalytics('last_20_days');
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $productNames = array_column($payload['products'] ?? [], 'product_name');

        self::assertSame(4, $payload['summary']['sale_count'] ?? null);
        self::assertSame('131.00', $payload['summary']['total_amount'] ?? null);
        self::assertContains('Boundary 20 Included', $productNames);
        self::assertNotContains('Boundary 20 Excluded', $productNames);
    }

    public function testSalesListLast30DaysStartsAtBeginningOfBoundaryDay(): void
    {
        $response = $this->getSales('last_30_days');
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $saleIds = array_column($payload['sales'] ?? [], 'id');

        self::assertContains(self::SALE_30_INCLUDED_ID, $saleIds);
        self::assertNotContains(self::SALE_30_EXCLUDED_ID, $saleIds);
    }

    public function testSalesAnalyticsLast30DaysIncludesBoundaryStartAndExcludesPreviousSecond(): void
    {
        $response = $this->getAnalytics('last_30_days');
        $response->assertStatus(200);

        $payload = $this->decodeJsonResponse($response->getJSON());
        $productNames = array_column($payload['products'] ?? [], 'product_name');

        self::assertSame(6, $payload['summary']['sale_count'] ?? null);
        self::assertSame('163.00', $payload['summary']['total_amount'] ?? null);
        self::assertContains('Boundary 30 Included', $productNames);
        self::assertNotContains('Boundary 30 Excluded', $productNames);
    }

    private function seedFixtures(): void
    {
        $db = db_connect('tests');

        $db->table('users')->insert([
            'id' => self::USER_ID,
            'email' => 'seller@example.com',
            'status' => 'active',
            'created_at' => '2026-05-26 09:00:00',
            'updated_at' => '2026-05-26 09:00:00',
            'deleted_at' => null,
        ]);

        $db->table('books')->insert([
            'id' => self::BOOK_ID,
            'user_id' => self::USER_ID,
            'type_key' => 'minishop',
            'title' => 'Shop Book',
            'description' => null,
            'icon' => null,
            'color' => null,
            'settings_json' => null,
            'is_archived' => 0,
            'sort_order' => 1,
            'last_opened_at' => null,
            'created_at' => '2026-05-26 09:00:00',
            'updated_at' => '2026-05-26 09:00:00',
            'deleted_at' => null,
        ]);

        $sales = [
            [
                'id' => self::SALE_CURRENT_ID,
                'sold_at' => '2026-05-26 10:00:00',
                'amount' => '100.00',
                'product_name' => 'Current Sale',
                'product_sku' => 'CUR-100',
            ],
            [
                'id' => self::SALE_10_INCLUDED_ID,
                'sold_at' => '2026-05-16 00:00:00',
                'amount' => '10.00',
                'product_name' => 'Boundary 10 Included',
                'product_sku' => 'DAY10-IN',
            ],
            [
                'id' => self::SALE_10_EXCLUDED_ID,
                'sold_at' => '2026-05-15 23:59:59',
                'amount' => '1.00',
                'product_name' => 'Boundary 10 Excluded',
                'product_sku' => 'DAY10-OUT',
            ],
            [
                'id' => self::SALE_20_INCLUDED_ID,
                'sold_at' => '2026-05-06 00:00:00',
                'amount' => '20.00',
                'product_name' => 'Boundary 20 Included',
                'product_sku' => 'DAY20-IN',
            ],
            [
                'id' => self::SALE_20_EXCLUDED_ID,
                'sold_at' => '2026-05-05 23:59:59',
                'amount' => '2.00',
                'product_name' => 'Boundary 20 Excluded',
                'product_sku' => 'DAY20-OUT',
            ],
            [
                'id' => self::SALE_30_INCLUDED_ID,
                'sold_at' => '2026-04-26 00:00:00',
                'amount' => '30.00',
                'product_name' => 'Boundary 30 Included',
                'product_sku' => 'DAY30-IN',
            ],
            [
                'id' => self::SALE_30_EXCLUDED_ID,
                'sold_at' => '2026-04-25 23:59:59',
                'amount' => '3.00',
                'product_name' => 'Boundary 30 Excluded',
                'product_sku' => 'DAY30-OUT',
            ],
        ];

        foreach ($sales as $index => $sale) {
            $this->insertSaleFixture($sale, $index + 1);
        }
    }

    /**
     * @param array{id: string, sold_at: string, amount: string, product_name: string, product_sku: string} $sale
     */
    private function insertSaleFixture(array $sale, int $index): void
    {
        $db = db_connect('tests');

        $db->table('minishop_sales')->insert([
            'id' => $sale['id'],
            'book_id' => self::BOOK_ID,
            'created_by' => self::USER_ID,
            'customer_id' => null,
            'currency_code' => 'UZS',
            'subtotal_amount' => $sale['amount'],
            'discount_amount' => '0.00',
            'total_amount' => $sale['amount'],
            'paid_amount' => $sale['amount'],
            'due_amount' => '0.00',
            'payment_status' => 'paid',
            'note' => null,
            'sold_at' => $sale['sold_at'],
            'created_at' => $sale['sold_at'],
            'updated_at' => $sale['sold_at'],
            'deleted_at' => null,
        ]);

        $db->table('minishop_sale_items')->insert([
            'id' => sprintf('item-%02d', $index),
            'sale_id' => $sale['id'],
            'product_id' => sprintf('product-%02d', $index),
            'product_name' => $sale['product_name'],
            'product_sku' => $sale['product_sku'],
            'quantity' => '1.000',
            'unit_price' => $sale['amount'],
            'line_total' => $sale['amount'],
        ]);
    }

    private function getSales(string $filterTime)
    {
        $query = http_build_query([
            'filter_time' => $filterTime,
            'local_now' => self::LOCAL_NOW,
            'page' => 1,
            'per_page' => 20,
        ]);

        return $this->withSession([
            'user_id' => self::USER_ID,
        ])->get('books/' . self::BOOK_ID . '/minishop/sales?' . $query);
    }

    private function getAnalytics(string $filterTime)
    {
        $query = http_build_query([
            'filter_time' => $filterTime,
            'local_now' => self::LOCAL_NOW,
        ]);

        return $this->withSession([
            'user_id' => self::USER_ID,
        ])->get('books/' . self::BOOK_ID . '/minishop/sales/analytics?' . $query);
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
