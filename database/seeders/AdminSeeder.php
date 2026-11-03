<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AdminSeeder extends Seeder
{
    /**
     * The launch account, and the author BlogPostSeeder pins the articles to.
     */
    public const EMAIL = 'admin.avenmaproperti@apfianigroup.com';

    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Avenma Properti',
                'email' => self::EMAIL,
                'phone' => '081299900387',
                'role' => User::ROLE_ADMIN,
                'password' => $this->password(),
            ],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                $account
            );
        }
    }

    /**
     * Seeded passwords skip the account form, so enforce the same rule here.
     */
    private function password(): string
    {
        $password = (string) config('seeding.admin_password');

        if ($password === '') {
            throw new RuntimeException('ADMIN_PASSWORD belum diisi di .env. Seeder berhenti.');
        }

        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', Password::defaults()]]
        );

        if ($validator->fails()) {
            throw new RuntimeException(
                'ADMIN_PASSWORD di .env tidak memenuhi aturan password: '.$validator->errors()->first('password')
            );
        }

        return $password;
    }
}
