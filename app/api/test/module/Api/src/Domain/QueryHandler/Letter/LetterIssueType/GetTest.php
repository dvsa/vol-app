<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Letter\LetterIssueType;

use Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterIssueType\Get as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterIssueType as LetterIssueTypeRepo;
use Dvsa\Olcs\Transfer\Query\Letter\LetterIssueType\Get as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Get Letter Issue Type Test
 */
class GetTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('LetterIssueType', LetterIssueTypeRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = ['id' => 123];
        $query = Qry::create($data);

        $mockLetterIssueType = m::mock(\Dvsa\Olcs\Api\Entity\Letter\LetterIssueType::class)
            ->shouldReceive('serialize')
            ->with([])
            ->once()
            ->andReturn(['id' => 123, 'name' => 'Test Issue Type'])
            ->getMock();

        $this->repoMap['LetterIssueType']->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockLetterIssueType);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['id' => 123, 'name' => 'Test Issue Type'], $result->serialize());
    }
}
