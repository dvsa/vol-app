<?php

declare(strict_types=1);

/**
 * Team Printer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TeamPrinter;

use Dvsa\Olcs\Api\Domain\QueryHandler\TeamPrinter\TeamPrinter as QueryHandler;
use Dvsa\Olcs\Transfer\Query\TeamPrinter\TeamPrinter as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\TeamPrinter as TeamPrinterRepo;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;

/**
 * Team Printer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TeamPrinterTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TeamPrinter', TeamPrinterRepo::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1]);

        $mockTeamPrinter = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')->with(
                [
                    'user',
                    'team',
                    'printer',
                    'subCategory' => ['category']
                ]
            )
            ->once()
            ->andReturn(['result' => ['foo'], 'count' => 1])
            ->getMock();

        $this->repoMap['TeamPrinter']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockTeamPrinter)
            ->getMock();

        $this->assertSame(
            [
                'result'    => ['foo'],
                'count'     => 1,
            ],
            $this->sut->handleQuery($query)->serialize()
        );
    }
}
