<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitWindow;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitWindow\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as PermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as PermitWindowRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\IrhpPermitWindow\Create as CreateCmd;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as PermitWindowEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Create IRHP Permit Window Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public $today;
    public $tomorrow;
    /**
     * @var string
     */
    public $yesterday;
    public function setUp(): void
    {
        $this->sut = m::mock(CreateHandler::class)->makePartial();
        $this->mockRepo('IrhpPermitWindow', PermitWindowRepo::class);
        $this->mockRepo('IrhpPermitStock', PermitStockRepo::class);

        $this->today = (new DateTime())->format('Y-m-d');
        $this->tomorrow = (new DateTime())->modify('+1 day')->format('Y-m-d');
        $this->yesterday = (new DateTime())->modify('-1 day')->format('Y-m-d');

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $cmdData = [
            'irhpPermitStock' => '1',
            'startDate' => $this->today,
            'endDate' => $this->tomorrow,
        ];

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('findOverlappingWindowsByType')
            ->once()
            ->andReturn([]);

        $command = CreateCmd::create($cmdData);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchById')
            ->with($command->getIrhpPermitStock())
            ->andReturn($irhpPermitStock);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PermitWindowEntity::class))
            ->andReturnUsing(
                function (PermitWindowEntity $permitWindow) use (&$savedPermitStock) {
                    $permitWindow->setId(1);
                }
            );

        $this->sut->shouldReceive('validateStockRanges')
            ->with($irhpPermitStock)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['IrhpPermitWindow' => 1],
            'messages' => ["IRHP Permit Window '1' created"]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test for overlapping IRHP Permit Windows - no values are asserted as this tests to ensure that a validation
     * exception is thrown.
     */
    public function testHandleOverlap(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);
        $this->expectExceptionMessage('The dates overlap with another window for this Permit stock');

        $cmdData = [
            'irhpPermitStock' => '1',
            'startDate' => $this->today,
            'endDate' => $this->tomorrow,
        ];

        $command = CreateCmd::create($cmdData);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('findOverlappingWindowsByType')
            ->once()
            ->with(
                $cmdData['irhpPermitStock'],
                $cmdData['startDate'],
                $cmdData['endDate'],
                null
            )
            ->andReturn([m::mock(PermitWindowEntity::class)]);

        $this->sut->handleCommand($command);
    }
}
