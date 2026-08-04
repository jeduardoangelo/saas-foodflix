<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\StoreSetting;
use App\Models\User;
use Database\Seeders\StoreSettingSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreSettingsManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_store_settings_routes_require_an_authenticated_admin(): void
    {
        $this->get('/admin/settings')->assertRedirect('/admin/login');
        $this->put('/admin/settings')->assertRedirect('/admin/login');
    }

    public function test_settings_screen_displays_seeded_placeholders_in_the_three_sections(): void
    {
        $this->resetToSeededSettings();
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get('/admin/settings');

        $response->assertOk();
        $response->assertSee('Configurações da Loja');
        $response->assertSeeInOrder([
            'Informações da Empresa',
            'Horário de Funcionamento',
            'Configurações de Entrega',
        ]);
        $response->assertSee('value="Foodflix"', false);
        $response->assertSee('value="00.000.000/0001-00"', false);
        $response->assertSee('value="Endereço a definir em Configurações"', false);
        $response->assertSee('Horário a definir em Configurações');
        $response->assertSee('Esta instalação ainda possui dados de exemplo.');
    }

    public function test_admin_updates_only_the_singleton_store_setting(): void
    {
        $this->resetToSeededSettings();
        $admin = $this->createAdmin();
        $createdAt = StoreSetting::current()->created_at;

        $response = $this->actingAs($admin, 'admin')->put('/admin/settings', [
            'store_name' => 'Foodflix',
            'cnpj' => '12.345.678/0001-90',
            'physical_address' => 'Rua Principal, 100 - Centro',
            'opening_hours' => 'Segunda a Domingo: 11h às 23h',
            'delivery_fee' => '8.50',
            'estimated_delivery_minutes' => 45,
        ]);

        $response->assertRedirect('/admin/settings');
        $response->assertSessionHas('success', 'Configurações da loja atualizadas com sucesso.');

        $settings = StoreSetting::current();
        $this->assertSame(1, $settings->id);
        $this->assertSame('Foodflix', $settings->store_name);
        $this->assertSame('12.345.678/0001-90', $settings->cnpj);
        $this->assertSame('Rua Principal, 100 - Centro', $settings->physical_address);
        $this->assertSame('Segunda a Domingo: 11h às 23h', $settings->opening_hours);
        $this->assertSame('8.50', $settings->delivery_fee);
        $this->assertSame(45, $settings->estimated_delivery_minutes);
        $this->assertTrue($createdAt->equalTo($settings->created_at));
        $this->assertSame(1, StoreSetting::query()->count());
    }

    public function test_required_company_fields_cannot_be_saved_empty(): void
    {
        $this->resetToSeededSettings();
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->from('/admin/settings')
            ->put('/admin/settings', [
                'store_name' => '',
                'cnpj' => '',
                'physical_address' => '',
                'opening_hours' => 'Todos os dias: 11h às 23h',
                'delivery_fee' => '0.00',
                'estimated_delivery_minutes' => 30,
            ]);

        $response->assertRedirect('/admin/settings');
        $response->assertSessionHasErrors(['store_name', 'cnpj', 'physical_address']);
        $this->assertSame('00.000.000/0001-00', StoreSetting::current()->cnpj);
        $this->assertSame(1, StoreSetting::query()->count());
    }

    public function test_operational_fields_are_validated_against_the_database_constraints(): void
    {
        $this->resetToSeededSettings();
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->from('/admin/settings')
            ->put('/admin/settings', [
                'store_name' => 'Foodflix',
                'cnpj' => '12.345.678/0001-90',
                'physical_address' => 'Rua Principal, 100',
                'opening_hours' => '',
                'delivery_fee' => '-1',
                'estimated_delivery_minutes' => 0,
            ]);

        $response->assertRedirect('/admin/settings');
        $response->assertSessionHasErrors([
            'opening_hours',
            'delivery_fee',
            'estimated_delivery_minutes',
        ]);
        $this->assertSame(1, StoreSetting::query()->count());
    }

    public function test_authenticated_client_cannot_access_store_settings(): void
    {
        $this->resetToSeededSettings();
        $user = User::create([
            'name' => 'Cliente sem configurações',
            'cpf' => '862.883.667-57',
            'email' => 'client-settings@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user, 'web')
            ->get('/admin/settings')
            ->assertRedirect('/admin/login');
    }

    private function resetToSeededSettings(): void
    {
        StoreSetting::query()->delete();
        (new StoreSettingSeeder())->run();
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Administrador de Configurações',
            'email' => uniqid('admin-settings-', true).'@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
