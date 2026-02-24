<?php

declare(strict_types=1);

/**
 * Printer List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Printer;

use Dvsa\Olcs\Api\Domain\QueryHandler\Printer\PrinterList as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Printer\PrinterList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Printer as PrinterRepo;
use Mockery as m;
use Doctrine\ORM\Query as DoctrineQuery;

/**
 * Printer List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrinterListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Printer', PrinterRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1]);

        $mockPrinter = m::mock();
        $mockPrinter->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['Printer']
            ->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn([$mockPrinter])
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
