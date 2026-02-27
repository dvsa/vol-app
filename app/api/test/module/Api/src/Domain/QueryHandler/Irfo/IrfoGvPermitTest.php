<?php

declare(strict_types=1);

/**
 * IrfoGvPermit Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Irfo;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Irfo\IrfoGvPermit as Sut;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as IrfoGvPermitEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\IrfoGvPermit as IrfoGvPermitRepo;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoGvPermit as Qry;

/**
 * IrfoGvPermit Test
 */
class IrfoGvPermitTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('IrfoGvPermit', IrfoGvPermitRepo::class);
        $this->mockRepo('Fee', FeeRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $fees = [m::mock(FeeEntity::class)];

        $irfoGvPermit = m::mock(IrfoGvPermitEntity::class)->makePartial();
        $irfoGvPermit->shouldReceive('serialize')
            ->andReturn(['foo'])
            ->shouldReceive('isApprovable')
            ->with($fees)
            ->andReturn(true)
            ->shouldReceive('isGeneratable')
            ->andReturn(false);

        $this->repoMap['IrfoGvPermit']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($irfoGvPermit);

        $this->repoMap['Fee']->shouldReceive('fetchFeesByIrfoGvPermitId')
            ->with(111)
            ->andReturn($fees);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['foo', 'isApprovable' => true, 'isGeneratable' => false], $result->serialize());
    }
}
