<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedOnce extends Command
{
    protected $signature = 'app:seed-once';
    protected $description = 'Seed the database only if it has not been seeded yet (safe for Railway restarts)';

    public function handle(): int
    {
        try {
            $count = DB::table('schools')->count();
        } catch (\Exception $e) {
            $this->info('Schools table not ready, skipping seed.');
            return 0;
        }

        if ($count > 0) {
            $this->info('Database already seeded (' . $count . ' schools found). Skipping.');
            return 0;
        }

        $this->info('Empty database detected. Running seeders...');
        $this->call('db:seed', ['--force' => true]);

        return 0;
    }
}
