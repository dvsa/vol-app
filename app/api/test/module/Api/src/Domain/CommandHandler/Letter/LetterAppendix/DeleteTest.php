<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterAppendix;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterAppendix\Delete;
use Dvsa\Olcs\Api\Domain\Repository\LetterAppendix as Repo;
use Dvsa\Olcs\Api\Entity\Letter\LetterAppendix as Entity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterAppendix\Delete as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\AbstractDeleteLetterContentTestCase;

final class DeleteTest extends AbstractDeleteLetterContentTestCase
{
    protected string $repoName = 'LetterAppendix';
    protected string $repoClass = Repo::class;
    protected string $entityClass = Entity::class;
    protected string $commandClass = Cmd::class;
    protected string $usedByMethod = 'fetchLetterTypeNamesUsing';
    protected string $referencedMethod = 'isUsedByLetterInstances';
    protected string $blockedMessage = 'Cannot delete this appendix because it is used by letter type: Alpha, Beta. Remove it from the letter type first.';

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new Delete();

        parent::setUp();
    }
}
