<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\Ebsr\EbsrSubmission;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\EbsrSubmission as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * EBSR Submission Test
 */
class EbsrSubmissionTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EbsrSubmission();
        $this->mockRepo('EbsrSubmission', EbsrSubmissionRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $ebsrSub = m::mock(EbsrSubmissionEntity::class)->makePartial();
        $ebsrSub->shouldReceive('serialize')
            ->andReturn(['foo']);

        $this->repoMap['EbsrSubmission']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($ebsrSub);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['foo'], $result->serialize());
    }
}
