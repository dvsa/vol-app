<?php

declare(strict_types=1);

/**
 * TmQualificationList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TmQualification;

use Dvsa\Olcs\Api\Domain\QueryHandler\TmQualification\TmQualificationsList as QueryHandler;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Transfer\Query\TmQualification\TmQualificationsList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\Repository\TmQualification as TmQualificationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Doctrine\ORM\Query as OrmQuery;

/**
 * TmQualificationList Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmQualificationListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TmQualification', TmQualificationRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);
        $this->mockRepo('TransportManager', DocumentRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['transportManager' => 1]);

        $mockTransportManager = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['bar' => 'cake'])
            ->getMock();

        $this->repoMap['TransportManager']->shouldReceive('fetchById')
            ->with(1)->andReturn($mockTransportManager);

        $mockQualification = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn('foo')
            ->getMock();

        $mockDocument = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn('doc')
            ->getMock();

        $this->repoMap['TmQualification']
            ->shouldReceive('fetchList')
            ->with($query, OrmQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn([$mockQualification])
            ->shouldReceive('fetchCount')
            ->with($query)
            ->once()
            ->andReturn(1)
            ->getMock();

        $this->repoMap['Document']
            ->shouldReceive('fetchListForTm')
            ->with(1)
            ->andReturn([$mockDocument])
            ->once()
            ->getMock();

        $result = $this->sut->handleQuery($query);
        $tm = $result['transportManager'];
        $this->assertEquals($tm->serialize(), ['bar' => 'cake']);
        unset($result['transportManager']);

        $this->assertSame(
            [
                'result'    => ['foo'],
                'count'     => 1,
                'documents' => ['doc']
            ],
            $result
        );
    }
}
