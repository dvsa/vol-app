<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Submission;

use Dvsa\Olcs\Api\Domain\QueryHandler\Submission\Submission;
use Dvsa\Olcs\Api\Domain\Repository\Submission as SubmissionRepo;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Transfer\Query\Submission\Submission as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Submission Test
 */
class SubmissionTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Submission();
        $this->mockRepo('Submission', SubmissionRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $submission = m::mock(SubmissionEntity::class);
        $submission->shouldReceive('getSubmissionType->getId')->once()->andReturn('submission_type_o_bus_reg');
        $submission->shouldReceive('canClose')->once()->andReturn(true);
        $submission->shouldReceive('isClosed')->once()->andReturn(false);
        $submission->shouldReceive('canReopen')->once()->andReturn(false);
        $submission->shouldReceive('isNi')->once()->andReturn(true);
        $submission->shouldReceive('serialize')->andReturn(['foo']);

        $this->repoMap['Submission']
            ->shouldReceive('disableSoftDeleteable')
            ->with(
                [
                    \Dvsa\Olcs\Api\Entity\Pi\Reason::class,
                    \Dvsa\Olcs\Api\Entity\User\User::class
                ]
            )
            ->once()
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($submission)
            ->shouldReceive('getRefdataReference')
            ->with('submission_type_t_bus_reg')
            ->andReturn('title');

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(
            [
                'foo',
                'canClose' => true,
                'isClosed' => false,
                'canReopen' => false,
                'submissionTypeTitle' => 'title',
                'isNi' => true
            ],
            $result->serialize()
        );
    }
}
