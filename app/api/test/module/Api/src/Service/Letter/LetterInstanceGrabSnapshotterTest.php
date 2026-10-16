<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceAppendix;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceGrabSnapshotter;
use Dvsa\Olcs\Api\Service\Letter\VolGrabContextBuilder;
use Dvsa\Olcs\Api\Service\Letter\VolGrabReplacementService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LetterInstanceGrabSnapshotterTest extends MockeryTestCase
{
    private m\MockInterface $grabService;

    private LetterInstanceGrabSnapshotter $sut;

    public function setUp(): void
    {
        $this->grabService = m::mock(VolGrabReplacementService::class);
        $this->sut = new LetterInstanceGrabSnapshotter($this->grabService, new VolGrabContextBuilder());
    }

    private static function paragraph(string $text): array
    {
        return ['blocks' => [['type' => 'paragraph', 'data' => ['text' => $text]]]];
    }

    /**
     * Stand-in for the bookmark system: swaps one token, leaves everything else alone.
     */
    private function grabServiceResolves(string $token, string $value): void
    {
        $this->grabService->shouldReceive('replaceGrabs')
            ->andReturnUsing(fn (string $json) => str_replace('[[' . $token . ']]', $value, $json));
    }

    public function testSectionContentIsSnapshottedWithGrabsResolved(): void
    {
        $this->grabServiceResolves('OP_NAME_ONLY', 'ACME LTD');

        $version = new LetterSectionVersion();
        $version->setDefaultContent(self::paragraph('Dear [[OP_NAME_ONLY]]'));
        $section = new LetterInstanceSection();
        $section->setLetterSectionVersion($version);

        $instance = new LetterInstance();
        $instance->addLetterInstanceSection($section);

        $this->sut->snapshot($instance);

        $this->assertSame(self::paragraph('Dear ACME LTD'), $section->getGeneratedContent());
        $this->assertSame(self::paragraph('Dear ACME LTD'), $section->getEffectiveContent());
        $this->assertFalse($section->hasBeenEdited());
        $this->assertSame(self::paragraph('Dear [[OP_NAME_ONLY]]'), $version->getDefaultContentAsArray());
    }

    public function testIssuesTodosAndAppendicesAreSnapshottedToo(): void
    {
        $this->grabServiceResolves('OP_NAME_ONLY', 'ACME LTD');

        $issueVersion = new LetterIssueVersion();
        $issueVersion->setDefaultBodyContent(self::paragraph('Issue for [[OP_NAME_ONLY]]'));
        $issue = new LetterInstanceIssue();
        $issue->setLetterIssueVersion($issueVersion);

        $todoVersion = new LetterTodoVersion();
        $todoVersion->setDescription(self::paragraph('Todo for [[OP_NAME_ONLY]]'));
        $todo = new LetterInstanceTodo();
        $todo->setLetterTodoVersion($todoVersion);

        $appendixVersion = new LetterAppendixVersion();
        $appendixVersion->setDefaultContent(self::paragraph('Appendix for [[OP_NAME_ONLY]]'));
        $appendix = new LetterInstanceAppendix();
        $appendix->setLetterAppendixVersion($appendixVersion);

        $instance = new LetterInstance();
        $instance->addLetterInstanceIssue($issue);
        $instance->addLetterInstanceTodo($todo);
        $instance->addLetterInstanceAppendix($appendix);

        $this->sut->snapshot($instance);

        $this->assertSame(self::paragraph('Issue for ACME LTD'), $issue->getGeneratedContent());
        $this->assertSame(self::paragraph('Todo for ACME LTD'), $todo->getGeneratedDescription());
        $this->assertSame(self::paragraph('Appendix for ACME LTD'), $appendix->getGeneratedContent());
    }

    public function testContentThatResolvesToItselfIsNotStored(): void
    {
        // no grabs in it, so the version default already says everything the snapshot would
        $this->grabServiceResolves('OP_NAME_ONLY', 'ACME LTD');

        $version = new LetterSectionVersion();
        $version->setDefaultContent(self::paragraph('Nothing to resolve here'));
        $section = new LetterInstanceSection();
        $section->setLetterSectionVersion($version);

        $instance = new LetterInstance();
        $instance->addLetterInstanceSection($section);

        $this->sut->snapshot($instance);

        $this->assertNull($section->getGeneratedContent());
        $this->assertSame(self::paragraph('Nothing to resolve here'), $section->getEffectiveContent());
    }

    public function testChildrenWithNoDefaultContentAreLeftAlone(): void
    {
        $this->grabService->shouldNotReceive('replaceGrabs');

        $section = new LetterInstanceSection();
        $section->setLetterSectionVersion(new LetterSectionVersion());
        $appendix = new LetterInstanceAppendix();
        $appendix->setLetterAppendixVersion(new LetterAppendixVersion());

        $instance = new LetterInstance();
        $instance->addLetterInstanceSection($section);
        $instance->addLetterInstanceAppendix($appendix);

        $this->sut->snapshot($instance);

        $this->assertNull($section->getGeneratedContent());
        $this->assertNull($appendix->getGeneratedContent());
    }

    public function testResolvesAgainstTheInstanceContextAndKeepsUnresolvedTokens(): void
    {
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(77);
        $licence->shouldReceive('isNi')->andReturn(false);

        $version = new LetterSectionVersion();
        $version->setDefaultContent(self::paragraph('Dear [[OP_NAME_ONLY]]'));
        $section = new LetterInstanceSection();
        $section->setLetterSectionVersion($version);

        $instance = new LetterInstance();
        $instance->setLicence($licence);
        $instance->addLetterInstanceSection($section);

        $this->grabService->shouldReceive('replaceGrabs')
            ->once()
            ->with(m::type('string'), ['licence' => 77, 'isNi' => false], false)
            ->andReturnUsing(fn (string $json) => $json);

        $this->sut->snapshot($instance);
    }
}
