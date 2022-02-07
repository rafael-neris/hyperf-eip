<?php

namespace App\Aggregator;

use longlang\phpkafka\Consumer\ConsumeMessage;

interface Aggregate
{
    public function addMessage(ConsumeMessage $message): void;
    public function isComplete(): bool;
    public function getResultMessage(): object;
}