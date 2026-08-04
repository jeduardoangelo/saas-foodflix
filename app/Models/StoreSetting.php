<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class StoreSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_name',
        'cnpj',
        'physical_address',
        'opening_hours',
        'delivery_fee',
        'estimated_delivery_minutes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'estimated_delivery_minutes' => 'integer',
    ];

    public static function current(): self
    {
        $storeSetting = static::find(1);

        if ($storeSetting === null) {
            throw new RuntimeException(
                'Store settings are not initialized. Run "php artisan db:seed --class=StoreSettingSeeder".'
            );
        }

        return $storeSetting;
    }
}
