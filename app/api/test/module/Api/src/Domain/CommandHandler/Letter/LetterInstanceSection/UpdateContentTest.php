<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstanceSection;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceSection\UpdateContent as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstanceSection as Repo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceSection\UpdateContent as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * A sent letter's content is what the operator received, so it can't be changed afterwards
 */
final class UpdateContentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstanceSection', Repo::class);

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
}
