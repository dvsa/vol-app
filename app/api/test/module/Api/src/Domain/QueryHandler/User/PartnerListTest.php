<?php

declare(strict_types=1);

/**
 * PartnerList Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\User;

use Mockery as m;
use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\User\PartnerList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Partner as PartnerRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Transfer\Query\User\PartnerList as Qry;

/**
 * PartnerList Test
 */
class PartnerListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PartnerList();
        $this->mockRepo('Partner', PartnerRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $this->repoMap['Partner']->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->andReturn(
                [
                    m::mock(BundleSerializableInterface::class)
                        ->shouldReceive('serialize')
                        ->andReturn(['foo'])
                        ->getMock(),
                    m::mock(BundleSerializableInterface::class)
                        ->shouldReceive('serialize')
                        ->andReturn(['bar'])
                        ->getMock()
                ]
            );

        $this->repoMap['Partner']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(2, $result['count']);
        $this->assertEquals([['foo'], ['bar']], $result['result']);
    }
}
