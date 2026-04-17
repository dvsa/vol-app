<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\ContinuationDetail;

use Dvsa\Olcs\Transfer\Query\ContinuationDetail\Review as ReviewQry;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as ContinuationDetailRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\ContinuationDetail\Review;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * Continuation details review test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ReviewTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Review();
        $this->mockRepo('ContinuationDetail', ContinuationDetailRepo::class);

        $this->mockedSmServices['ContinuationReview'] = m::mock();

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = ReviewQry::create(['id' => 111]);

        $continuationDetail = m::mock(ApplicationEntity::class)->makePartial();

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($continuationDetail);

        $this->mockedSmServices['ContinuationReview']
            ->shouldReceive('generate')
            ->with($continuationDetail)
            ->andReturn('<foo>');

        $expected = [
            'markup' => '<foo>'
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
    }
}
