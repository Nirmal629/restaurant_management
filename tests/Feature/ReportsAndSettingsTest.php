<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_screen_data_and_export_are_available(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Reports', 'View'],
            ['Reports', 'Export'],
        ]);

        $this->get('/reports')->assertOk()->assertSee('window.reportsModule', false);

        $this->getJson('/reports/data?report=Daily%20Sales')
            ->assertOk()
            ->assertJsonStructure([
                'categories',
                'dailySales' => ['kpis', 'hourly', 'transactions'],
                'menuProfitability',
                'revenueSummary',
                'waiterSales',
                'generic' => ['rows', 'totals'],
            ]);

        $this->get('/reports/export/csv?report=Daily%20Sales')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_settings_can_be_saved_and_reset(): void
    {
        $this->actingAsEmployeeWithPermissions([
            ['Settings', 'View'],
            ['Settings', 'Edit'],
        ]);

        $this->get('/settings')->assertOk()->assertSee('window.settingsModule', false);

        $this->putJson('/settings/general', [
            'values' => ['name' => 'Royal Bengal Test Kitchen'],
        ])->assertOk()
            ->assertJsonPath('settings.general.name', 'Royal Bengal Test Kitchen');

        $this->assertDatabaseHas('app_settings', ['section' => 'general']);
        $this->assertSame('Royal Bengal Test Kitchen', AppSetting::where('section', 'general')->first()->values['name']);

        $this->deleteJson('/settings/general')
            ->assertOk()
            ->assertJsonPath('settings.general.name', 'Royal Bengal Restaurant');

        $this->assertDatabaseMissing('app_settings', ['section' => 'general']);
    }
}
