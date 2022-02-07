<?php

namespace HyperfTest\Integration;

use App\Aggregator\AggregateAbstract;
use App\Aggregator\AggregatorAbstract;
use App\Aggregator\Repositories\AggregateRepositoryInterface;
use App\Aggregator\Strategies\AggregateStrategyInterface;
use Hyperf\Utils\Collection;
use HyperfTest\Fixtures\MessageAggregate;
use HyperfTest\Fixtures\MessageAggregator;
use longlang\phpkafka\Consumer\ConsumeMessage;
use Mockery;
use PHPUnit\Framework\TestCase;

class AggregatorTest extends TestCase
{
    private AggregateRepositoryInterface $aggregateRepository;
    private AggregateStrategyInterface $aggregateStrategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aggregateRepository = Mockery::mock(AggregateRepositoryInterface::class);
        $this->aggregateStrategy = Mockery::mock(AggregateStrategyInterface::class);
    }

    public function testAddFirstMessageToAggregator()
    {
        $activeAggregators = new Collection();
        $messageAggregateCollection = new Collection();

        $this->aggregateRepository->shouldReceive("save")->twice();
        $this->aggregateStrategy->shouldReceive("execute")->twice();
        $messageAggregate = new MessageAggregate(
            $messageAggregateCollection,
            $this->aggregateRepository,
            $this->aggregateStrategy
        );
        $messageAggregator = new MessageAggregator($activeAggregators, $messageAggregate);
        $message = Mockery::mock(ConsumeMessage::class);
        $message->shouldReceive('getHeaders')->andReturn(["CORRELATION_ID" => "123"]);
        $messageAggregator->onMessage($message);
        $messageAggregator->onMessage($message);

        self::assertEquals(1, $activeAggregators->count());
        self::assertEquals(2, $messageAggregateCollection->count());
    }

    public function testAddFirstMessageToAggregatorWithCompletedTask()
    {
        $activeAggregators = new Collection();
        $messageAggregateCollection = new Collection();

        $this->aggregateRepository->shouldReceive("save")->twice();
        $messageAggregate = new MessageAggregate(
            $messageAggregateCollection,
            $this->aggregateRepository,
            $this->aggregateStrategy
        );
        $this->aggregateStrategy->shouldReceive("execute")->andReturn($messageAggregate);
        $messageAggregateStrategyReturn = $messageAggregate->setCompleted();
        $this->aggregateStrategy->shouldReceive("execute")->andReturn($messageAggregateStrategyReturn);

        $messageAggregator = new MessageAggregator($activeAggregators, $messageAggregate);
        $message = Mockery::mock(ConsumeMessage::class);
        $message->shouldReceive('getHeaders')->andReturn(["CORRELATION_ID" => "123"]);
        $messageAggregator->onMessage($message);
        $messageAggregator->onMessage($message);

        self::assertEquals(1, $activeAggregators->count());
        self::assertEquals(2, $messageAggregateCollection->count());

        $messageAggregator->run();
        self::assertEquals(0, $activeAggregators->count());


    }
}