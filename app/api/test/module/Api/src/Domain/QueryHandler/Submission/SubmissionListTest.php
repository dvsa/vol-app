<?php

declare(strict_types=1);

/**
 * SubmissionList Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Submission;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Submission\SubmissionList;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\Repository\Submission as SubmissionRepo;
use Dvsa\Olcs\Transfer\Query\Submission\SubmissionList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * SubmissionList Test
 */
class SubmissionListTest extends QueryHandlerTestCase
{
    /**
     * Set up test
     */
    public function setUp(): void
    {
        $this->sut = new SubmissionList();
        $this->mockRepo('Submission', SubmissionRepo::class);

        parent::setUp();
    }

    /**
     * Test handle list query
     */
    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['Submission']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult]);

        $this->repoMap['Submission']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], 2);
        $this->assertEquals($result['result'], ['foo']);
    }
}
