<?php

namespace App\Services\StoreSetting;

use App\Models\StoreSetting;

class StoreSettingService
{
    public function current(): StoreSetting
    {
        return StoreSetting::current();
    }

    public function update(array $attributes): StoreSetting
    {
        $storeSetting = StoreSetting::current();
        $storeSetting->update($attributes);

        return $storeSetting->refresh();
    }
}
