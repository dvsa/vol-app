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

/**
 * A sent letter's content is what the operator received, so it can't be changed afterwards
 */
final class UpdateContentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstanceAppendix', Repo::class);

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
}
