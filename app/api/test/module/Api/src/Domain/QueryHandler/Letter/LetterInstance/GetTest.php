<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterInstance\Get as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstance as LetterInstanceRepo;
use Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\Get as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Get LetterInstance QueryHandler Test
 */
final class GetTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('LetterInstance', LetterInstanceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = ['id' => 123];
        $query = Qry::create($data);

        $mockLetterInstance = m::mock(\Dvsa\Olcs\Api\Entity\Letter\LetterInstance::class)
            ->shouldReceive('serialize')->with(
                [
                    'letterType' => [
                        'category',
                        'subCategory'
                    ],
                    'licence',
                    'application',
                    'case',
                    'letterInstanceSections' => [
                        'letterSectionVersion' => [
                            'letterSectionVariant' => [
                                'letterSection'
                            ]
                        ]
                    ],
                    'letterInstanceIssues' => [
                        'letterIssueVersion' => [
                            'letterIssueType'
                        ]
                    ],
                    'letterInstanceTodos' => [
                        'letterTodoVersion' => [
                            'letterTodo',
                        ],
                        'letterInstanceIssue' => [
                            'letterIssueVersion' => [
                                'letterIssueType',
                            ],
                        ],
                    ],
                    'letterInstanceAppendices' => [
                        'letterAppendixVersion' => [
                            'document'
                        ]
                    ]
                ]
            )->once()->andReturn(['id' => 123, 'reference' => 'LTR20251202ABC123'])->getMock();

        $mockLetterInstance->shouldReceive('getTodoRequiringIssueCounts')->andReturn([]);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')->with($query)->once()
            ->andReturn($mockLetterInstance);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['id' => 123, 'reference' => 'LTR20251202ABC123'], $result);
    }

    public function testEachTodoIsToldHowManyIssuesRequireIt(): void
    {
        // A to-do shared by several of the letter's issues still renders once, attached to
        // whichever issue came first. The count is what lets the editor say so.
        $query = Qry::create(['id' => 123]);

        $serialized = [
            'id' => 123,
            'letterInstanceTodos' => [
                ['id' => 1, 'letterTodoVersion' => ['id' => 27]],
                ['id' => 2, 'letterTodoVersion' => ['id' => 33]],
                ['id' => 3, 'letterTodoVersion' => ['id' => 99]],
            ],
        ];

        $mockLetterInstance = m::mock(\Dvsa\Olcs\Api\Entity\Letter\LetterInstance::class);
        $mockLetterInstance->shouldReceive('serialize')->once()->andReturn($serialized);
        $mockLetterInstance->shouldReceive('getTodoRequiringIssueCounts')->once()->andReturn([27 => 4, 33 => 1]);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')->with($query)->once()
            ->andReturn($mockLetterInstance);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(4, $result['letterInstanceTodos'][0]['requiringIssueCount']);
        $this->assertSame(1, $result['letterInstanceTodos'][1]['requiringIssueCount']);
        // A to-do the map does not mention falls back to 1, so the hint stays hidden rather
        // than the view erroring on a missing key.
        $this->assertSame(1, $result['letterInstanceTodos'][2]['requiringIssueCount']);
    }
}
