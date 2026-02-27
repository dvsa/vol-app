<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\OpenWindows;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType as IrhpPermitTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use DateTime;

class OpenWindowsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new OpenWindows();
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);
        $this->mockRepo('IrhpPermitType', IrhpPermitTypeRepo::class);
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);

        parent::setUp();
    }

    #[\Override]
    public function initReferences(): void
    {
        $this->references = [
            IrhpPermitWindow::class => [
                1 => m::mock(IrhpPermitWindow::class),
                2 => m::mock(IrhpPermitWindow::class),
                3 => m::mock(IrhpPermitWindow::class)
            ],
            IrhpPermitType::class => [
                10 => m::mock(IrhpPermitType::class)
            ],
            IrhpPermitStock::class => [
                100 => m::mock(IrhpPermitStock::class),
                200 => m::mock(IrhpPermitStock::class),
                300 => m::mock(IrhpPermitStock::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleQuery(): void
    {
        /** @var IrhpPermitType $irhpPermitType */
        $irhpPermitType = $this->references[IrhpPermitType::class][10];
        /** @var IrhpPermitWindow $irhpPermitWindowB */
        $irhpPermitWindowB = $this->references[IrhpPermitWindow::class][2];

        $stocks = $this->references[IrhpPermitStock::class];

        $dateTime = new DateTime('now');
        $dateTimeAsString = $dateTime->format('Y-m-d H:i:s');

        $query = m::mock(QueryInterface::class);

        $query->shouldReceive('getPermitType')
            ->andReturn(10);

        $this->repoMap['IrhpPermitType']
            ->shouldReceive('fetchById')
            ->with(10)
            ->andReturn($irhpPermitType);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchByIrhpPermitType')
            ->with(10)
            ->andReturn($stocks);

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindows')
            ->with(100, m::on(fn($dateTime) => $dateTime->format('Y-m-d H:i:s') == $dateTimeAsString))
            ->once()
            ->andReturn([]);

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindows')
            ->with(200, m::on(fn($dateTime) => $dateTime->format('Y-m-d H:i:s') == $dateTimeAsString))
            ->once()
            ->andReturn([$irhpPermitWindowB]);

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindows')
            ->with(300, m::type(DataTime::class))
            ->never();

        $this->assertEquals(
            ['windows' => [$irhpPermitWindowB]],
            $this->sut->handleQuery($query)
        );
    }

    public function testHandleQueryWhenNoOpenWindow(): void
    {
        $irhpPermitTypeId = 10;

        /** @var IrhpPermitType $irhpPermitType */
        $irhpPermitType = $this->references[IrhpPermitType::class][$irhpPermitTypeId];

        $stocks = $this->references[IrhpPermitStock::class];

        $query = m::mock(QueryInterface::class);

        $query->shouldReceive('getPermitType')
            ->andReturn($irhpPermitTypeId);

        $this->repoMap['IrhpPermitType']
            ->shouldReceive('fetchById')
            ->with($irhpPermitTypeId)
            ->andReturn($irhpPermitType);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchByIrhpPermitType')
            ->with($irhpPermitTypeId)
            ->andReturn($stocks);

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindows')
            ->andReturn([]);

        $this->assertEquals(
            ['windows' => []],
            $this->sut->handleQuery($query)
        );
    }
}
