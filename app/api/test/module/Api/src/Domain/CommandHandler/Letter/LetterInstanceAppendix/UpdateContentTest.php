<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstanceAppendix;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceAppendix\UpdateContent as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstanceAppendix as Repo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceAppendix as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceAppendix\UpdateContent as Cmd;
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
        $this->mockRepo('LetterInstanceAppendix', Repo::class);

        $this->refData = [\Dvsa\Olcs\Api\Entity\Letter\LetterInstance::STATUS_DRAFT];

        parent::setUp();
    }

    public function testCannotChangeASentLetter(): void
    {
        $letter = new LetterInstance();
        $letter->setStatus(new RefData(LetterInstance::STATUS_SENT));
        $version = new \Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion();
        $version->setAppendixType('editable');
        $entity = new Entity();
        $entity->setLetterInstance($letter);
        $entity->setLetterAppendixVersion($version);

        $this->repoMap['LetterInstanceAppendix']->shouldReceive('fetchUsingId')->andReturn($entity);
        $this->repoMap['LetterInstanceAppendix']->shouldReceive('save')->never();

        $this->expectException(ValidationException::class);
        $this->sut->handleCommand(Cmd::create(['id' => 1, 'editedContent' => '{"blocks":[]}', 'version' => 1]));
    }

    private function appendixOn(LetterInstance $letter): Entity
    {
        $version = new \Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion();
        $version->setAppendixType('editable');
        $version->setDefaultContentFromArray(self::editorContent('Standing wording'));
        $entity = new Entity();
        $entity->setLetterInstance($letter);
        $entity->setLetterAppendixVersion($version);

        return $entity;
    }

    /**
     * PR review: changing a prepared letter takes it back to draft
     */
    public function testChangingAPreparedLetterTakesItBackToDraft(): void
    {
        $letter = $this->letterWithStatus(LetterInstance::STATUS_READY);
        $this->repoMap['LetterInstanceAppendix']->shouldReceive('fetchUsingId')->andReturn($this->appendixOn($letter));
        $this->repoMap['LetterInstanceAppendix']->shouldReceive('save')->once();

        $this->sut->handleCommand(Cmd::create(['id' => 1, 'editedContent' => self::editorJson('New wording'), 'version' => 1]));

        $this->assertTrue($letter->isDraft());
        $this->assertLetterRowWritten($letter);
    }

    public function testSavingWithNothingChangedLeavesAPreparedLetterPrepared(): void
    {
        $letter = $this->letterWithStatus(LetterInstance::STATUS_READY);
        $this->repoMap['LetterInstanceAppendix']->shouldReceive('fetchUsingId')->andReturn($this->appendixOn($letter));
        $this->repoMap['LetterInstanceAppendix']->shouldReceive('save')->once();

        $this->sut->handleCommand(Cmd::create(['id' => 1, 'editedContent' => self::editorJson('Standing wording', 5), 'version' => 1]));

        $this->assertTrue($letter->isReady());
        $this->assertLetterRowWritten($letter);
    }

    public function testAClashWithAnotherChangeToTheLetterSaysSo(): void
    {
        $letter = $this->letterWithStatus(LetterInstance::STATUS_DRAFT);
        $this->repoMap['LetterInstanceAppendix']->shouldReceive('fetchUsingId')->andReturn($this->appendixOn($letter));
        $this->repoMap['LetterInstanceAppendix']->shouldReceive('save')->once()->andThrow(self::clash());

        $this->expectClashMessage();
        $this->sut->handleCommand(Cmd::create(['id' => 1, 'editedContent' => self::editorJson('New wording'), 'version' => 1]));
    }
}
