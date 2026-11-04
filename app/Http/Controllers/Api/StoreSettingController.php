<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\StoreSettingResource;
use App\Services\StoreSetting\StoreSettingService;

class StoreSettingController extends Controller
{
    private $settings;

    public function __construct(StoreSettingService $settings)
    {
        $this->settings = $settings;
    }

    public function show()
    {
        return new StoreSettingResource($this->settings->current());
    }
}
