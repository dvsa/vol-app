<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\ContactDetail\PhoneContact;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryHandler\ContactDetail\PhoneContact\GetList
 */
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler\ContactDetail\PhoneContact\GetList();
        $this->mockRepo('PhoneContact', Repository\PhoneContact::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query\ContactDetail\PhoneContact\GetList::create([]);

        $mockEntity = m::mock(QueryHandler\BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->with(['phoneContactType'])
            ->times(2)
            ->andReturn('unit_Result')
            ->getMock();

        $this->repoMap['PhoneContact']
            ->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn([$mockEntity, clone $mockEntity])
            //
            ->shouldReceive('fetchCount')->with($query)->once()->andReturn(2);

        /** @var QueryHandler\ResultList $actual */
        $actual = $this->sut->handleQuery($query);

        static::assertSame(
            [
                'result' => ['unit_Result', 'unit_Result'],
                'count' => 2,
            ],
            $actual
        );
    }
}
