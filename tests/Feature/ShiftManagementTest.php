<?php

namespace Tests\Feature;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShiftManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
        ]);

        $role = Role::create(['name' => 'Super Admin']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);
    }

    public function test_admin_can_view_shifts_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.shifts.index'));
        $response->assertSuccessful();
        $response->assertSee('Master Shift Kerja');
    }

    public function test_admin_can_view_create_shift_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.shifts.create'));
        $response->assertSuccessful();
        $response->assertSee('Tambah Shift Kerja');
    }

    public function test_admin_can_store_new_shift(): void
    {
        $payload = [
            'name' => 'Shift Malam (22:00 - 06:00)',
            'start_time' => '22:00',
            'end_time' => '06:00',
            'grace_period_minutes' => 15,
            'is_overnight' => 1,
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.shifts.store'), $payload);

        $response->assertRedirect(route('admin.shifts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shifts', [
            'name' => 'Shift Malam (22:00 - 06:00)',
            'is_overnight' => true,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_shift(): void
    {
        $shift = Shift::create([
            'name' => 'Shift Siang',
            'start_time' => '13:00',
            'end_time' => '21:00',
            'grace_period_minutes' => 10,
            'is_overnight' => 0,
            'is_active' => 1,
        ]);

        $payload = [
            'name' => 'Shift Siang Updated',
            'start_time' => '13:30',
            'end_time' => '21:30',
            'grace_period_minutes' => 20,
            'is_overnight' => 0,
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.shifts.update', $shift->id), $payload);

        $response->assertRedirect(route('admin.shifts.index'));
        $this->assertDatabaseHas('shifts', [
            'id' => $shift->id,
            'name' => 'Shift Siang Updated',
            'grace_period_minutes' => 20,
        ]);
    }

    public function test_admin_can_delete_shift(): void
    {
        $shift = Shift::create([
            'name' => 'Shift Temporer',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'grace_period_minutes' => 15,
            'is_overnight' => 0,
            'is_active' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.shifts.destroy', $shift->id));

        $response->assertRedirect(route('admin.shifts.index'));
        $this->assertDatabaseMissing('shifts', ['id' => $shift->id]);
    }
}
