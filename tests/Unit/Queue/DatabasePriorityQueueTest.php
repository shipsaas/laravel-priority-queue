<?php

namespace ShipSaasPriorityQueue\Unit\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\DB;
use ShipSaasPriorityQueue\Queue\DatabasePriorityQueue;
use ShipSaasPriorityQueue\Tests\TestCase;
use ShipSaasPriorityQueue\Traits\UseJobPrioritization;

class DatabasePriorityQueueTest extends TestCase
{
    protected DatabasePriorityQueue $queue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queue = app(QueueManager::class)
            ->connection('database-priority');
    }

    public function testGetNextAvailableJobReturnsEmptyOnNoJob()
    {
        $job = $this->queue->pop();

        $this->assertNull($job);
    }

    public function testGetNextAvailableJobReturnsTheHighestPriorityJob()
    {
        DB::table('priority_jobs')->insert([
            [
                'queue' => 'default',
                'priority' => 999,
                'payload' => serialize('hehe'),
                'attempts' => 0,
                'available_at' => time(),
                'created_at' => time(),
            ],
            [
                'queue' => 'default',
                'priority' => 888,
                'payload' => serialize('meme'),
                'attempts' => 0,
                'available_at' => time(),
                'created_at' => time(),
            ],
        ]);

        $job = $this->queue->pop();

        $this->assertNotNull($job);
        $this->assertStringContainsString('hehe', $job->getRawBody());
    }

    public function testGetNextAvailableJobReturnsSamePriorityWouldPickTheOneCreatedFirst()
    {
        DB::table('priority_jobs')->insert([
            [
                'queue' => 'default',
                'priority' => 555,
                'payload' => serialize('hehe'),
                'attempts' => 0,
                'available_at' => time(),
                'created_at' => time(),
            ],
            [
                'queue' => 'default',
                'priority' => 555,
                'payload' => serialize('meme'),
                'attempts' => 0,
                'available_at' => time() - 100,
                'created_at' => time() - 100,
            ],
        ]);

        $job = $this->queue->pop();

        $this->assertNotNull($job);
        $this->assertStringContainsString('meme', $job->getRawBody());
    }

    public function testPushJobWithWeightProperty()
    {
        TestJobWeightProperty::dispatch('hello world')
            ->onConnection('database-priority');

        $this->assertDatabaseHas('priority_jobs', [
            'queue' => 'default',
            'priority' => 100,
        ]);
    }

    public function testPushJobWithWeightMethod()
    {
        TestJobWeightMethod::dispatch('hello world x2')
            ->onConnection('database-priority');

        $this->assertDatabaseHas('priority_jobs', [
            'queue' => 'default',
            'priority' => 1000,
        ]);
    }

    public function testPushJobWithoutWeightUsesDefaultWeight()
    {
        TestJobWithoutWeight::dispatch('hello world x 3')
            ->onConnection('database-priority');

        $this->assertDatabaseHas('priority_jobs', [
            'queue' => 'default',
            'priority' => DatabasePriorityQueue::DEFAULT_WEIGHT,
        ]);
    }


    public function testPushJobLaterWorks()
    {
        $now = now()->addSeconds(100);

        TestJobWithoutWeight::dispatch('hello world x 3')
            ->delay($now)
            ->onConnection('database-priority');

        $this->assertDatabaseHas('priority_jobs', [
            'queue' => 'default',
            'available_at' => $now->timestamp,
            'priority' => DatabasePriorityQueue::DEFAULT_WEIGHT,
        ]);
    }
}

class TestJobWeightProperty implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public int $jobWeight = 100;

    public function __construct(public string $hello)
    {
    }
}



class TestJobWeightMethod implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use UseJobPrioritization;

    public function __construct(public string $hello)
    {
    }

    public function getJobWeight(): int
    {
        return 1000;
    }
}

class TestJobWithoutWeight implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public function __construct(public string $hello)
    {
    }
}
