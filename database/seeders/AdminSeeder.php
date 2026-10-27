<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Fellix',
                'email' => 'fellixwl@gmail.com',
                'phone' => null,
                'role' => User::ROLE_ADMIN,
                'password' => $this->passwordFromEnv('ADMIN_PASSWORD'),
            ],
            [
                'name' => 'Budi',
                'email' => 'agenbudi@gmail.com',
                'phone' => '089630999996',
                'role' => User::ROLE_AGEN,
                'password' => $this->passwordFromEnv('AGEN_PASSWORD'),
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
    private function passwordFromEnv(string $key): string
    {
        $password = (string) env($key);

        if ($password === '') {
            throw new RuntimeException("{$key} belum diisi di .env. Seeder berhenti.");
        }

        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', Password::defaults()]]
        );

        if ($validator->fails()) {
            throw new RuntimeException(
                "{$key} di .env tidak memenuhi aturan password: ".$validator->errors()->first('password')
            );
        }

        return $password;
    }
}
