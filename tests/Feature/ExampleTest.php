<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Test root redirects to login.
     */
    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    /**
     * Test login page loads successfully.
     */
    public function test_login_page_renders_properly(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Attention OS');
    }

    /**
     * Test Super Admin login and access dashboard.
     */
    public function test_admin_can_login_and_access_dashboard(): void
    {
        $admin = User::where('email', 'admin@attention.test')->first();
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Utama');
    }

    /**
     * Test Employee can login and access ESS dashboard.
     */
    public function test_employee_can_access_ess_dashboard(): void
    {
        $budi = User::where('email', 'staff.eng@attention.test')->first();
        $this->actingAs($budi);

        $response = $this->get('/employee/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Status Presensi Hari Ini');
    }
}
