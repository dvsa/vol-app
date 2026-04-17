<?php

declare(strict_types=1);

/**
 * Queue Processor Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Cli\Service\Queue;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\Exception\ORMException;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Query\Queue\NextItem as NextQueueItemQry;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\MessageConsumerInterface;
use Dvsa\Olcs\Cli\Service\Queue\MessageConsumerManager;
use Dvsa\Olcs\Cli\Service\Queue\QueueProcessor;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;

/**
 * Queue Processor Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class QueueProcessorTest extends MockeryTestCase
{
    protected $sut;

    private $mockQueryHandlerManager;

    private $mockMsm;

    public function setUp(): void
    {
        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        $this->mockQueryHandlerManager = m::mock(QueryHandlerManager::class);

        $this->mockMsm = m::mock(MessageConsumerManager::class)->makePartial();

        $this->sut = new QueueProcessor($this->mockQueryHandlerManager, $this->mockMsm);
    }

    public function testProcessNextItemWithoutItem(): void
    {
        $includeTypes = ['foo'];
        $excludeTypes = ['bar'];

        // Expectations
        $this->expectQuery(
            NextQueueItemQry::class,
            ['includeTypes' => $includeTypes, 'excludeTypes' => $excludeTypes],
            null
        );

        // Assertions
        $this->assertNull($this->sut->processNextItem($includeTypes, $excludeTypes));
    }

    public function testProcessNextItem(): void
    {
        $includeTypes = ['foo'];
        $excludeTypes = ['bar'];

        $type = new RefData($includeTypes[0]);
        $item = new QueueEntity($type);

        // Mocks
        $mockConsumer = m::mock(MessageConsumerInterface::class);
        $this->mockMsm->setService('foo', $mockConsumer);

        // Expectations
        $this->expectQuery(
            NextQueueItemQry::class,
            ['includeTypes' => $includeTypes, 'excludeTypes' => $excludeTypes],
            $item
        );

        $mockConsumer->shouldReceive('processMessage')
            ->once()
            ->with($item)
            ->andReturn('foo');

        // Assertions
        $this->assertEquals('foo', $this->sut->processNextItem($includeTypes, $excludeTypes));
    }

    public function testProcessMessageHandlesException(): void
    {
        $includeTypes = ['foo'];
        $excludeTypes = ['bar'];

        $type = new RefData($includeTypes[0]);
        $item = new QueueEntity($type);

        // Mocks
        $mockConsumer = m::mock(MessageConsumerInterface::class);
        $this->mockMsm->setService('foo', $mockConsumer);

        // Expectations
        $this->expectQuery(
            NextQueueItemQry::class,
            ['includeTypes' => $includeTypes, 'excludeTypes' => $excludeTypes],
            $item
        );

        $exceptionMessage = 'something went wrong';
        $mockConsumer->shouldReceive('processMessage')
            ->once()
            ->with($item)
            ->andThrow(new \Exception($exceptionMessage))
            ->shouldReceive('failed')
            ->once()
            ->with($item, $exceptionMessage)
            ->andReturn('error message');

        // Assertions
        $this->assertEquals('error message', $this->sut->processNextItem($includeTypes, $excludeTypes));
    }

    public function testProcessMessageHandlesOrmException(): void
    {
        $includeTypes = ['foo'];
        $excludeTypes = ['bar'];

        $type = new RefData($includeTypes[0]);
        $item = new QueueEntity($type);

        // Mocks
        $mockConsumer = m::mock(MessageConsumerInterface::class);
        $this->mockMsm->setService('foo', $mockConsumer);

        // Expectations
        $this->expectQuery(
            NextQueueItemQry::class,
            ['includeTypes' => $includeTypes, 'excludeTypes' => $excludeTypes],
            $item
        );

        $exceptionMessage = 'something went wrong';
        $mockConsumer->shouldReceive('processMessage')
            ->once()
            ->with($item)
            ->andThrow(new ORMException($exceptionMessage));

        $this->expectException(ORMException::class);

        $this->sut->processNextItem($includeTypes, $excludeTypes);
    }

    public function testProcessMessageHandlesDbalException(): void
    {
        $includeTypes = ['foo'];
        $excludeTypes = ['bar'];

        $type = new RefData($includeTypes[0]);
        $item = new QueueEntity($type);

        // Mocks
        $mockConsumer = m::mock(MessageConsumerInterface::class);
        $this->mockMsm->setService('foo', $mockConsumer);

        // Expectations
        $this->expectQuery(
            NextQueueItemQry::class,
            ['includeTypes' => $includeTypes, 'excludeTypes' => $excludeTypes],
            $item
        );

        $exceptionMessage = 'something went wrong';
        $mockConsumer->shouldReceive('processMessage')
            ->once()
            ->with($item)
            ->andThrow(new DBALException($exceptionMessage));

        $this->expectException(DBALException::class);

        $this->sut->processNextItem($includeTypes, $excludeTypes);
    }

    /**
     * @param string $class expected Query/Command class name
     * @param array $expectedDtoData
     * @param array $result to be returned by $response->getResult()
     */
    private function expectQuery(mixed $class, mixed $expectedDtoData, mixed $result): void
    {
        $this->mockQueryHandlerManager
            ->shouldReceive('handleQuery')
            ->with(
                m::on(
                    function ($cmd) use ($expectedDtoData, $class) {
                        $matched = (
                            is_a($cmd, $class)
                            &&
                            $cmd->getArrayCopy() == $expectedDtoData
                        );
                        return $matched;
                    }
                )
            )
            ->once()
            ->andReturn($result);
    }
}
