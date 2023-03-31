<?php

namespace ShipSaasPriorityQueue\Traits;

use ShipSaasPriorityQueue\Queue\DatabasePriorityQueue;

trait UseJobPrioritization
{
    /**
     * Desire job weight, you can calculate (based on any logic) or hardcoded the number
     *
     * @return int
     */
    public function getJobWeight(): int
    {
        return DatabasePriorityQueue::DEFAULT_WEIGHT;
    }
}
