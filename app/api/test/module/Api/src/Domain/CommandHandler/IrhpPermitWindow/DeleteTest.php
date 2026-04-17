<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitWindow;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitWindow\Delete as DeleteHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as PermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as PermitWindowRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Delete as DeleteCmd;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as PermitWindowEntity;

/**
 * Delete IRHP Permit Window
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteHandler();
        $this->mockRepo('IrhpPermitWindow', PermitWindowRepo::class);
        $this->mockRepo('IrhpPermitStock', PermitStockRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 1;

        $cmdData = [
            'id' => $id
        ];
        $command = DeleteCmd::create($cmdData);

        $irhpPermitWindow = m::mock(PermitWindowEntity::class);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($irhpPermitWindow);

        $irhpPermitWindow->shouldReceive('canBeDeleted')->once()->andReturn(true);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('delete')
            ->with($irhpPermitWindow);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['id' => 1],
            'messages' => ['Permit Window Deleted']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCantDelete(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('irhp-permit-windows-cannot-delete-past-or-active-windows');

        $id = 1;

        $cmdData = [
            'id' => $id
        ];
        $command = DeleteCmd::create($cmdData);

        $irhpPermitWindow = m::mock(PermitWindowEntity::class);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($irhpPermitWindow);

        $irhpPermitWindow->shouldReceive('canBeDeleted')->once()->andReturn(false);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('delete')
            ->never();

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandNotFoundException(): void
    {
        $id = 1;

        $cmdData = [
            'id' => $id
        ];
        $command = DeleteCmd::create($cmdData);

        $irhpPermitWindow = m::mock(PermitWindowEntity::class);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($irhpPermitWindow);

        $irhpPermitWindow->shouldReceive('canBeDeleted')->once()->andReturn(true);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('delete')
            ->andThrow(NotFoundException::class);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ['Id 1 not found']
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
