<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterSection;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterSection\Delete;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\LetterSection as Repo;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection as Entity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSection\Delete as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\AbstractDeleteLetterContentTestCase;

final class DeleteTest extends AbstractDeleteLetterContentTestCase
{
    protected string $repoName = 'LetterSection';
    protected string $repoClass = Repo::class;
    protected string $entityClass = Entity::class;
    protected string $commandClass = Cmd::class;
    protected string $usedByMethod = 'fetchLetterTypeNamesUsing';
    protected string $referencedMethod = 'isUsedByLetterInstances';
    protected string $blockedMessage = 'Cannot delete this section because it is used by letter type: Alpha, Beta. Remove it from the letter type first.';

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new Delete();

        parent::setUp();
    }

    public function testIssuesPlaceholderCannotBeDeleted(): void
    {
        $entity = new Entity();
        $entity->setSectionKey('__ISSUES__');

        $repo = $this->repoMap['LetterSection'];
        $repo->expects('fetchById')->with(self::ID)->andReturn($entity);
        $repo->shouldNotReceive('fetchLetterTypeNamesUsing', 'softDelete', 'hardDelete');

        try {
            $this->sut->handleCommand($this->command());
            $this->fail('Expected a ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame(
                ['letterDelete' => 'The __ISSUES__ placeholder section cannot be deleted'],
                $e->getMessages()
            );
        }
    }
}
