<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterTodo;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterTodo\Delete;
use Dvsa\Olcs\Api\Domain\Repository\LetterTodo as Repo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodo as Entity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTodo\Delete as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\AbstractDeleteLetterContentTestCase;

final class DeleteTest extends AbstractDeleteLetterContentTestCase
{
    protected string $repoName = 'LetterTodo';
    protected string $repoClass = Repo::class;
    protected string $entityClass = Entity::class;
    protected string $commandClass = Cmd::class;
    protected string $usedByMethod = 'fetchLiveIssueKeysUsing';
    protected string $referencedMethod = 'isReferenced';
    protected string $blockedMessage = 'Cannot delete this to-do because it is used by issue: Alpha, Beta. Remove it from the issue first.';

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new Delete();

        parent::setUp();
    }
}
