<?php

namespace App\Aggregator;

use longlang\phpkafka\Consumer\ConsumeMessage;

interface Aggregator
{
    public function run(): void;
    public function onMessage(ConsumeMessage $message): void;
}