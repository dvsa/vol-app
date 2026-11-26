<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging\Subject;

use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Subject\Delete as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\MessagingSubject as Repo;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingSubject;
use Dvsa\Olcs\Transfer\Command\Messaging\Subject\Delete as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

final class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo(Repo::class, Repo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['id' => 7]);
        $subject = m::mock(MessagingSubject::class);

        $this->repoMap[Repo::class]->shouldReceive('fetchById')->once()->with(7)->andReturn($subject);
        $this->repoMap[Repo::class]->shouldReceive('delete')->once()->with($subject);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['id7' => 7], $result->getIds());
        $this->assertSame(['Id 7 deleted'], $result->getMessages());
    }
}
