<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler;

use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Mockery as m;

abstract class AbstractUpdateDefinedValueTestCase extends AbstractCommandHandlerTestCase
{
    protected $repoServiceName = 'changeMe';
    protected $entityMethodName = 'changeMe';
    protected $entityClass = 'changeMe';
    protected $repoClass = 'changeMe';
    protected $definedValue = 'changeMe';

    public function setUp(): void
    {
        $this->mockRepo($this->repoServiceName, $this->repoClass);
        $this->sut = new $this->sutClass();

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        if ($this->sut->isRefData()) {
            $this->refData = [
                $this->definedValue
            ];
        }

        parent::initReferences();
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
            ->withNoArgs()
            ->andReturn($entityId);

        $this->repoMap[$this->repoServiceName]->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($entity);

        if ($this->sut->isRefData()) {
            $entity->shouldReceive($this->entityMethodName)
                ->with($this->refData[$this->definedValue])
                ->once()
                ->globally()
                ->ordered();
        } else {
            $entity->shouldReceive($this->entityMethodName)
                ->with($this->definedValue)
                ->once()
                ->globally()
                ->ordered();
        }

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
