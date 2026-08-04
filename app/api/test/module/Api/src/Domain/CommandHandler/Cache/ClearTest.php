<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cache;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversClass;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cache\Clear as Handler;
use Dvsa\Olcs\Transfer\Command\Cache\Clear as Command;

#[CoversClass(Handler::class)]
final class ClearTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var \Redis&m\MockInterface
     */
    private $redis;

    #[\Override]
    public function setUp(): void
    {
        $this->redis = m::mock(\Redis::class);

        $this->sut = new Handler();

        $this->mockedSmServices['config'] = [
            'caches' => [
                'default-cache' => [
                    'options' => [
                        'namespace' => 'zfcache',
                    ],
                ],
            ],
        ];

        $this->mockedSmServices['cache.redis.connection'] = $this->redis;

        parent::setUp();
    }

    public function testHandleCommandReturnsMessageWhenNoOperationSpecified(): void
    {
        $command = Command::create([]);

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

        $command = Command::create([
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

        $command = Command::create([
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

        $command = Command::create([
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

        $command = Command::create([
            'flushAll' => true,
            'dryRun' => true,
        ]);

        $result = $this->sut->handleCommand($command);

        self::assertSame(
            ['Error clearing cache: Redis unavailable'],
            $result->toArray()['messages']
        );
    }

    public function testNamespaceUsesConfiguredCachePrefix(): void
    {
        $this->sut->setConfig([
            'caches' => [
                'default-cache' => [
                    'options' => [
                        'namespace' => 'custom-cache',
                    ],
                ],
            ],
        ]);

        $this->redis
            ->shouldReceive('scan')
            ->once()
            ->withArgs(
                static function (&$iterator, string $pattern, int $count): bool {
                    $iterator = 0;

                    return $pattern === 'custom-cache:user_account*'
                        && $count === 100;
                }
            )
            ->andReturnFalse();

        $this->redis->shouldNotReceive('del');

        $command = Command::create([
            'namespace' => 'user_account',
            'dryRun' => true,
        ]);

        $result = $this->sut->handleCommand($command);

        self::assertSame(
            [
                '[DRY RUN] Would delete 0 keys from namespace "user_account" '
                    . '(pattern: custom-cache:user_account*)',
                '[DRY RUN] Total: would delete 0 keys',
            ],
            $result->toArray()['messages']
        );
    }

    public function testCqrsNamespaceUsesDedicatedPrefix(): void
    {
        $this->redis
            ->shouldReceive('scan')
            ->once()
            ->withArgs(
                static function (&$iterator, string $pattern, int $count): bool {
                    $iterator = 0;

                    return $pattern === 'cqrs:*'
                        && $count === 100;
                }
            )
            ->andReturnFalse();

        $this->redis->shouldNotReceive('del');

        $command = Command::create([
            'namespace' => 'cqrs',
            'dryRun' => true,
        ]);

        $result = $this->sut->handleCommand($command);

        self::assertSame(
            [
                '[DRY RUN] Would delete 0 keys from namespace "cqrs" '
                    . '(pattern: cqrs:*)',
                '[DRY RUN] Total: would delete 0 keys',
            ],
            $result->toArray()['messages']
        );
    }
}
