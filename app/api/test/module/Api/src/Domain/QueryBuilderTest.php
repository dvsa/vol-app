<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain;

use Dvsa\Olcs\Api\Domain\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryPartial\QueryPartialInterface;
use Dvsa\Olcs\Api\Domain\QueryPartialServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryBuilder
 */
class QueryBuilderTest extends MockeryTestCase
{
    /** @var  QueryBuilder */
    private $sut;

    /** @var  m\MockInterface */
    private $mockQueryPartialSrvMngr;

    public function setUp(): void
    {
        $this->mockQueryPartialSrvMngr = m::mock(QueryPartialServiceManager::class);

        $this->sut = new QueryBuilder($this->mockQueryPartialSrvMngr);
    }

    public function testCallFailQbNotSet(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(QueryBuilder::ERR_QB_NOT_SET);

        $this->sut->unit_testMethod('unit_Arg1', 'unit_Arg2');
    }

    public function testCallPass(): void
    {
        /** @var  \Doctrine\ORM\QueryBuilder $mockQb */
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $mockPartial = m::mock(QueryPartialInterface::class)
            ->shouldReceive('modifyQuery')->once()->with($mockQb, ['unit_Arg1', 'unit_Arg2'])
            ->getMock();

        $this->mockQueryPartialSrvMngr
            ->shouldReceive('get')->once()->with('unit_testMethod')->andReturn($mockPartial);

        $actual = $this->sut
            ->modifyQuery($mockQb)
            ->unit_testMethod('unit_Arg1', 'unit_Arg2');

        static::assertSame($this->sut, $actual);
    }
}
