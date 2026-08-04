<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Database\Seeders\StoreSettingSeeder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class FoundationDataTest extends TestCase
{
    use DatabaseTransactions;

    public function test_foundation_tables_have_the_expected_columns(): void
    {
        $expectedColumns = [
            'users' => [
                'id',
                'name',
                'cpf',
                'email',
                'password',
                'phone',
                'email_verified_at',
                'deleted_at',
                'created_at',
                'updated_at',
            ],
            'addresses' => [
                'id',
                'user_id',
                'street',
                'number',
                'neighborhood',
                'city',
                'complement',
                'is_default',
                'created_at',
                'updated_at',
            ],
            'admins' => [
                'id',
                'name',
                'email',
                'password',
                'created_at',
                'updated_at',
            ],
            'categories' => [
                'id',
                'name',
                'slug',
                'display_order',
                'created_at',
                'updated_at',
            ],
            'products' => [
                'id',
                'category_id',
                'name',
                'description',
                'ingredients',
                'price',
                'image_url',
                'active',
                'deleted_at',
                'created_at',
                'updated_at',
            ],
            'store_settings' => [
                'id',
                'store_name',
                'cnpj',
                'physical_address',
                'opening_hours',
                'delivery_fee',
                'estimated_delivery_minutes',
                'created_at',
                'updated_at',
            ],
        ];

        foreach ($expectedColumns as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table));
            $this->assertEqualsCanonicalizing($columns, Schema::getColumnListing($table));
        }
    }

    public function test_foundation_columns_have_the_expected_mysql_types(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());

        $expectedTypes = [
            'users' => [
                'id' => 'bigint unsigned',
                'name' => 'varchar(255)',
                'cpf' => 'varchar(14)',
                'email' => 'varchar(255)',
                'password' => 'varchar(255)',
                'phone' => 'varchar(20)',
                'email_verified_at' => 'timestamp',
                'deleted_at' => 'timestamp',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
            ],
            'addresses' => [
                'id' => 'bigint unsigned',
                'user_id' => 'bigint unsigned',
                'street' => 'varchar(255)',
                'number' => 'varchar(20)',
                'neighborhood' => 'varchar(100)',
                'city' => 'varchar(100)',
                'complement' => 'varchar(255)',
                'is_default' => 'tinyint(1)',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
            ],
            'admins' => [
                'id' => 'bigint unsigned',
                'name' => 'varchar(255)',
                'email' => 'varchar(255)',
                'password' => 'varchar(255)',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
            ],
            'categories' => [
                'id' => 'bigint unsigned',
                'name' => 'varchar(100)',
                'slug' => 'varchar(100)',
                'display_order' => 'int',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
            ],
            'products' => [
                'id' => 'bigint unsigned',
                'category_id' => 'bigint unsigned',
                'name' => 'varchar(255)',
                'description' => 'text',
                'ingredients' => 'text',
                'price' => 'decimal(10,2)',
                'image_url' => 'varchar(500)',
                'active' => 'tinyint(1)',
                'deleted_at' => 'timestamp',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
            ],
            'store_settings' => [
                'id' => 'bigint unsigned',
                'store_name' => 'varchar(255)',
                'cnpj' => 'varchar(18)',
                'physical_address' => 'varchar(500)',
                'opening_hours' => 'text',
                'delivery_fee' => 'decimal(10,2)',
                'estimated_delivery_minutes' => 'int',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
            ],
        ];

        foreach ($expectedTypes as $table => $columns) {
            $actualTypes = collect($this->mysqlColumns($table))
                ->mapWithKeys(function ($column) {
                    return [$column->COLUMN_NAME => $column->COLUMN_TYPE];
                })
                ->all();

            $this->assertSame($columns, $actualTypes);
        }
    }

    public function test_foundation_columns_have_the_expected_nullability_and_defaults(): void
    {
        $nullableColumns = [
            'users' => ['phone', 'email_verified_at', 'deleted_at'],
            'addresses' => ['complement'],
            'admins' => [],
            'categories' => [],
            'products' => ['description', 'ingredients', 'image_url', 'deleted_at'],
            'store_settings' => [],
        ];

        foreach ($nullableColumns as $table => $expectedNullable) {
            $columns = $this->mysqlColumns($table);
            $actualNullable = collect($columns)
                ->filter(function ($column) {
                    return $column->IS_NULLABLE === 'YES';
                })
                ->pluck('COLUMN_NAME')
                ->values()
                ->all();

            $this->assertSame($expectedNullable, $actualNullable);
        }

        $this->assertSame('1', $this->mysqlColumn('addresses', 'is_default')->COLUMN_DEFAULT);
        $this->assertSame('0', $this->mysqlColumn('categories', 'display_order')->COLUMN_DEFAULT);
        $this->assertSame('1', $this->mysqlColumn('products', 'active')->COLUMN_DEFAULT);
    }

    public function test_unique_constraints_are_configured(): void
    {
        $this->assertUniqueIndex('users', 'users_cpf_unique', ['cpf']);
        $this->assertUniqueIndex('users', 'users_email_unique', ['email']);
        $this->assertUniqueIndex('admins', 'admins_email_unique', ['email']);
        $this->assertUniqueIndex('categories', 'categories_name_unique', ['name']);
        $this->assertUniqueIndex('categories', 'categories_slug_unique', ['slug']);
    }

    public function test_foreign_keys_are_configured(): void
    {
        $this->assertForeignKey('addresses', 'user_id', 'users', 'id');
        $this->assertForeignKey('products', 'category_id', 'categories', 'id');
    }

    public function test_models_apply_the_expected_casts(): void
    {
        $user = (new User())->forceFill(['email_verified_at' => '2026-07-30 12:00:00']);
        $address = new Address(['is_default' => 1]);
        $category = new Category(['display_order' => '2']);
        $product = new Product(['price' => '19.9', 'active' => 0]);
        $storeSetting = new StoreSetting([
            'delivery_fee' => '7',
            'estimated_delivery_minutes' => '45',
        ]);

        $this->assertInstanceOf(Carbon::class, $user->email_verified_at);
        $this->assertTrue($address->is_default);
        $this->assertSame(2, $category->display_order);
        $this->assertSame('19.90', $product->price);
        $this->assertFalse($product->active);
        $this->assertSame('7.00', $storeSetting->delivery_fee);
        $this->assertSame(45, $storeSetting->estimated_delivery_minutes);
    }

    public function test_models_define_the_expected_relationships(): void
    {
        $this->assertInstanceOf(HasMany::class, (new User())->addresses());
        $this->assertInstanceOf(BelongsTo::class, (new Address())->user());
        $this->assertInstanceOf(HasMany::class, (new Category())->products());
        $this->assertInstanceOf(BelongsTo::class, (new Product())->category());

        $this->assertTrue(method_exists(User::class, 'orders'));
        $this->assertTrue(method_exists(Product::class, 'orderItems'));
    }

    public function test_current_store_setting_returns_the_seeded_singleton(): void
    {
        StoreSetting::query()->delete();

        (new StoreSettingSeeder())->run();
        (new StoreSettingSeeder())->run();

        $storeSetting = StoreSetting::current();

        $this->assertSame(1, $storeSetting->id);
        $this->assertSame('Foodflix', $storeSetting->store_name);
        $this->assertSame('00.000.000/0001-00', $storeSetting->cnpj);
        $this->assertSame('Endereço a definir em Configurações', $storeSetting->physical_address);
        $this->assertSame('Horário a definir em Configurações', $storeSetting->opening_hours);
        $this->assertSame('0.00', $storeSetting->delivery_fee);
        $this->assertSame(30, $storeSetting->estimated_delivery_minutes);
        $this->assertSame(1, StoreSetting::query()->count());
    }

    public function test_current_store_setting_throws_a_clear_exception_when_the_singleton_is_missing(): void
    {
        StoreSetting::query()->delete();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Run "php artisan db:seed --class=StoreSettingSeeder".'
        );

        StoreSetting::current();
    }

    public function test_users_and_products_use_soft_deletes(): void
    {
        $user = User::create([
            'name' => 'Cliente Teste',
            'cpf' => '123.456.789-00',
            'email' => 'cliente-foundation@example.com',
            'password' => Hash::make('test-password'),
        ]);

        $category = Category::create([
            'name' => 'Categoria Teste',
            'slug' => 'categoria-teste',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produto Teste',
            'price' => '10.00',
        ]);

        $user->delete();
        $product->delete();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertNotNull(User::withTrashed()->findOrFail($user->id)->deleted_at);
        $this->assertNotNull(Product::withTrashed()->findOrFail($product->id)->deleted_at);
    }

    public function test_web_and_admin_guards_use_isolated_providers(): void
    {
        $password = 'shared-test-password';

        $user = User::create([
            'name' => 'Cliente Guard',
            'cpf' => '987.654.321-00',
            'email' => 'cliente-guard@example.com',
            'password' => Hash::make($password),
        ]);

        $admin = Admin::create([
            'name' => 'Admin Guard',
            'email' => 'admin-guard@example.com',
            'password' => Hash::make($password),
        ]);

        $this->assertSame('session', config('auth.guards.web.driver'));
        $this->assertSame('users', config('auth.guards.web.provider'));
        $this->assertSame(User::class, config('auth.providers.users.model'));
        $this->assertSame('session', config('auth.guards.admin.driver'));
        $this->assertSame('admins', config('auth.guards.admin.provider'));
        $this->assertSame(Admin::class, config('auth.providers.admins.model'));

        $this->assertFalse(Auth::guard('web')->attempt([
            'email' => $admin->email,
            'password' => $password,
        ]));
        $this->assertFalse(Auth::guard('admin')->attempt([
            'email' => $user->email,
            'password' => $password,
        ]));

        $this->assertTrue(Auth::guard('web')->attempt([
            'email' => $user->email,
            'password' => $password,
        ]));
        Auth::guard('web')->logout();

        $this->assertTrue(Auth::guard('admin')->attempt([
            'email' => $admin->email,
            'password' => $password,
        ]));
        Auth::guard('admin')->logout();
    }

    /**
     * @return array<int, object>
     */
    private function mysqlColumns(string $table): array
    {
        return DB::select(
            'SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
             ORDER BY ORDINAL_POSITION',
            [DB::connection()->getDatabaseName(), $table]
        );
    }

    private function mysqlColumn(string $table, string $column): object
    {
        return collect($this->mysqlColumns($table))
            ->first(function ($candidate) use ($column) {
                return $candidate->COLUMN_NAME === $column;
            });
    }

    private function assertUniqueIndex(string $table, string $index, array $columns): void
    {
        $actualColumns = collect(DB::select(
            'SELECT COLUMN_NAME
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? AND NON_UNIQUE = 0
             ORDER BY SEQ_IN_INDEX',
            [DB::connection()->getDatabaseName(), $table, $index]
        ))->pluck('COLUMN_NAME')->all();

        $this->assertSame($columns, $actualColumns);
    }

    private function assertForeignKey(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn
    ): void {
        $foreignKey = DB::selectOne(
            'SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [DB::connection()->getDatabaseName(), $table, $column]
        );

        $this->assertNotNull($foreignKey);
        $this->assertSame($referencedTable, $foreignKey->REFERENCED_TABLE_NAME);
        $this->assertSame($referencedColumn, $foreignKey->REFERENCED_COLUMN_NAME);
    }
}
