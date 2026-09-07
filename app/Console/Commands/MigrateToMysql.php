<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateToMysql extends Command
{
    protected $signature = 'db:sqlite-to-mysql
                            {--mysql-host=127.0.0.1 : MySQL host}
                            {--mysql-port=3306 : MySQL port}
                            {--mysql-db= : MySQL database name}
                            {--mysql-user= : MySQL username}
                            {--mysql-pass= : MySQL password}';

    protected $description = 'Migrate all data from SQLite to MySQL';

    public function handle(): int
    {
        // Setup MySQL connection dinamis
        $mysqlDb   = $this->option('mysql-db')   ?: $this->ask('MySQL database name');
        $mysqlUser = $this->option('mysql-user')  ?: $this->ask('MySQL username');
        $mysqlPass = $this->option('mysql-pass')  ?: $this->secret('MySQL password');
        $mysqlHost = $this->option('mysql-host');
        $mysqlPort = $this->option('mysql-port');

        config(['database.connections.mysql_target' => [
            'driver'    => 'mysql',
            'host'      => $mysqlHost,
            'port'      => $mysqlPort,
            'database'  => $mysqlDb,
            'username'  => $mysqlUser,
            'password'  => $mysqlPass,
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ]]);

        // Test koneksi MySQL
        try {
            DB::connection('mysql_target')->getPdo();
            $this->info("✅ MySQL connected: {$mysqlHost}/{$mysqlDb}");
        } catch (\Exception $e) {
            $this->error("❌ MySQL connection failed: " . $e->getMessage());
            return 1;
        }

        // Jalankan migration di MySQL
        $this->info('🗄️  Running migrations on MySQL...');
        $this->call('migrate', [
            '--database' => 'mysql_target',
            '--force'    => true,
        ]);

        // Ambil semua tabel dari SQLite (kecuali migrations)
        $tables = DB::connection('sqlite')
            ->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT IN ('migrations', 'sqlite_sequence')");

        $tableNames = array_map(fn($t) => $t->name, $tables);

        $this->info('📋 Tables found: ' . implode(', ', $tableNames));

        // Copy data per tabel
        foreach ($tableNames as $table) {
            $rows = DB::connection('sqlite')->table($table)->get();

            if ($rows->isEmpty()) {
                $this->line("  ⏭️  {$table}: empty, skip");
                continue;
            }

            // Disable foreign key checks sementara
            DB::connection('mysql_target')->statement('SET FOREIGN_KEY_CHECKS=0');

            // Truncate dulu agar tidak duplikat
            DB::connection('mysql_target')->table($table)->truncate();

            // Insert dalam batch 500
            $chunks = $rows->map(fn($r) => (array) $r)->chunk(500);
            foreach ($chunks as $chunk) {
                DB::connection('mysql_target')->table($table)->insert($chunk->toArray());
            }

            DB::connection('mysql_target')->statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info("  ✅ {$table}: {$rows->count()} rows copied");
        }

        $this->info('🎉 Migration complete!');

        return 0;
    }
}
