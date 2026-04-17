<?php

declare(strict_types=1);

/**
 * Queue Retry Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Queue;

use Dvsa\Olcs\Api\Domain\Command\Queue\Retry as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Queue\Retry;
use Dvsa\Olcs\Api\Domain\Repository\Queue as Repo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 *  Queue Retry Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class RetryTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Retry();
        $this->mockRepo('Queue', Repo::class);

        $this->refData = [
            QueueEntity::STATUS_QUEUED,
        ];

        parent::setUp();
    }

    /**
     * Test handleCommand method
     */
    public function testHandleCommand(): void
    {
        $item = new QueueEntity();
        $command = Cmd::create(['item' => $item, 'retryAfter' => 60, 'lastError' => 'last error']);

        $processAfter = new DateTime();
        $processAfter->add(new \DateInterval('PT60S'));
        $expectedProcessAfter = $processAfter->format(\DateTime::W3C);

        $this->repoMap['Queue']
            ->shouldReceive('save')
            ->once()
            ->with($item);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['Queue item requeued for after ' . $expectedProcessAfter], $result->getMessages());

        $this->assertEquals(QueueEntity::STATUS_QUEUED, $item->getStatus()->getId());
        $this->assertEquals($expectedProcessAfter, $item->getProcessAfterDate()->format(\DateTime::W3C));
        $this->assertEquals('last error', $item->getLastError());
    }
}
