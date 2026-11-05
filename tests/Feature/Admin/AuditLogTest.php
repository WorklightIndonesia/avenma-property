<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Developer;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_audit_page(): void
    {
        $this->get('/admin/audit')->assertRedirect('/login');
    }

    public function test_creating_a_model_writes_a_row(): void
    {
        $developer = Developer::create([
            'name' => 'Beverly Group',
            'slug' => 'beverly-group',
            'whatsapp' => '081234567890',
            'email' => 'halo@beverly.test',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => Developer::class,
            'auditable_id' => $developer->id,
        ]);
    }

    public function test_updating_a_model_keeps_the_old_and_new_value(): void
    {
        $setting = SiteSetting::create(['whatsapp' => '081299900387']);
        $setting->update(['whatsapp' => '081299900388']);

        $log = AuditLog::query()
            ->where('event', 'updated')
            ->where('auditable_type', SiteSetting::class)
            ->firstOrFail();

        $this->assertSame('081299900387', $log->changes['old']['whatsapp']);
        $this->assertSame('081299900388', $log->changes['new']['whatsapp']);
    }

    public function test_a_password_is_never_stored(): void
    {
        $user = User::factory()->create();

        $log = AuditLog::query()
            ->where('event', 'created')
            ->where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->firstOrFail();

        $this->assertArrayNotHasKey('password', $log->changes['new']);
        $this->assertArrayNotHasKey('remember_token', $log->changes['new']);
    }

    public function test_a_login_writes_a_row(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'login',
            'user_id' => $user->id,
        ]);
    }

    public function test_the_audit_page_lists_the_entries(): void
    {
        Developer::create([
            'name' => 'Beverly Group',
            'slug' => 'beverly-group',
            'whatsapp' => '081234567890',
            'email' => 'halo@beverly.test',
        ]);

        $this->actingAs(User::factory()->create())
            ->get('/admin/audit')
            ->assertOk()
            ->assertSee('created')
            ->assertSee('Developer')
            ->assertSee('Beverly Group');
    }
}
