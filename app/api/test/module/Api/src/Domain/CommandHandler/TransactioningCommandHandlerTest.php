<?php

declare(strict_types=1);

/**
 * Transactioning Command Handler Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\CommandHandler\CommandHandlerInterface;
use Dvsa\Olcs\Api\Domain\Exception\DisabledHandlerException;
use Dvsa\Olcs\Api\Domain\Repository\TransactionManagerInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactioningCommandHandler;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Transactioning Command Handler Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransactioningCommandHandlerTest extends MockeryTestCase
{
    private $wrapped;

    private $repo;

    /**
     * @var TransactioningCommandHandler
     */
    private $sut;

    public function setUp(): void
    {
        $this->wrapped = m::mock(CommandHandlerInterface::class);
        $this->repo = m::mock(TransactionManagerInterface::class);

        $this->sut = new TransactioningCommandHandler($this->wrapped, $this->repo);
    }

    public function testGetWrapped(): void
    {
        $this->assertSame($this->wrapped, $this->sut->getWrapped());
    }

    public function testHandleCommand(): void
    {
        $command = m::mock(CommandInterface::class);

        $this->repo->shouldReceive('beginTransaction')->once();

        $this->wrapped->shouldReceive('handleCommand')->once()->with($command)->andReturn(['result']);

        $this->repo->shouldReceive('commit')->once();

        $this->assertEquals(['result'], $this->sut->handleCommand($command));
    }

    public function testHandleCommandException(): void
    {
        $this->expectException(\Exception::class);

        $command = m::mock(CommandInterface::class);

        $this->repo->shouldReceive('beginTransaction')->once();

        $this->wrapped->shouldReceive('handleCommand')->once()->with($command)->andReturn(['result']);

        $this->repo->shouldReceive('commit')->once()->andThrow(new \Exception());

        $this->repo->shouldReceive('rollback')->once();

        $this->sut->handleCommand($command);
    }

    public function testCheckEnabled(): void
    {
        $isEnabled = true;
        $this->wrapped->shouldReceive('checkEnabled')->once()->andReturn($isEnabled);
        $this->assertEquals($isEnabled, $this->sut->checkEnabled());
    }

    public function testCheckEnabledExceptionPassedBack(): void
    {
        $this->expectException(DisabledHandlerException::class);
        $this->wrapped->shouldReceive('checkEnabled')->once()->andThrow(DisabledHandlerException::class);
        $this->sut->checkEnabled();
    }
}
