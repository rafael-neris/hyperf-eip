<?php

namespace App\Aggregator\Repositories;

use App\Aggregator\Aggregate;
use longlang\phpkafka\Consumer\ConsumeMessage;

interface AggregateRepositoryInterface
{
    public function get(string $correlation): Aggregate;
    public function save(ConsumeMessage $message): void;
}