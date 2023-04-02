<?php

namespace ShipSaasPriorityQueue\Tests\Unit\Queue;

use ShipSaasPriorityQueue\Queue\DatabasePriorityConnector;
use ShipSaasPriorityQueue\Tests\TestCase;

class DatabasePriorityConnectorTest extends TestCase
{
    public function testConnectorCanConnectToDatabaseQueue()
    {
        $databasePriorityConnector = new DatabasePriorityConnector(app('db'));
        $queue = $databasePriorityConnector->connect([
            'connection' => 'mysql',
            'table' => 'priority_jobs',
            'queue' => 'default',
        ]);

        $this->assertNotNull($queue);
    }
}
