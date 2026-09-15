<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterType;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterType\SuggestPreviewRecords;
use Dvsa\Olcs\Api\Domain\Repository\LetterSection as LetterSectionRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterType as LetterTypeRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection as LetterSectionEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterType as LetterTypeEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterTypeSection as LetterTypeSectionEntity;
use Dvsa\Olcs\Api\Service\Letter\PreviewRecordSuggester;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\SuggestPreviewRecords as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * SuggestPreviewRecords Test
 */
final class SuggestPreviewRecordsTest extends AbstractCommandHandlerTestCase
{
    private m\MockInterface $suggester;

    public function setUp(): void
    {
        $this->sut = new SuggestPreviewRecords();
        $this->mockRepo('LetterType', LetterTypeRepo::class);
        $this->mockRepo('LetterSection', LetterSectionRepo::class);

        $this->suggester = m::mock(PreviewRecordSuggester::class);
        $this->mockedSmServices[PreviewRecordSuggester::class] = $this->suggester;

        parent::setUp();
    }

    public function testSectionsFromTheCommandBeatTheSavedComposition(): void
    {
        $sectionA = m::mock(LetterSectionEntity::class);
        $sectionB = m::mock(LetterSectionEntity::class);

        $this->repoMap['LetterType']->shouldReceive('fetchById')->with(7)
            ->andReturn(m::mock(LetterTypeEntity::class));
        $this->repoMap['LetterSection']->shouldReceive('fetchById')->with(15)->once()->andReturn($sectionA);
        $this->repoMap['LetterSection']->shouldReceive('fetchById')->with(16)->once()->andReturn($sectionB);

        $this->suggester->shouldReceive('suggest')->with([$sectionA, $sectionB])->once()
            ->andReturn([['dimensions' => ['goodsOrPsv' => 'lcat_gv'], 'record' => null]]);

        $result = $this->sut->handleCommand(Cmd::create(['letterType' => 7, 'sections' => [15, 16]]));

        $this->assertCount(1, $result->getFlag('suggestions'));
    }

    public function testFallsBackToTheSavedCompositionWhenNoSectionsSent(): void
    {
        $section = m::mock(LetterSectionEntity::class);

        $typeSection = m::mock(LetterTypeSectionEntity::class);
        $typeSection->shouldReceive('getLetterSection')->andReturn($section);

        $letterType = m::mock(LetterTypeEntity::class);
        $letterType->shouldReceive('getLetterTypeSections')->andReturn(new ArrayCollection([$typeSection]));

        $this->repoMap['LetterType']->shouldReceive('fetchById')->with(7)->andReturn($letterType);
        $this->repoMap['LetterSection']->shouldNotReceive('fetchById');

        $this->suggester->shouldReceive('suggest')->with([$section])->once()->andReturn([]);

        $result = $this->sut->handleCommand(Cmd::create(['letterType' => 7]));

        $this->assertSame([], $result->getFlag('suggestions'));
    }
}
