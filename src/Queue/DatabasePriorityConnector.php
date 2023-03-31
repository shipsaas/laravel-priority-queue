<?php

namespace ShipSaasPriorityQueue\Queue;

use Illuminate\Queue\Connectors\DatabaseConnector;

class DatabasePriorityConnector extends DatabaseConnector
{
    public function connect(array $config): DatabasePriorityQueue
    {
        return new DatabasePriorityQueue(
            $this->connections->connection($config['connection'] ?? null),
            $config['table'],
            $config['queue'],
            $config['retry_after'] ?? 60,
            $config['after_commit'] ?? null
        );
    }
}
