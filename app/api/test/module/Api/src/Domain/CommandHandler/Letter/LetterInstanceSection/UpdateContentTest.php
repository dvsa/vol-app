<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstanceSection;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceSection\UpdateContent as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstanceSection as Repo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceSection\UpdateContent as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterChangeTestTrait;

/**
 * A sent letter's content is what the operator received, so it can't be changed afterwards
 */
final class UpdateContentTest extends AbstractCommandHandlerTestCase
{
    use LetterChangeTestTrait;

    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstanceSection', Repo::class);

        $this->refData = [\Dvsa\Olcs\Api\Entity\Letter\LetterInstance::STATUS_DRAFT];

        parent::setUp();
    }

    public function testCannotChangeASentLetter(): void
    {
        $letter = new LetterInstance();
        $letter->setStatus(new RefData(LetterInstance::STATUS_SENT));
        $entity = new Entity();
        $entity->setLetterInstance($letter);

        $this->repoMap['LetterInstanceSection']->shouldReceive('fetchUsingId')->andReturn($entity);
        $this->repoMap['LetterInstanceSection']->shouldReceive('save')->never();

        $this->expectException(ValidationException::class);
        $this->sut->handleCommand(Cmd::create(['id' => 1, 'editedContent' => '{"blocks":[]}', 'version' => 1]));
    }

    private function sectionOn(LetterInstance $letter, string $saidBefore = 'Standing wording'): Entity
    {
        $version = new LetterSectionVersion();
        $version->setDefaultContentFromArray(self::editorContent('Standing wording'));
        $entity = new Entity();
        $entity->setLetterInstance($letter);
        $entity->setLetterSectionVersion($version);
        if ($saidBefore !== 'Standing wording') {
            $entity->setEditedContentFromArray(self::editorContent($saidBefore));
        }

        return $entity;
    }

    /**
     * PR review: changing a prepared letter takes it back to draft, so its PDF can't be sent until
     * it's prepared again with the change in it
     */
    public function testChangingAPreparedLetterTakesItBackToDraft(): void
    {
        $letter = $this->letterWithStatus(LetterInstance::STATUS_READY);
        $this->repoMap['LetterInstanceSection']->shouldReceive('fetchUsingId')->andReturn($this->sectionOn($letter));
        $this->repoMap['LetterInstanceSection']->shouldReceive('save')->once();

        $this->sut->handleCommand(Cmd::create(['id' => 1, 'editedContent' => self::editorJson('New wording'), 'version' => 1]));

        $this->assertTrue($letter->isDraft());
        $this->assertLetterRowWritten($letter);
    }

    /**
     * Review: Save posts the editor even when nothing changed; that mustn't throw away a prepared PDF
     * that still matches the letter
     */
    public function testSavingWithNothingChangedLeavesAPreparedLetterPrepared(): void
    {
        $letter = $this->letterWithStatus(LetterInstance::STATUS_READY);
        $this->repoMap['LetterInstanceSection']->shouldReceive('fetchUsingId')->andReturn($this->sectionOn($letter, 'Edited before'));
        $this->repoMap['LetterInstanceSection']->shouldReceive('save')->once();

        $this->sut->handleCommand(Cmd::create(['id' => 1, 'editedContent' => self::editorJson('Edited before', 99), 'version' => 1]));

        $this->assertTrue($letter->isReady());
        $this->assertLetterRowWritten($letter);
    }

    /**
     * Review: an edit to a draft writes the letter's row too, so one that lands while the letter is
     * being prepared fails rather than leaving the prepared PDF without it
     */
    public function testAChangeToADraftLetterWritesTheLetterRowToo(): void
    {
        $letter = $this->letterWithStatus(LetterInstance::STATUS_DRAFT);
        $this->repoMap['LetterInstanceSection']->shouldReceive('fetchUsingId')->andReturn($this->sectionOn($letter));
        $this->repoMap['LetterInstanceSection']->shouldReceive('save')->once();

        $this->sut->handleCommand(Cmd::create(['id' => 1, 'editedContent' => self::editorJson('New wording'), 'version' => 1]));

        $this->assertTrue($letter->isDraft());
        $this->assertLetterRowWritten($letter);
    }

    public function testAClashWithAnotherChangeToTheLetterSaysSo(): void
    {
        $letter = $this->letterWithStatus(LetterInstance::STATUS_DRAFT);
        $this->repoMap['LetterInstanceSection']->shouldReceive('fetchUsingId')->andReturn($this->sectionOn($letter));
        $this->repoMap['LetterInstanceSection']->shouldReceive('save')->once()->andThrow(self::clash());

        $this->expectClashMessage();
        $this->sut->handleCommand(Cmd::create(['id' => 1, 'editedContent' => self::editorJson('New wording'), 'version' => 1]));
    }
}
