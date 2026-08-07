<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer;
use Dvsa\Olcs\Api\Service\Letter\Resolution\ResolvedSection;
use Dvsa\Olcs\Api\Service\Letter\Resolution\SectionResolution;
use Dvsa\Olcs\Api\Service\Letter\Resolution\UnresolvedSection;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * LetterInstanceComposer
 *
 * Shared by real letter generation and the letter type builder's preview, so that the two cannot
 * disagree about what a letter contains.
 */
class LetterInstanceComposerTest extends TestCase
{
    private LetterInstanceComposer $sut;

    protected function setUp(): void
    {
        $this->sut = new LetterInstanceComposer();
    }

    protected function tearDown(): void
    {
        m::close();
    }

    private function resolvedSection(int $displayOrder): ResolvedSection
    {
        return new ResolvedSection(
            new LetterSection(),
            new LetterSectionVariant(),
            new LetterSectionVersion(),
            $displayOrder,
            false
        );
    }

    private function todoVersion(int $id): LetterTodoVersion
    {
        $todoVersion = m::mock(LetterTodoVersion::class)->makePartial();
        $todoVersion->setId($id);

        return $todoVersion;
    }

    /**
     * @param LetterTodoVersion[] $todoVersions
     */
    private function issueVersionWithTodos(array $todoVersions): LetterIssueVersion
    {
        $issueTodos = [];
        foreach ($todoVersions as $todoVersion) {
            $issueTodo = m::mock(LetterIssueTodo::class)->makePartial();
            $issueTodo->shouldReceive('getLetterTodoVersion')->andReturn($todoVersion);
            $issueTodos[] = $issueTodo;
        }

        $issueVersion = m::mock(LetterIssueVersion::class)->makePartial();
        $issueVersion->shouldReceive('getLetterIssueTodos')->andReturn($issueTodos);

        return $issueVersion;
    }

    public function testComposesSectionsInTheOrderResolved(): void
    {
        $letterInstance = new LetterInstance();

        $this->sut->composeSections($letterInstance, new SectionResolution([
            $this->resolvedSection(0),
            $this->resolvedSection(1),
            $this->resolvedSection(2),
        ], []));

        $sections = $letterInstance->getLetterInstanceSections();

        $this->assertCount(3, $sections);
        $orders = [];
        foreach ($sections as $section) {
            $orders[] = $section->getDisplayOrder();
            $this->assertSame($letterInstance, $section->getLetterInstance());
        }
        $this->assertSame([0, 1, 2], $orders);
    }

    public function testComposesNothingForAnEmptyResolution(): void
    {
        $letterInstance = new LetterInstance();

        $this->sut->composeSections($letterInstance, new SectionResolution([], []));

        $this->assertCount(0, $letterInstance->getLetterInstanceSections());
    }

    /**
     * Unresolved sections are deliberately not composed: they have no version to read content from
     * and would fatal the renderer. They are reported to the caller instead.
     */
    public function testUnresolvedSectionsAreNotComposed(): void
    {
        $letterInstance = new LetterInstance();

        $unresolved = new UnresolvedSection(
            new LetterSection(),
            1,
            true,
            UnresolvedSection::REASON_NO_MATCHING_VARIANT
        );

        $this->sut->composeSections($letterInstance, new SectionResolution(
            [$this->resolvedSection(0)],
            [$unresolved]
        ));

        $this->assertCount(1, $letterInstance->getLetterInstanceSections());
    }

    public function testComposesIssuesInOrder(): void
    {
        $letterInstance = new LetterInstance();

        $first = m::mock(LetterIssueVersion::class)->makePartial();
        $second = m::mock(LetterIssueVersion::class)->makePartial();

        $this->sut->composeIssues($letterInstance, [$first, $second]);

        $issues = $letterInstance->getLetterInstanceIssues();
        $this->assertCount(2, $issues);
        $this->assertSame($first, $issues->first()->getLetterIssueVersion());
        $this->assertSame([0, 1], array_map(
            static fn($i): int => $i->getDisplayOrder(),
            $issues->toArray()
        ));
    }

    /**
     * Two issues frequently link the same to-do. Without deduplication the operator is told to do
     * the same thing twice (VOL-7280).
     */
    public function testTheSameTodoReachedFromTwoIssuesIsOnlyAddedOnce(): void
    {
        $shared = $this->todoVersion(1);

        $letterInstance = new LetterInstance();
        $this->sut->composeIssues($letterInstance, [
            $this->issueVersionWithTodos([$shared]),
            $this->issueVersionWithTodos([$shared]),
        ]);

        $this->sut->composeTodos($letterInstance);

        $this->assertCount(1, $letterInstance->getLetterInstanceTodos());
    }

    /**
     * A deduplicated to-do hangs off the first issue that brought it, which is what gives the
     * renderer "appears under the first issue type it relates to" without the renderer knowing.
     */
    public function testADeduplicatedTodoAttachesToTheFirstIssueThatBroughtIt(): void
    {
        $shared = $this->todoVersion(1);

        $letterInstance = new LetterInstance();
        $this->sut->composeIssues($letterInstance, [
            $this->issueVersionWithTodos([$shared]),
            $this->issueVersionWithTodos([$shared]),
        ]);

        $this->sut->composeTodos($letterInstance);

        $firstIssue = $letterInstance->getLetterInstanceIssues()->first();
        $this->assertSame($firstIssue, $letterInstance->getLetterInstanceTodos()->first()->getLetterInstanceIssue());
    }

    public function testDistinctTodosAreAllKept(): void
    {
        $letterInstance = new LetterInstance();
        $this->sut->composeIssues($letterInstance, [
            $this->issueVersionWithTodos([$this->todoVersion(1), $this->todoVersion(2)]),
            $this->issueVersionWithTodos([$this->todoVersion(3)]),
        ]);

        $this->sut->composeTodos($letterInstance);

        $this->assertCount(3, $letterInstance->getLetterInstanceTodos());
    }

    public function testComposesAppendicesInOrder(): void
    {
        $letterInstance = new LetterInstance();

        $first = m::mock(LetterAppendixVersion::class)->makePartial();
        $second = m::mock(LetterAppendixVersion::class)->makePartial();

        $this->sut->composeAppendices($letterInstance, [$first, $second]);

        $appendices = $letterInstance->getLetterInstanceAppendices();
        $this->assertCount(2, $appendices);
        $this->assertSame($first, $appendices->first()->getLetterAppendixVersion());
        $this->assertSame([0, 1], array_map(
            static fn($a): int => $a->getDisplayOrder(),
            $appendices->toArray()
        ));
    }
}
