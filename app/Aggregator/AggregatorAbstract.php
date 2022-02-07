<?php

namespace App\Aggregator;

use Hyperf\Kafka\AbstractConsumer;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Coroutine;
use longlang\phpkafka\Consumer\ConsumeMessage;

abstract class AggregatorAbstract extends AbstractConsumer implements Aggregator
{
    protected const PROP_CORRELATION = 'CORRELATION_ID';


    public function __construct(
        protected Collection $activeAggregates,
        protected Aggregate $aggregate
    ){}

    public function run(): void
    {
        Coroutine::create(
            function () {
                $filtered = $this->activeAggregates->filter(
                    function (Aggregate $aggregate, $correlation) {
                        return $aggregate->isComplete();
                    }
                );

                $filtered->each(function (Aggregate $aggregate, $correlation) {
                    $this->activeAggregates->pull((string)$correlation);
                });
            }
        );
    }

    public function onMessage(ConsumeMessage $message): void
    {
        Coroutine::create(
            function () use ($message) {
                try {
                    $correlation = ($message->getHeaders())[self::PROP_CORRELATION];
                    $aggregate = $this->activeAggregates->get($correlation);

                    if (is_null($aggregate)) {
                        $aggregate = clone $this->aggregate;
                    }

                    $aggregate->addMessage($message);
                    $this->activeAggregates->put($correlation, $aggregate);
                } catch (\Exception $exception) {

                }
            }
        );
    }

    public function consume(ConsumeMessage $message)
    {
        $this->onMessage($message);
    }
}