<?php

declare(strict_types=1);

/**
 * Next Item Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Queue;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Doctrine\ORM\OptimisticLockException;
use Dvsa\Olcs\Api\Domain\Query\Queue\NextItem as Qry;
use Dvsa\Olcs\Api\Domain\QueryHandler\Queue\NextItem;
use Dvsa\Olcs\Api\Domain\Repository\Queue as QueueRepo;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Olcs\Logging\Log\Logger;

/**
 * Next Item Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class NextItemTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        $this->sut = new NextItem();
        $this->mockRepo('Queue', QueueRepo::class);

        parent::setUp();
    }

    public function testHandleQueryWithItem(): void
    {
        $item = m::mock(QueueEntity::class)->makePartial();

        $query = Qry::create(['includeTypes' => ['foo'], 'excludeTypes' => ['bar']]);

        $this->repoMap['Queue']
            ->shouldReceive('getNextItem')
            ->with(['foo'], ['bar'])
            ->once()
            ->andReturn($item);

        $this->assertSame($item, $this->sut->handleQuery($query));
    }

    /**
     * @param $exception
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('exceptionProvider')]
    public function testHandleQueryNoItem(mixed $exception): void
    {
        $query = Qry::create(['includeTypes' => ['foo'], 'excludeTypes' => ['bar']]);

        $this->repoMap['Queue']
            ->shouldReceive('getNextItem')
            ->with(['foo'], ['bar'])
            ->once()
            ->andThrow($exception);

        $this->assertNull($this->sut->handleQuery($query));
    }

    /**
     * @return array
     */
    public static function exceptionProvider(): array
    {
        return [
            [m::mock(NotFoundException::class)],
            [m::mock(OptimisticLockException::class)]
        ];
    }
}
