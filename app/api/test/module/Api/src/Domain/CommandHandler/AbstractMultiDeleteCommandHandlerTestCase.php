<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Mockery as m;

/**
 * Abstract delete command handler
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
abstract class AbstractMultiDeleteCommandHandlerTestCase extends AbstractCommandHandlerTestCase
{
    protected $cmdClass = 'changeMe';
    protected $sutClass = 'changeMe';
    protected $repoServiceName = 'changeMe';
    protected $repoClass = 'changeMe';
    protected $entityClass = 'changeMe';

    public function setUp(): void
    {
        $this->mockRepo($this->repoServiceName, $this->repoClass);
        $this->sut = new $this->sutClass();

        parent::setUp();
    }

    public function testHandleCommandMultiId(): void
    {
        $id1 = 999;
        $id2 = 777;

        $command = $this->buildCommand($id1, $id2);

        $entity1 = m::mock($this->entityClass);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('fetchById')
            ->with($id1)
            ->once()
            ->andReturn($entity1);

        $entity1->shouldReceive('canDelete')->once()->andReturn(true);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('delete')
            ->once()
            ->with($entity1);

        $entity2 = m::mock($this->entityClass);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('fetchById')
            ->with($id2)
            ->once()
            ->andReturn($entity2);

        $entity2->shouldReceive('canDelete')->once()->andReturn(true);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('delete')
            ->once()
            ->with($entity2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'id' . $id1 => $id1,
                'id' . $id2 => $id2
            ],
            'messages' => [
                'Id ' . $id1 . ' deleted',
                'Id ' . $id2 . ' deleted'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Tests when an entity can't be deleted
     */
    public function testHandleCantDelete(): void
    {
        $id1 = 999;
        $id2 = 777;
        $exceptionMessage = 'Id ' . $id1 . ' (' . $this->repoServiceName . ') is not allowed to be deleted';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $command = $this->buildCommand($id1, $id2);

        $entity = m::mock($this->entityClass);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('fetchById')
            ->with($id1)
            ->once()
            ->andReturn($entity);

        $entity->shouldReceive('canDelete')->once()->andReturn(false);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('delete')
            ->never();
        $this->sut->handleCommand($command);
    }

    /**
     * Tests when an entity can't be found
     */
    public function testHandleNotFound(): void
    {
        $id1 = 999;
        $id2 = 777;

        $command = $this->buildCommand($id1, $id2);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('fetchById')
            ->with($id1)
            ->once()
            ->andThrow(NotFoundException::class);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('fetchById')
            ->with($id2)
            ->once()
            ->andThrow(NotFoundException::class);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('delete')
            ->never();

        $expected = [
            'id' => [],
            'messages' => [
                'Id ' . $id1 . ' not found',
                'Id ' . $id2 . ' not found'
            ],
        ];

        $this->assertEquals($expected, $this->sut->handleCommand($command)->toArray());
    }

    private function buildCommand(int $id1, int $id2): mixed
    {
        $cmdData = [
            'ids' => [
                $id1,
                $id2,
            ],
        ];

        return $this->cmdClass::create($cmdData);
    }
}
