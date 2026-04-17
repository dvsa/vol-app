<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\QuestionAnswer as QuestionAnswerHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\QuestionAnswer as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * QuestionAnswer Test
 */
class QuestionAnswerTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QuestionAnswerHandler();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $id = 1;

        $query = QryClass::create(['id' => $id]);

        $questionAnswerData = [
            [
                'question' => 'q1',
                'answer' => 'a1',
            ],
            [
                'question' => 'q2',
                'answer' => 'a2',
            ],
        ];

        $mockEntity = m::mock(IrhpApplicationEntity::class)
            ->shouldReceive('getQuestionAnswerData')
            ->withNoArgs()
            ->once()
            ->andReturn($questionAnswerData)
            ->getMock();

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockEntity);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals($questionAnswerData, $result);
    }
}
