<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler;

use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Mockery as m;

abstract class AbstractCallEntityMethodTestCase extends AbstractCommandHandlerTestCase
{
    protected $repoServiceName = 'changeMe';
    protected $entityMethodName = 'changeMe';
    protected $entityClass = 'changeMe';
    protected $repoClass = 'changeMe';
    protected $sutClass = 'changeMe';

    public function setUp(): void
    {
        $this->mockRepo($this->repoServiceName, $this->repoClass);
        $this->sut = new $this->sutClass();

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $entityId = 43;

        $entity = m::mock($this->entityClass);
        $entity->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($entityId);

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($entityId);

        $this->repoMap[$this->repoServiceName]->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($entity);

        $entity->shouldReceive($this->entityMethodName)
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap[$this->repoServiceName]->shouldReceive('save')
            ->with($entity)
            ->once()
            ->globally()
            ->ordered();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [$this->repoServiceName . ' updated'],
            $result->getMessages()
        );

        $this->assertEquals(
            $entityId,
            $result->getId($this->repoServiceName)
        );
    }
}
