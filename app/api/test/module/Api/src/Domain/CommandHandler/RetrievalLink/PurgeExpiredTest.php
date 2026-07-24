<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\RetrievalLink;

use Dvsa\Olcs\Api\Domain\Command\RetrievalLink\PurgeExpired as PurgeExpiredCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\RetrievalLink\PurgeExpired;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalLink as RetrievalLinkRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

final class PurgeExpiredTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PurgeExpired();
        $this->mockRepo('RetrievalLink', RetrievalLinkRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $this->repoMap['RetrievalLink']
            ->shouldReceive('deleteExpired')
            ->with(m::type(\DateTimeInterface::class))
            ->once()
            ->andReturn(3);

        $result = $this->sut->handleCommand(PurgeExpiredCmd::create([]));

        self::assertContains('Purged 3 expired retrieval link(s)', $result->getMessages());
    }
}
