<?php

declare(strict_types=1);

/**
 * Team Printer Exceptions List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TeamPrinter;

use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\TeamPrinter\TeamPrinterExceptionsList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\TeamPrinter as TeamPrinterRepo;
use Dvsa\Olcs\Transfer\Query\TeamPrinter\TeamPrinterExceptionsList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Team Printer Exceptions List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TeamPrinterExceptionsListTest extends QueryHandlerTestCase
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

        $mockTeam = m::mock();
        $mockTeam->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['TeamPrinter']
            ->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn([$mockTeam])
            ->shouldReceive('fetchCount')
            ->with($query)
            ->once()
            ->andReturn(1)
            ->getMock();

        $this->assertSame(
            [
                'result'    => ['foo'],
                'count'     => 1,
            ],
            $this->sut->handleQuery($query)
        );
    }
}
