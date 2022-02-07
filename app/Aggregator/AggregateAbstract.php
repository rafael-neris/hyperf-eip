<?php

namespace App\Aggregator;

use App\Aggregator\Repositories\AggregateRepositoryInterface;
use App\Aggregator\Strategies\AggregateStrategyInterface;
use Hyperf\Utils\Collection;
use longlang\phpkafka\Consumer\ConsumeMessage;


class AggregateAbstract implements Aggregate
{
    protected const PROP_CORRELATION = 'CORRELATION_ID';
    protected bool $isCompleted = false;

    public function __construct(
        protected Collection $collection,
        protected AggregateRepositoryInterface $aggregateRepository,
        protected AggregateStrategyInterface $strategy
    ){}

    public function addMessage(ConsumeMessage $message): void
    {
        $this->collection->push($message);
        $this->aggregateRepository->save($message);
        $this->strategy->execute($this);
    }

    public function isComplete(): bool
    {
        return $this->isCompleted;
    }

    public function setCompleted(): Aggregate
    {
        $this->isCompleted = true;
        return $this;
    }

    public function getResultMessage(): object
    {
        // TODO: Implement getResultMessage() method.
    }

    public function setRepository(AggregateRepositoryInterface $aggregateRepository): Aggregate
    {
        $this->aggregateRepository = $aggregateRepository;
        return $this;
    }
}