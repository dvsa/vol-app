<?php

declare(strict_types=1);

/**
 * IrfoPermitStockList Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Irfo;

use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\Irfo\IrfoPermitStockList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPermitStock as IrfoPermitStockRepo;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPermitStockList as Qry;
use Mockery as m;

/**
 * IrfoPermitStockList Test
 */
class IrfoPermitStockListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new IrfoPermitStockList();
        $this->mockRepo('IrfoPermitStock', IrfoPermitStockRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockEntity = m::mock();
        $mockEntity->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['IrfoPermitStock']
            ->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn([$mockEntity])
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
