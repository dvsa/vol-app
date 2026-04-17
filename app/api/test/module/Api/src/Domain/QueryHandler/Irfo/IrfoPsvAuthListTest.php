<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Irfo;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * IrfoPsvAuthList Test
 */
class IrfoPsvAuthListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new  QueryHandler\Irfo\IrfoPsvAuthList();
        $this->mockRepo('IrfoPsvAuth', Repository\IrfoPsvAuth::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query\Irfo\IrfoPsvAuthList::create([]);

        $entity = m::mock(\Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth::class)
            ->shouldReceive('serialize')
            ->with(
                [
                    'irfoPsvAuthType',
                    'status',
                ]
            )
            ->once()
            ->andReturn('SERIALIZED')
            ->getMock();

        $this->repoMap['IrfoPsvAuth']
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
