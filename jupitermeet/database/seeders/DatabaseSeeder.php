<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::unprepared(file_get_contents(public_path() . '/sources/currencies.sql'));
        $this->call(UserSeeder::class);
        $this->call(ContentSeeder::class);
        $this->call(GlobalConfigSeeder::class);
    }
}
