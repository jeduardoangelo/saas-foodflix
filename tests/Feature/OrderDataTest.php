<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class OrderDataTest extends TestCase
{
    public function test_order_tables_have_exactly_the_specified_columns(): void
    {
        $expected = [
            'orders' => ['id', 'order_number', 'user_id', 'status', 'delivery_method', 'delivery_address_snapshot', 'payment_method', 'payment_status', 'subtotal', 'delivery_fee', 'total', 'created_at', 'updated_at'],
            'order_items' => ['id', 'order_id', 'product_id', 'product_name_snapshot', 'unit_price_snapshot', 'quantity', 'subtotal', 'created_at', 'updated_at'],
            'payments' => ['id', 'order_id', 'gateway', 'gateway_transaction_id', 'method', 'status', 'amount', 'paid_at', 'created_at', 'updated_at'],
        ];

        foreach ($expected as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table));
            $this->assertEqualsCanonicalizing($columns, Schema::getColumnListing($table));
        }
    }

    public function test_order_columns_have_the_specified_mysql_types(): void
    {
        $expected = [
            'orders' => [
                'id' => 'bigint unsigned', 'order_number' => 'varchar(20)', 'user_id' => 'bigint unsigned',
                'status' => 'varchar(30)', 'delivery_method' => 'varchar(20)', 'delivery_address_snapshot' => 'json',
                'payment_method' => 'varchar(30)', 'payment_status' => 'varchar(20)', 'subtotal' => 'decimal(10,2)',
                'delivery_fee' => 'decimal(10,2)', 'total' => 'decimal(10,2)', 'created_at' => 'timestamp', 'updated_at' => 'timestamp',
            ],
            'order_items' => [
                'id' => 'bigint unsigned', 'order_id' => 'bigint unsigned', 'product_id' => 'bigint unsigned',
                'product_name_snapshot' => 'varchar(255)', 'unit_price_snapshot' => 'decimal(10,2)',
                'quantity' => 'int', 'subtotal' => 'decimal(10,2)', 'created_at' => 'timestamp', 'updated_at' => 'timestamp',
            ],
            'payments' => [
                'id' => 'bigint unsigned', 'order_id' => 'bigint unsigned', 'gateway' => 'varchar(50)',
                'gateway_transaction_id' => 'varchar(255)', 'method' => 'varchar(30)', 'status' => 'varchar(20)',
                'amount' => 'decimal(10,2)', 'paid_at' => 'timestamp', 'created_at' => 'timestamp', 'updated_at' => 'timestamp',
            ],
        ];

        foreach ($expected as $table => $types) {
            $actual = collect($this->mysqlColumns($table))
                ->mapWithKeys(function ($column) {
                    return [$column->COLUMN_NAME => $column->COLUMN_TYPE];
                })
                ->all();

            $this->assertSame($types, $actual);
        }
    }

    public function test_order_defaults_nullability_unique_constraints_and_foreign_keys_match_the_spec(): void
    {
        $this->assertSame('novo', $this->column('orders', 'status')->COLUMN_DEFAULT);
        $this->assertSame('pendente', $this->column('orders', 'payment_status')->COLUMN_DEFAULT);
        $this->assertSame('0.00', $this->column('orders', 'delivery_fee')->COLUMN_DEFAULT);
        $this->assertSame('pendente', $this->column('payments', 'status')->COLUMN_DEFAULT);

        $this->assertSame('YES', $this->column('orders', 'delivery_address_snapshot')->IS_NULLABLE);
        $this->assertSame('YES', $this->column('payments', 'gateway_transaction_id')->IS_NULLABLE);
        $this->assertSame('YES', $this->column('payments', 'paid_at')->IS_NULLABLE);

        $this->assertUniqueIndex('orders', 'orders_order_number_unique', ['order_number']);
        $this->assertUniqueIndex('payments', 'payments_order_id_unique', ['order_id']);

        $this->assertForeignKey('orders', 'user_id', 'users', 'id');
        $this->assertForeignKey('order_items', 'order_id', 'orders', 'id');
        $this->assertForeignKey('order_items', 'product_id', 'products', 'id');
        $this->assertForeignKey('payments', 'order_id', 'orders', 'id');
    }

    public function test_models_define_expected_casts_and_relationships(): void
    {
        $order = new Order([
            'delivery_address_snapshot' => ['street' => 'Rua A'],
            'subtotal' => '10', 'delivery_fee' => '2.5', 'total' => '12.5',
        ]);
        $item = new OrderItem(['unit_price_snapshot' => '4', 'quantity' => '2', 'subtotal' => '8']);
        $payment = new Payment(['amount' => '12.5', 'paid_at' => '2026-08-04 12:00:00']);

        $this->assertSame(['street' => 'Rua A'], $order->delivery_address_snapshot);
        $this->assertSame('10.00', $order->subtotal);
        $this->assertSame('2.50', $order->delivery_fee);
        $this->assertSame('12.50', $order->total);
        $this->assertSame('4.00', $item->unit_price_snapshot);
        $this->assertSame(2, $item->quantity);
        $this->assertSame('8.00', $item->subtotal);
        $this->assertSame('12.50', $payment->amount);
        $this->assertInstanceOf(Carbon::class, $payment->paid_at);

        $this->assertInstanceOf(BelongsTo::class, (new Order())->user());
        $this->assertInstanceOf(HasMany::class, (new Order())->items());
        $this->assertInstanceOf(HasOne::class, (new Order())->payment());
        $this->assertInstanceOf(BelongsTo::class, (new OrderItem())->order());
        $this->assertInstanceOf(BelongsTo::class, (new OrderItem())->product());
        $this->assertInstanceOf(BelongsTo::class, (new Payment())->order());
    }

    private function mysqlColumns(string $table): array
    {
        return DB::select(
            'SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
            [$table]
        );
    }

    private function column(string $table, string $column)
    {
        return collect($this->mysqlColumns($table))->firstWhere('COLUMN_NAME', $column);
    }

    private function assertUniqueIndex(string $table, string $index, array $columns): void
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);
        $actual = collect($indexes)->sortBy('Seq_in_index')->pluck('Column_name')->all();

        $this->assertNotEmpty($indexes);
        $this->assertSame(0, (int) $indexes[0]->Non_unique);
        $this->assertSame($columns, $actual);
    }

    private function assertForeignKey(string $table, string $column, string $referencedTable, string $referencedColumn): void
    {
        $foreignKey = DB::selectOne(
            'SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column]
        );

        $this->assertNotNull($foreignKey);
        $this->assertSame($referencedTable, $foreignKey->REFERENCED_TABLE_NAME);
        $this->assertSame($referencedColumn, $foreignKey->REFERENCED_COLUMN_NAME);
    }
}
