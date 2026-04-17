<?php

declare(strict_types=1);

/**
 * SubmissionSectionComment Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Submission;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Submission\SubmissionSectionComment;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\Repository\SubmissionSectionComment as SubmissionSectionCommentRepo;
use Dvsa\Olcs\Transfer\Query\Submission\SubmissionSectionComment as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * SubmissionSectionComment Test
 */
class SubmissionSectionCommentTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new SubmissionSectionComment();
        $this->mockRepo('SubmissionSectionComment', SubmissionSectionCommentRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $this->repoMap['SubmissionSectionComment']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn(
                m::mock(BundleSerializableInterface::class)
                    ->shouldReceive('serialize')
                    ->andReturn(['foo'])
                    ->getMock()
            );

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(['foo'], $result->serialize());
    }
}
