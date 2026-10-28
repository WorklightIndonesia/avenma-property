<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_says_the_email_or_password_is_wrong(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'salah-sekali'])
            ->assertSessionHasErrors(['email' => 'Email atau password yang digunakan salah.']);
    }

    public function test_form_errors_are_indonesian_and_use_the_field_label(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_AGEN]))
            ->post('/admin/listings', [])
            ->assertSessionHasErrors([
                'title' => 'Judul wajib diisi.',
                'description' => 'Deskripsi wajib diisi.',
                'price' => 'Harga wajib diisi.',
            ]);
    }

    public function test_a_rejected_upload_explains_the_allowed_types(): void
    {
        Storage::fake('uploads');

        $this->actingAs(User::factory()->create(['role' => User::ROLE_AGEN]))
            ->post('/admin/listings', [
                'title' => 'Rumah Uji',
                'description' => 'Deskripsi uji.',
                'listing_type' => 'primary',
                'property_type' => 'rumah',
                'status' => 'dijual',
                'price' => '1000000000',
                'photos' => [UploadedFile::fake()->create('shell.php', 10, 'application/x-php')],
            ])
            ->assertSessionHasErrors([
                'photos.0' => 'Foto harus berupa berkas bertipe: image/jpeg, image/png, image/webp.',
            ]);
    }
}
