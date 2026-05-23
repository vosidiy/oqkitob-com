<?php

declare(strict_types=1);

namespace Tests\Database;

use App\Models\MinishopSaleModel;
use CodeIgniter\Test\CIUnitTestCase;

final class MinishopSalesSearchModelTest extends CIUnitTestCase
{
    private const BOOK_ID = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';
    private const TODAY_CUSTOMER_ID = 'cccccccc-cccc-cccc-cccc-cccccccccccc';
    private const OTHER_CUSTOMER_ID = 'dddddddd-dddd-dddd-dddd-dddddddddddd';
    private const TODAY_SALE_ID = 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee';
    private const YESTERDAY_SALE_ID = 'ffffffff-ffff-ffff-ffff-ffffffffffff';
    private const MULTI_MATCH_SALE_ID = '11111111-2222-3333-4444-555555555555';
    private const PRODUCT_NAME_SALE_ID = '66666666-7777-8888-9999-000000000000';

    protected function setUp(): void
    {
        parent::setUp();

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

    public function testFindByBookMatchesCustomerName(): void
    {
        $model = new MinishopSaleModel();

        self::assertSame(
            [self::YESTERDAY_SALE_ID, self::TODAY_SALE_ID],
            array_column($model->findByBook(self::BOOK_ID, null, null, 'Alice'), 'id')
        );
    }

    public function testFindByBookMatchesSaleId(): void
    {
        $model = new MinishopSaleModel();

        self::assertSame(
            [self::TODAY_SALE_ID],
            array_column($model->findByBook(self::BOOK_ID, null, null, 'eeeeeeee'), 'id')
        );
    }

    public function testFindByBookMatchesSaleItemProductName(): void
    {
        $model = new MinishopSaleModel();

        self::assertSame(
            [self::PRODUCT_NAME_SALE_ID],
            array_column($model->findByBook(self::BOOK_ID, null, null, 'Atlas'), 'id')
        );
    }

    public function testFindByBookMatchesSaleItemSkuWithoutDuplicateSales(): void
    {
        $model = new MinishopSaleModel();
        $sales = $model->findByBook(self::BOOK_ID, null, null, 'ALPHA');

        self::assertCount(1, $sales);
        self::assertSame(self::MULTI_MATCH_SALE_ID, $sales[0]['id']);
    }

    public function testFindByBookRespectsDateFiltersWhenSearchIsPresent(): void
    {
        $model = new MinishopSaleModel();

        self::assertSame(
            [self::TODAY_SALE_ID],
            array_column(
                $model->findByBook(
                    self::BOOK_ID,
                    '2026-05-23 00:00:00',
                    '2026-05-23 23:59:59',
                    'Alice'
                ),
                'id'
            )
        );
    }

    public function testFindByBookRespectsDateFiltersWhenSearchingBySaleId(): void
    {
        $model = new MinishopSaleModel();

        self::assertSame(
            [self::TODAY_SALE_ID],
            array_column(
                $model->findByBook(
                    self::BOOK_ID,
                    '2026-05-23 00:00:00',
                    '2026-05-23 23:59:59',
                    'eeeeeeee'
                ),
                'id'
            )
        );
    }

    public function testFindByBookTreatsBlankSearchAsNoSearch(): void
    {
        $model = new MinishopSaleModel();
        $withoutSearch = array_column($model->findByBook(self::BOOK_ID), 'id');
        $withBlankSearch = array_column($model->findByBook(self::BOOK_ID, null, null, '   '), 'id');

        self::assertSame($withoutSearch, $withBlankSearch);
    }

    private function seedFixtures(): void
    {
        $db = db_connect('tests');

        $db->table('users')->insert([
            'id' => 'user-1',
            'email' => 'seller@example.com',
            'status' => 'active',
            'created_at' => '2026-05-23 10:00:00',
            'updated_at' => '2026-05-23 10:00:00',
            'deleted_at' => null,
        ]);

        $db->table('books')->insert([
            'id' => self::BOOK_ID,
            'user_id' => 'user-1',
            'type_key' => 'minishop',
            'title' => 'Shop Book',
            'description' => null,
            'icon' => null,
            'color' => null,
            'settings_json' => null,
            'is_archived' => 0,
            'sort_order' => 1,
            'last_opened_at' => null,
            'created_at' => '2026-05-23 10:00:00',
            'updated_at' => '2026-05-23 10:00:00',
            'deleted_at' => null,
        ]);

        $db->table('minishop_customers')->insertBatch([
            [
                'id' => self::TODAY_CUSTOMER_ID,
                'book_id' => self::BOOK_ID,
                'created_by' => 'user-1',
                'name' => 'Alice Search',
                'phone' => null,
                'note' => null,
                'reminder_at' => null,
                'reminder_note' => null,
                'created_at' => '2026-05-23 10:00:00',
                'updated_at' => '2026-05-23 10:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => self::OTHER_CUSTOMER_ID,
                'book_id' => self::BOOK_ID,
                'created_by' => 'user-1',
                'name' => 'Bob Search',
                'phone' => null,
                'note' => null,
                'reminder_at' => null,
                'reminder_note' => null,
                'created_at' => '2026-05-23 10:00:00',
                'updated_at' => '2026-05-23 10:00:00',
                'deleted_at' => null,
            ],
        ]);

        $db->table('minishop_sales')->insertBatch([
            [
                'id' => self::TODAY_SALE_ID,
                'book_id' => self::BOOK_ID,
                'created_by' => 'user-1',
                'customer_id' => self::TODAY_CUSTOMER_ID,
                'currency_code' => 'UZS',
                'subtotal_amount' => '10000.00',
                'discount_amount' => '0.00',
                'total_amount' => '10000.00',
                'paid_amount' => '10000.00',
                'due_amount' => '0.00',
                'payment_status' => 'paid',
                'note' => null,
                'sold_at' => '2026-05-23 10:15:00',
                'created_at' => '2026-05-23 10:15:00',
                'updated_at' => '2026-05-23 10:15:00',
                'deleted_at' => null,
            ],
            [
                'id' => self::YESTERDAY_SALE_ID,
                'book_id' => self::BOOK_ID,
                'created_by' => 'user-1',
                'customer_id' => self::TODAY_CUSTOMER_ID,
                'currency_code' => 'UZS',
                'subtotal_amount' => '8000.00',
                'discount_amount' => '0.00',
                'total_amount' => '8000.00',
                'paid_amount' => '8000.00',
                'due_amount' => '0.00',
                'payment_status' => 'paid',
                'note' => null,
                'sold_at' => '2026-05-22 10:15:00',
                'created_at' => '2026-05-22 10:15:00',
                'updated_at' => '2026-05-22 10:15:00',
                'deleted_at' => null,
            ],
            [
                'id' => self::MULTI_MATCH_SALE_ID,
                'book_id' => self::BOOK_ID,
                'created_by' => 'user-1',
                'customer_id' => self::OTHER_CUSTOMER_ID,
                'currency_code' => 'UZS',
                'subtotal_amount' => '12000.00',
                'discount_amount' => '0.00',
                'total_amount' => '12000.00',
                'paid_amount' => '12000.00',
                'due_amount' => '0.00',
                'payment_status' => 'paid',
                'note' => null,
                'sold_at' => '2026-05-23 11:15:00',
                'created_at' => '2026-05-23 11:15:00',
                'updated_at' => '2026-05-23 11:15:00',
                'deleted_at' => null,
            ],
            [
                'id' => self::PRODUCT_NAME_SALE_ID,
                'book_id' => self::BOOK_ID,
                'created_by' => 'user-1',
                'customer_id' => self::OTHER_CUSTOMER_ID,
                'currency_code' => 'UZS',
                'subtotal_amount' => '9000.00',
                'discount_amount' => '0.00',
                'total_amount' => '9000.00',
                'paid_amount' => '9000.00',
                'due_amount' => '0.00',
                'payment_status' => 'paid',
                'note' => null,
                'sold_at' => '2026-05-21 10:15:00',
                'created_at' => '2026-05-21 10:15:00',
                'updated_at' => '2026-05-21 10:15:00',
                'deleted_at' => null,
            ],
        ]);

        $db->table('minishop_sale_items')->insertBatch([
            [
                'id' => 'item-1',
                'sale_id' => self::TODAY_SALE_ID,
                'product_id' => null,
                'product_name' => 'Receipt Product',
                'product_sku' => 'SKU-1',
                'quantity' => '1.000',
                'unit_price' => '10000.00',
                'line_total' => '10000.00',
            ],
            [
                'id' => 'item-2',
                'sale_id' => self::MULTI_MATCH_SALE_ID,
                'product_id' => null,
                'product_name' => 'Alpha Notebook',
                'product_sku' => 'ALPHA-100',
                'quantity' => '1.000',
                'unit_price' => '6000.00',
                'line_total' => '6000.00',
            ],
            [
                'id' => 'item-3',
                'sale_id' => self::MULTI_MATCH_SALE_ID,
                'product_id' => null,
                'product_name' => 'Alpha Marker',
                'product_sku' => 'ALPHA-200',
                'quantity' => '1.000',
                'unit_price' => '6000.00',
                'line_total' => '6000.00',
            ],
            [
                'id' => 'item-4',
                'sale_id' => self::PRODUCT_NAME_SALE_ID,
                'product_id' => null,
                'product_name' => 'Math Atlas',
                'product_sku' => 'BOOK-1',
                'quantity' => '1.000',
                'unit_price' => '9000.00',
                'line_total' => '9000.00',
            ],
        ]);
    }
}
