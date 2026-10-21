<?php

namespace Tests\Feature;

use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'username' => 'admin',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'password' => 'Password123',
        ]);

        Trainee::create([
            'reg_no' => '20260001',
            'electronic_reg_no' => 'E000001',
            'last_name' => 'بن عمر',
            'first_name' => 'أمينة',
            'eligible' => true,
            'file_accepted' => true,
        ]);
    }

    public function test_trainees_can_be_exported_to_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('export.trainees', ['format' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('بن عمر', $response->streamedContent());
    }

    public function test_trainees_can_be_exported_to_xlsx(): void
    {
        $response = $this->actingAs($this->admin)->get(route('export.trainees', ['format' => 'xlsx']));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('content-type'));
        $this->assertNotEmpty($response->streamedContent());
    }

    public function test_payments_can_be_exported(): void
    {
        $this->actingAs($this->admin)
            ->get(route('export.payments', ['format' => 'csv']))
            ->assertOk();
    }

    public function test_unsupported_export_format_returns_not_found(): void
    {
        $this->actingAs($this->admin)
            ->get(route('export.trainees', ['format' => 'pdf']))
            ->assertNotFound();
    }

    public function test_export_respects_filters(): void
    {
        Trainee::create([
            'reg_no' => '20260002',
            'electronic_reg_no' => 'E000002',
            'last_name' => 'زيتوني',
            'first_name' => 'محمد',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('export.trainees', ['format' => 'csv', 'q' => 'زيتوني']));

        $body = $response->streamedContent();

        $this->assertStringContainsString('زيتوني', $body);
        $this->assertStringNotContainsString('بن عمر', $body);
    }

    public function test_canteen_role_cannot_export(): void
    {
        $canteen = User::factory()->create([
            'username' => 'canteen',
            'role' => User::ROLE_CANTEEN,
            'is_active' => true,
            'password' => 'Password123',
        ]);

        $this->actingAs($canteen)
            ->get(route('export.trainees', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_barcode_and_qr_sources_are_available_to_active_users(): void
    {
        $this->actingAs($this->admin)->get(route('barcode.code128', ['code' => 'E000001']))->assertOk();
        $this->actingAs($this->admin)->get(route('barcode.qr', ['code' => 'E000001']))->assertOk();
    }
}
