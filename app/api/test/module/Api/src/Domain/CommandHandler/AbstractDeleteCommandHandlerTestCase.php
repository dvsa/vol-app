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
abstract class AbstractDeleteCommandHandlerTestCase extends AbstractCommandHandlerTestCase
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

    public function testHandleCommandSingleId(): void
    {
        $id = 999;
        $command = $this->cmdClass::create(['id' => $id]);

        $entity = m::mock($this->entityClass);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($entity);

        $entity->shouldReceive('canDelete')->once()->andReturn(true);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('delete')
            ->once()
            ->with($entity);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['id' . $id => $id],
            'messages' => ['Id ' . $id . ' deleted']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Tests when an entity can't be deleted
     */
    public function testHandleCantDelete(): void
    {
        $id = 999;
        $exceptionMessage = 'Id ' . $id . ' (' . $this->repoServiceName . ') is not allowed to be deleted';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $command = $this->cmdClass::create(['id' => $id]);

        $entity = m::mock($this->entityClass);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('fetchById')
            ->with($id)
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
        $id = 999;
        $command = $this->cmdClass::create(['id' => $id]);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andThrow(NotFoundException::class);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('delete')
            ->never();

        $expected = [
            'id' => [],
            'messages' => [
                'Id ' . $id . ' not found',
            ]
        ];

        $this->assertEquals($expected, $this->sut->handleCommand($command)->toArray());
    }
}
