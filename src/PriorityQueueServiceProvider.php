<?php

namespace ShipSaasPriorityQueue;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use ShipSaasPriorityQueue\Queue\DatabasePriorityConnector;

class PriorityQueueServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Database/Migrations/' => database_path('migrations'),
            ], 'priority-queue-migrations');
        }
    }

    public function register(): void
    {
        $this->app->afterResolving('queue', function (QueueManager $manager): void {
            $manager->addConnector(
                'database-priority',
                fn () => new DatabasePriorityConnector($this->app['db'])
            );
        });
    }
}
