<?php

namespace ShipSaasPriorityQueue\Tests\Feature;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ShipSaasPriorityQueue\Tests\TestCase;
use ShipSaasPriorityQueue\Traits\UseJobPrioritization;

class PriorityQueueWorkCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('logs', function (Blueprint $table) {
            $table->uuid('id')->unique();
            $table->string('text');
        });
    }

    public function testQueueWorkCommandWorksNormallyForDatabasePriorityQueueDriver()
    {
        TestJob::dispatch('Hello Seth Tran', $firstId = fake()->uuid());
        TestJob::dispatch('Hello Seth Phat', $secondId = fake()->uuid());

        $this->artisan('queue:work database-priority --max-jobs=1');

        $this->assertDatabaseHas('logs', [
            'id' => $firstId,
            'text' => 'Hello Seth Tran',
        ]);

        $this->artisan('queue:work database-priority --max-jobs=1');

        $this->assertDatabaseHas('logs', [
            'id' => $secondId,
            'text' => 'Hello Seth Phat',
        ]);
    }

    public function testQueueWorkCommandHandlesTheHighestWeightJobFirst()
    {
        TestJob::dispatch('Hello Seth Tran', $firstId = fake()->uuid())
            ->setWeight(400);
        TestJob::dispatch('Hello Seth Phat', $secondId = fake()->uuid());

        $this->artisan('queue:work database-priority --max-jobs=1');

        // second job will be picked
        $this->assertDatabaseHas('logs', [
            'id' => $secondId,
            'text' => 'Hello Seth Phat',
        ]);

        $this->artisan('queue:work database-priority --max-jobs=1');

        $this->assertDatabaseHas('logs', [
            'id' => $firstId,
            'text' => 'Hello Seth Tran',
        ]);
    }
}

class TestJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use UseJobPrioritization;

    public int $customWeight;

    public function __construct(public string $hello, public string $id)
    {
        $this->onConnection('database-priority');
    }

    public function handle(): void
    {
        DB::table('logs')->insert([
            'id' => $this->id,
            'text' => $this->hello,
        ]);
    }

    public function setWeight(int $weight): self
    {
        $this->customWeight = $weight;

        return $this;
    }

    public function getJobWeight(): int
    {
        return $this->customWeight ?? 1000;
    }
}
