<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    /**
     * Seed the singleton store settings row.
     *
     * @return void
     */
    public function run()
    {
        StoreSetting::unguarded(function () {
            StoreSetting::firstOrCreate(
                ['id' => 1],
                [
                    'store_name' => 'Foodflix',
                    // Temporary placeholders: replace these with the store's real data in Configurações before production.
                    'cnpj' => '00.000.000/0001-00',
                    'physical_address' => 'Endereço a definir em Configurações',
                    'opening_hours' => 'Horário a definir em Configurações',
                    'delivery_fee' => '0.00',
                    'estimated_delivery_minutes' => 30,
                ]
            );
        });
    }
}
