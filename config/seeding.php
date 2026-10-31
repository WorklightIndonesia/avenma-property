<?php

return [

    /*
    | Read through config() in the seeder: env() returns null once the config is
    | cached, which is exactly when the first deploy tends to seed an account.
    */

    'admin_password' => env('ADMIN_PASSWORD'),

];
