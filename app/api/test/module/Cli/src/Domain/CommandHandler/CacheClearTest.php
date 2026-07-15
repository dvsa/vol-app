<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Cli\Domain\Command\CacheClear as Cmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\CacheClear;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CacheClear::class)]
final class CacheClearTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var \Redis&m\MockInterface
     */
    private $redis;

    #[\Override]
    public function setUp(): void
    {
        $this->redis = m::mock(\Redis::class);

        $this->sut = new CacheClear();

        $this->mockedSmServices['cache.redis.connection'] = $this->redis;

        parent::setUp();
    }

    public function testHandleCommandReturnsMessageWhenNoOperationSpecified(): void
    {
        $command = Cmd::create([]);

        $result = $this->sut->handleCommand($command);

        self::assertSame(
            ['No cache clearing operation specified'],
            $result->toArray()['messages']
        );
    }

    public function testFlushAllDryRunDoesNotClearRedis(): void
    {
        $this->redis
            ->expects('dbSize')
            ->once()
            ->andReturn(75);

        $this->redis->shouldNotReceive('flushDB');

        $command = Cmd::create([
            'flushAll' => true,
            'dryRun' => true,
        ]);

        $result = $this->sut->handleCommand($command);

        self::assertSame(
            ['[DRY RUN] Would flush all Redis cache (75 keys)'],
            $result->toArray()['messages']
        );
    }

    public function testFlushAllClearsRedisAndReportsDeletedCount(): void
    {
        $this->redis
            ->expects('dbSize')
            ->twice()
            ->andReturn(75, 5);

        $this->redis
            ->expects('flushDB')
            ->once()
            ->andReturnTrue();

        $command = Cmd::create([
            'flushAll' => true,
            'dryRun' => false,
        ]);

        $result = $this->sut->handleCommand($command);

        self::assertSame(
            ['Flushed all Redis cache: 70 keys deleted'],
            $result->toArray()['messages']
        );
    }

    public function testUnknownNamespaceDoesNotAccessRedis(): void
    {
        $this->redis->shouldNotReceive('scan');
        $this->redis->shouldNotReceive('del');

        $command = Cmd::create([
            'namespace' => 'not_a_valid_namespace',
            'dryRun' => false,
        ]);

        $result = $this->sut->handleCommand($command);

        self::assertSame(
            [
                'Unknown namespace: not_a_valid_namespace',
                'Total: deleted 0 keys',
            ],
            $result->toArray()['messages']
        );
    }

    public function testRedisExceptionIsConvertedToResultMessage(): void
    {
        $this->redis
            ->expects('dbSize')
            ->once()
            ->andThrow(new \RuntimeException('Redis unavailable'));

        $command = Cmd::create([
            'flushAll' => true,
            'dryRun' => true,
        ]);

        $result = $this->sut->handleCommand($command);

        self::assertSame(
            ['Error clearing cache: Redis unavailable'],
            $result->toArray()['messages']
        );
    }
}