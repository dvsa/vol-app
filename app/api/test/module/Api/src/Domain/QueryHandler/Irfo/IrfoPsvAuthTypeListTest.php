<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Irfo;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * IrfoPsvAuthType list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class IrfoPsvAuthTypeListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler\Irfo\IrfoPsvAuthTypeList();
        $this->mockRepo('IrfoPsvAuthType', Repository\IrfoPsvAuthType::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query\Irfo\IrfoPsvAuthTypeList::create([]);

        $entity = m::mock(\Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType::class)
            ->shouldReceive('serialize')
            ->with([])
            ->once()
            ->andReturn('SERIALIZED')
            ->getMock();

        $this->repoMap['IrfoPsvAuthType']
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
