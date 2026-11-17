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
                    'status',
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
                            'letterIssueType',
                            'letterIssue',
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
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();

        $mockLetterInstance->shouldReceive('getTodoRequiringIssueCounts')->andReturn([]);
        $mockLetterInstance->shouldReceive('getTodoPlacementIssueIds')->andReturn([]);
        $mockLetterInstance->shouldReceive('isOpenForChanges')->andReturn(true);
        $mockLetterInstance->shouldReceive('isSent')->andReturn(false);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')->with($query)->once()
            ->andReturn($mockLetterInstance);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            ['id' => 123, 'reference' => 'LTR20251202ABC123', 'isOpenForChanges' => true, 'isSent' => false],
            $result
        );
    }

    public function testEachTodoIsToldHowManyIssuesRequireIt(): void
    {
        // A to-do shared by several of the letter's issues still renders once, attached to
        // whichever issue came first. The count is what lets the editor say so.
        $query = Qry::create(['id' => 123]);

        $serialized = [
            'id' => 123,
            'letterInstanceTodos' => [
                ['id' => 1, 'letterTodoVersion' => ['id' => 32, 'letterTodo' => ['id' => 5]]],
                ['id' => 2, 'letterTodoVersion' => ['id' => 33, 'letterTodo' => ['id' => 6]]],
                ['id' => 3, 'letterTodoVersion' => ['id' => 99, 'letterTodo' => ['id' => 7]]],
            ],
        ];

        $mockLetterInstance = m::mock(\Dvsa\Olcs\Api\Entity\Letter\LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('serialize')->once()->andReturn($serialized);
        $mockLetterInstance->shouldReceive('isOpenForChanges')->andReturn(true)->byDefault();
        $mockLetterInstance->shouldReceive('isSent')->andReturn(false)->byDefault();
        // counts are per to-do, so version 32 picks up issues that linked older versions of to-do 5
        $mockLetterInstance->shouldReceive('getTodoRequiringIssueCounts')->once()
            ->andReturn(['todo-5' => 4, 'todo-6' => 1]);
        $mockLetterInstance->shouldReceive('getTodoPlacementIssueIds')->andReturn([]);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')->with($query)->once()
            ->andReturn($mockLetterInstance);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(4, $result['letterInstanceTodos'][0]['requiringIssueCount']);
        $this->assertSame(1, $result['letterInstanceTodos'][1]['requiringIssueCount']);
        // A to-do the map does not mention falls back to 1, so the hint stays hidden rather
        // than the view erroring on a missing key.
        $this->assertSame(1, $result['letterInstanceTodos'][2]['requiringIssueCount']);
    }

    public function testATodoVersionWithoutAParentIsCountedByVersion(): void
    {
        $query = Qry::create(['id' => 123]);

        $serialized = [
            'id' => 123,
            'letterInstanceTodos' => [
                ['id' => 1, 'letterTodoVersion' => ['id' => 27]],
            ],
        ];

        $mockLetterInstance = m::mock(\Dvsa\Olcs\Api\Entity\Letter\LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('serialize')->once()->andReturn($serialized);
        $mockLetterInstance->shouldReceive('isOpenForChanges')->andReturn(true)->byDefault();
        $mockLetterInstance->shouldReceive('isSent')->andReturn(false)->byDefault();
        $mockLetterInstance->shouldReceive('getTodoRequiringIssueCounts')->once()->andReturn(['version-27' => 3]);
        $mockLetterInstance->shouldReceive('getTodoPlacementIssueIds')->andReturn([]);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')->with($query)->once()
            ->andReturn($mockLetterInstance);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(3, $result['letterInstanceTodos'][0]['requiringIssueCount']);
    }

    /**
     * VOL-7737: the edit page puts each to-do under an issue, so it needs the issues it can sit
     * under, preferred first. The entity works that out per to-do id.
     */
    public function testEachTodoIsToldWhichIssuesItCanSitUnder(): void
    {
        $query = Qry::create(['id' => 123]);

        $serialized = [
            'id' => 123,
            'letterInstanceTodos' => [
                ['id' => 1, 'letterTodoVersion' => ['id' => 32, 'letterTodo' => ['id' => 5]]],
                ['id' => 2, 'letterTodoVersion' => ['id' => 33, 'letterTodo' => ['id' => 6]]],
            ],
        ];

        $mockLetterInstance = m::mock(\Dvsa\Olcs\Api\Entity\Letter\LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('serialize')->once()->andReturn($serialized);
        $mockLetterInstance->shouldReceive('isOpenForChanges')->andReturn(true)->byDefault();
        $mockLetterInstance->shouldReceive('isSent')->andReturn(false)->byDefault();
        $mockLetterInstance->shouldReceive('getTodoRequiringIssueCounts')->andReturn([]);
        $mockLetterInstance->shouldReceive('getTodoPlacementIssueIds')->once()->andReturn([1 => [10, 12]]);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')->with($query)->once()
            ->andReturn($mockLetterInstance);

        $result = $this->sut->handleQuery($query);

        $this->assertSame([10, 12], $result['letterInstanceTodos'][0]['placementIssueIds']);
        $this->assertSame([], $result['letterInstanceTodos'][1]['placementIssueIds']);
    }

    /**
     * VOL-7735: the internal app reads the effective heading and the "Input required" and custom
     * to-do flags from here rather than working them out from the versions itself.
     */
    public function testEachIssueAndTodoCarriesItsFlags(): void
    {
        $query = Qry::create(['id' => 123]);

        $version = new \Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion();
        $version->setHeading('Other issue');
        $version->setIsOther(true);
        $other = new \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue();
        $other->setId(10);
        $other->setLetterIssueVersion($version);
        $other->setEditedHeading('Vehicle storage');
        $custom = new \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo();
        $custom->setId(20);
        $custom->setDisplayOrder(1);
        $other->addLetterInstanceTodo($custom);
        $library = new \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo();
        $library->setId(21);
        $library->setLetterTodoVersion(new \Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion());

        $serialized = [
            'id' => 123,
            'letterInstanceIssues' => [['id' => 10]],
            'letterInstanceTodos' => [['id' => 20], ['id' => 21, 'letterTodoVersion' => ['id' => 5]]],
        ];

        $mockLetterInstance = m::mock(\Dvsa\Olcs\Api\Entity\Letter\LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')->andReturn(new \Doctrine\Common\Collections\ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('serialize')->once()->andReturn($serialized);
        $mockLetterInstance->shouldReceive('isOpenForChanges')->andReturn(true)->byDefault();
        $mockLetterInstance->shouldReceive('isSent')->andReturn(false)->byDefault();
        $mockLetterInstance->shouldReceive('getTodoRequiringIssueCounts')->andReturn([]);
        $mockLetterInstance->shouldReceive('getTodoPlacementIssueIds')->andReturn([]);
        $mockLetterInstance->shouldReceive('isOpenForChanges')->andReturn(false);
        $mockLetterInstance->shouldReceive('isSent')->andReturn(true);
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')->andReturn(new \Doctrine\Common\Collections\ArrayCollection([$other]));
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new \Doctrine\Common\Collections\ArrayCollection([$custom, $library]));

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($mockLetterInstance);

        $result = $this->sut->handleQuery($query);

        // the letter's own rule for whether it can still change, so pages don't keep a copy of it
        $this->assertFalse($result['isOpenForChanges']);
        $this->assertTrue($result['isSent']);

        $issue = $result['letterInstanceIssues'][0];
        $this->assertSame('Vehicle storage', $issue['effectiveHeading']);
        $this->assertSame('Vehicle storage', $issue['displayName']);
        $this->assertTrue($issue['isOther']);
        $this->assertTrue($issue['inputPending']);

        [$customRow, $libraryRow] = $result['letterInstanceTodos'];
        $this->assertTrue($customRow['isCustom']);
        $this->assertSame(1, $customRow['customIndex']);
        $this->assertSame('Vehicle storage: to-do 1', $customRow['customLabel']);
        $this->assertNull($libraryRow['customLabel']);
        $this->assertTrue($customRow['inputPending']);
        $this->assertFalse($libraryRow['isCustom']);
        $this->assertNull($libraryRow['customIndex']);
        $this->assertFalse($libraryRow['inputPending']);
    }

    /**
     * A section that got the empty catch-all of wording that didn't apply (the interim paragraph
     * when Interim isn't ticked) is marked, so the preview doesn't offer it for editing.
     */
    public function testEachSectionSaysWhetherItHasAnythingToEdit(): void
    {
        $query = Qry::create(['id' => 123]);

        $closingVersion = new \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion();
        $closingVersion->setDefaultContent(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Yours sincerely']]]]);
        $closing = new \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection();
        $closing->setId(30);
        $closing->setLetterSectionVersion($closingVersion);

        // interim wording: a catch-all with nothing in it, and wording for when Interim is ticked
        $catchAll = new \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant();
        $whenTicked = new \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant();
        $whenTicked->setLetterChoice(new \Dvsa\Olcs\Api\Entity\Letter\LetterChoice());
        $interimSection = new \Dvsa\Olcs\Api\Entity\Letter\LetterSection();
        $interimSection->addVariant($catchAll);
        $interimSection->addVariant($whenTicked);
        $interimVersion = new \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion();
        $interimVersion->setDefaultContent(['blocks' => []]);
        $interimVersion->setLetterSectionVariant($catchAll);
        $interim = new \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection();
        $interim->setId(31);
        $interim->setLetterSectionVersion($interimVersion);

        $mockLetterInstance = m::mock(\Dvsa\Olcs\Api\Entity\Letter\LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')->andReturn(new \Doctrine\Common\Collections\ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new \Doctrine\Common\Collections\ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')->andReturn(new \Doctrine\Common\Collections\ArrayCollection([$closing, $interim]));
        $mockLetterInstance->shouldReceive('serialize')->once()->andReturn([
            'id' => 123,
            'letterInstanceSections' => [['id' => 30], ['id' => 31]],
        ]);
        $mockLetterInstance->shouldReceive('isOpenForChanges')->andReturn(true);
        $mockLetterInstance->shouldReceive('isSent')->andReturn(false);
        $mockLetterInstance->shouldReceive('getTodoRequiringIssueCounts')->andReturn([]);
        $mockLetterInstance->shouldReceive('getTodoPlacementIssueIds')->andReturn([]);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($mockLetterInstance);

        $result = $this->sut->handleQuery($query);

        $this->assertSame([false, true], array_column($result['letterInstanceSections'], 'nothingToEdit'));
    }
}
