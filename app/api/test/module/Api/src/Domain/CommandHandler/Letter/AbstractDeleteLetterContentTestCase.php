<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Shared cases for deleting letter appendices, issues, to-dos and sections: blocked while in use,
 * soft-deleted while generated letters point at it, otherwise hard-deleted.
 */
abstract class AbstractDeleteLetterContentTestCase extends AbstractCommandHandlerTestCase
{
    protected const ID = 99;

    protected string $repoName;
    protected string $repoClass;
    protected string $entityClass;
    protected string $commandClass;
    protected string $usedByMethod;
    protected string $referencedMethod;
    protected string $blockedMessage;

    #[\Override]
    public function setUp(): void
    {
        $this->mockRepo($this->repoName, $this->repoClass);

        parent::setUp();
    }

    public function testRunsInATransaction(): void
    {
        $this->assertInstanceOf(TransactionedInterface::class, $this->sut);
    }

    public function testBlockedWhileInUse(): void
    {
        $repo = $this->repoMap[$this->repoName];
        $repo->expects('fetchById')->with(self::ID)->andReturn($this->entity());
        $repo->expects($this->usedByMethod)->with(self::ID)->andReturn(['Alpha', 'Beta']);
        $repo->shouldNotReceive('softDelete', 'hardDelete');

        try {
            $this->sut->handleCommand($this->command());
            $this->fail('Expected a ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame(['letterDelete' => $this->blockedMessage], $e->getMessages());
        }
    }

    public function testSoftDeletedWhileGeneratedLettersUseIt(): void
    {
        $entity = $this->entity();

        $repo = $this->repoMap[$this->repoName];
        $repo->expects('fetchById')->with(self::ID)->andReturn($entity);
        $repo->expects($this->usedByMethod)->with(self::ID)->andReturn([]);
        $repo->expects($this->referencedMethod)->with(self::ID)->andReturnTrue();
        $repo->expects('softDelete')->with($entity);
        $repo->shouldNotReceive('hardDelete');

        $this->assertDeleted($this->sut->handleCommand($this->command()));
    }

    public function testHardDeletedWhenNothingUsesIt(): void
    {
        $repo = $this->repoMap[$this->repoName];
        $repo->expects('fetchById')->with(self::ID)->andReturn($this->entity());
        $repo->expects($this->usedByMethod)->with(self::ID)->andReturn([]);
        $repo->expects($this->referencedMethod)->with(self::ID)->andReturnFalse();
        $repo->expects('hardDelete')->with(self::ID);
        $repo->shouldNotReceive('softDelete');

        $this->assertDeleted($this->sut->handleCommand($this->command()));
    }

    public function testAlreadyGone(): void
    {
        $repo = $this->repoMap[$this->repoName];
        $repo->expects('fetchById')->with(self::ID)->andThrow(new NotFoundException('Entity not found'));
        $repo->shouldNotReceive('softDelete', 'hardDelete');

        $result = $this->sut->handleCommand($this->command());

        $this->assertSame(['Id 99 not found'], $result->getMessages());
    }

    protected function command(): mixed
    {
        return $this->commandClass::create(['id' => self::ID]);
    }

    protected function entity(): object
    {
        return new $this->entityClass();
    }

    private function assertDeleted(Result $result): void
    {
        $this->assertSame(['id99' => self::ID], $result->getIds());
        $this->assertSame(['Id 99 deleted'], $result->getMessages());
    }
}
