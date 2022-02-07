<?php

namespace App\Aggregator\Strategies;

use App\Aggregator\Aggregate;

interface AggregateStrategyInterface
{
    public function execute(Aggregate $aggregate): void;
}