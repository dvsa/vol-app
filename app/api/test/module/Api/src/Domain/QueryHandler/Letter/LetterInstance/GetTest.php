<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterInstance\Get as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstance as LetterInstanceRepo;
use Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\Get as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Get LetterInstance QueryHandler Test
 */
class GetTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('LetterInstance', LetterInstanceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $data = ['id' => 123];
        $query = Qry::create($data);

        $mockLetterInstance = m::mock(\Dvsa\Olcs\Api\Entity\Letter\LetterInstance::class)
            ->shouldReceive('serialize')->with(
                [
                    'letterType',
                    'licence',
                    'application',
                    'case',
                    'letterInstanceSections' => [
                        'letterSectionVersion'
                    ],
                    'letterInstanceIssues' => [
                        'letterIssueVersion' => [
                            'letterIssueType'
                        ]
                    ],
                    'letterInstanceTodos' => [
                        'letterTodoVersion'
                    ],
                    'letterInstanceAppendices' => [
                        'letterAppendixVersion'
                    ]
                ]
            )->once()->andReturn(['id' => 123, 'reference' => 'LTR20251202ABC123'])->getMock();

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')->with($query)->once()
            ->andReturn($mockLetterInstance);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['id' => 123, 'reference' => 'LTR20251202ABC123'], $result->serialize());
    }
}
