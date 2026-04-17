<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Irfo;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * IrfoCountryList test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class IrfoCountryListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler\Irfo\IrfoCountryList();
        $this->mockRepo('IrfoCountry', Repository\IrfoCountry::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query\Irfo\IrfoCountryList::create([]);

        $entity = m::mock(\Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry::class)
            ->shouldReceive('serialize')
            ->with([])->once()
            ->andReturn('SERIALIZED')
            ->getMock();

        $this->repoMap['IrfoCountry']
            ->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)
            ->andReturn([$entity])
            //
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2);

        $actual = $this->sut->handleQuery($query);

        static::assertEquals(2, $actual['count']);
        static::assertEquals(['SERIALIZED'], $actual['result']);
    }
}
