<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStoreSettingRequest;
use App\Services\StoreSetting\StoreSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StoreSettingController extends Controller
{
    private $settings;

    public function __construct(StoreSettingService $settings)
    {
        $this->settings = $settings;
    }

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => $this->settings->current(),
        ]);
    }

    public function update(UpdateStoreSettingRequest $request): RedirectResponse
    {
        $this->settings->update($request->validated());

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Configurações da loja atualizadas com sucesso.');
    }
}
