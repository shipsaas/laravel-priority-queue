<?php

namespace ShipSaasPriorityQueue\Tests;

use Dotenv\Dotenv;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Env;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ShipSaasPriorityQueue\PriorityQueueServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use WithFaker;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            PriorityQueueServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Load the .env.testing file
        Dotenv::create(
            Env::getRepository(),
            __DIR__ . '/../',
            '.env.testing',
        )->load();

        // setup configs
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
        $app['config']->set('queue.connections.database-priority', [
            'driver' => 'database-priority',
            'connection' => 'mysql',
            'table' => 'priority_jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ]);

        $app['db']->connection('mysql')
            ->getSchemaBuilder()
            ->dropAllTables();

        $migrationFiles = [
            __DIR__ . '/../src/Database/Migrations/2022_03_31_17_00_00_create_priority_jobs_table.php',
        ];

        foreach ($migrationFiles as $migrationFile) {
            $migrateInstance = include $migrationFile;
            $migrateInstance->up();
        }
    }
}
