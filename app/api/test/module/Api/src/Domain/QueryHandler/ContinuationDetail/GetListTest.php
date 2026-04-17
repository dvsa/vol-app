<?php

declare(strict_types=1);

/**
 * Continuation details - get list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\QueryHandler\ContinuationDetail\GetList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as ContinuationDetailRepo;
use Dvsa\Olcs\Api\Domain\Repository\Continuation as ContinuationRepo;
use Dvsa\Olcs\Transfer\Query\ContinuationDetail\GetList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\ResultList;

/**
 * Continuation details - get list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Continuation', ContinuationRepo::class);
        $this->mockRepo('ContinuationDetail', ContinuationDetailRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $continuationId = 1;
        $licenceStatus = 'lsts_valid';
        $licenceNo = 'OB1';
        $method = 'email';
        $status = 'con_det_sts_printed';

        $query = Qry::create(
            [
                'continuationId' => $continuationId,
                'licenceStatus'  => $licenceStatus,
                'licenceNo'      => $licenceNo,
                'method'         => $method,
                'status'         => $status
            ]
        );
        $entity = m::mock(BundleSerializableInterface::class);
        $entity->shouldReceive('serialize')
            ->once()
            ->with(
                [
                    'continuation',
                    'status',
                    'licence' => [
                        'status',
                        'organisation',
                        'licenceType',
                        'goodsOrPsv'
                    ]
                ]
            )
            ->andReturn('details');

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchDetails')
            ->with($continuationId, $licenceStatus, $licenceNo, $method, $status)
            ->once()
            ->andReturn([$entity]);

        $this->repoMap['Continuation']
            ->shouldReceive('fetchWithTa')
            ->with($continuationId)
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('getYear')
                ->andReturn('2017')
                ->once()
                ->shouldReceive('getMonth')
                ->andReturn('1')
                ->once()
                ->shouldReceive('getTrafficArea')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getName')
                    ->andReturn('East of England')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            );

        $header = [
            'year' => '2017',
            'month' => '1',
            'name' => 'East of England'
        ];
        $this->assertEquals(
            ['results' => ['details'], 'count' => 1, 'header' => $header],
            $this->sut->handleQuery($query)
        );
    }
}
