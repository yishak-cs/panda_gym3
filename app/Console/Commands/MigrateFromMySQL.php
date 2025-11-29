<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateFromMySQL extends Command
{
    protected $signature = 'db:migrate-from-mysql';
    protected $description = 'Migrate data from MySQL to SQLite';

    public function handle()
    {
        $this->info('Starting migration from MySQL to SQLite...');

        // Tables to migrate in order (respecting foreign keys)
        $tables = [
            'users',
            'membership_plans',
            'members',
            'subscriptions',
            'q_rcodes',
            'check_ins',
            'check_in_times',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
        ];

        // Configure MySQL connection
        config(['database.connections.mysql_source' => [
            'driver' => 'mysql',
            'host' => env('MYSQL_HOST', '127.0.0.1'),
            'port' => env('MYSQL_PORT', '3306'),
            'database' => env('MYSQL_DATABASE', 'pandafit'),
            'username' => env('MYSQL_USERNAME', 'root'),
            'password' => env('MYSQL_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        $this->info('Connecting to MySQL database...');
        
        try {
            DB::connection('mysql_source')->getPdo();
            $this->info('✓ MySQL connection successful');
        } catch (\Exception $e) {
            $this->error('Failed to connect to MySQL: ' . $e->getMessage());
            return 1;
        }

        // Disable foreign key checks for SQLite
        DB::statement('PRAGMA foreign_keys = OFF');

        foreach ($tables as $table) {
            if (!Schema::connection('mysql_source')->hasTable($table)) {
                $this->warn("⊘ Table '{$table}' does not exist in MySQL, skipping...");
                continue;
            }

            $this->info("Migrating table: {$table}");

            try {
                // Truncate SQLite table if it exists
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }

                // Get data from MySQL
                $data = DB::connection('mysql_source')->table($table)->get();
                $count = $data->count();

                if ($count === 0) {
                    $this->warn("  → Table is empty, skipping data migration");
                    continue;
                }

                $this->info("  → Found {$count} records");

                // Insert in chunks to avoid memory issues
                $bar = $this->output->createProgressBar($count);
                $bar->start();

                $data->chunk(100)->each(function ($chunk) use ($table, $bar) {
                    $records = $chunk->map(function ($item) {
                        return (array) $item;
                    })->toArray();
                    
                    DB::table($table)->insert($records);
                    $bar->advance(count($records));
                });

                $bar->finish();
                $this->newLine();
                $this->info("  ✓ Successfully migrated {$count} records");

            } catch (\Exception $e) {
                $this->error("  ✗ Error migrating table '{$table}': " . $e->getMessage());
                continue;
            }
        }

        // Re-enable foreign key checks
        DB::statement('PRAGMA foreign_keys = ON');

        $this->newLine();
        $this->info('Migration completed!');
        
        // Show summary
        $this->newLine();
        $this->table(
            ['Table', 'Records'],
            collect($tables)
                ->filter(fn($table) => Schema::hasTable($table))
                ->map(fn($table) => [$table, DB::table($table)->count()])
                ->toArray()
        );

        return 0;
    }
}
