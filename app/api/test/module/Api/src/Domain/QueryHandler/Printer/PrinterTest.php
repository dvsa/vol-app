<?php

declare(strict_types=1);

/**
 * Printer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Printer;

use Dvsa\Olcs\Api\Domain\QueryHandler\Printer\Printer as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Printer\Printer as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Printer as PrinterRepo;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;

/**
 * Printer Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrinterTest extends QueryHandlerTestCase
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

        $mockPrinter = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['result' => ['foo'], 'count' => 1])
            ->getMock();

        $this->repoMap['Printer']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockPrinter)
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
