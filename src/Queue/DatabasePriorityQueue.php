<?php

namespace ShipSaasPriorityQueue\Queue;

use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Jobs\DatabaseJobRecord;

class DatabasePriorityQueue extends DatabaseQueue
{
    public const DEFAULT_WEIGHT = 500;

    protected function getNextAvailableJob($queue): ?DatabaseJobRecord
    {
        $job = $this->database->table($this->table)
            ->lock($this->getLockForPopping())
            ->where('queue', $this->getQueue($queue))
            ->where(function ($query) {
                $this->isAvailable($query);
                $this->isReservedButExpired($query);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at')
            ->first();

        return $job
            ? new DatabaseJobRecord((object) $job)
            : null;
    }

    public function push($job, $data = '', $queue = null): mixed
    {
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $this->getQueue($queue), $data),
            $queue,
            null,
            function ($payload, $queue) use ($job) {
                return $this->pushToDatabase(
                    $queue,
                    $payload,
                    weight: $this->getJobWeight($job)
                );
            }
        );
    }

    public function later($delay, $job, $data = '', $queue = null): mixed
    {
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $this->getQueue($queue), $data),
            $queue,
            $delay,
            function ($payload, $queue, $delay) use ($job) {
                return $this->pushToDatabase(
                    $queue,
                    $payload,
                    $delay,
                    weight: $this->getJobWeight($job)
                );
            }
        );
    }

    protected function pushToDatabase(
        $queue,
        $payload,
        $delay = 0,
        $attempts = 0,
        $weight = self::DEFAULT_WEIGHT
    ): int {
        return $this->database->table($this->table)->insertGetId($this->buildDatabaseRecord(
            $this->getQueue($queue),
            $payload,
            $this->availableAt($delay),
            $attempts,
            $weight
        ));
    }

    protected function buildDatabaseRecord(
        $queue,
        $payload,
        $availableAt,
        $attempts = 0,
        $weight = self::DEFAULT_WEIGHT
    ): array {
        return [
            ...parent::buildDatabaseRecord($queue, $payload, $availableAt, $attempts),
            'priority' => $weight,
        ];
    }

    protected function getJobWeight($job): int
    {
        if (is_object($job) && property_exists($job, 'jobWeight')) {
            return (int) $job->jobWeight;
        }

        if (is_object($job) && method_exists($job, 'getJobWeight')) {
            return $job->getJobWeight();
        }

        return self::DEFAULT_WEIGHT;
    }
}
