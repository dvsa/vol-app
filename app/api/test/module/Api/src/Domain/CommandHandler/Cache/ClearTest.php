<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cache;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cache\Clear as Handler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\Cache\Clear as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Handler::class)]
final class ClearTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var \Redis&m\MockInterface
     */
    private $redis;

    /**
     * @var User&m\MockInterface
     */
    private $currentUser;

    #[\Override]
    public function setUp(): void
    {
        $this->redis = m::mock(\Redis::class);

        $this->sut = new Handler();

        $this->currentUser = m::mock(User::class);

        $authService = m::mock(AuthorizationService::class);
        $authService->shouldReceive('getIdentity->getUser')->andReturn($this->currentUser);
        $this->mockedSmServices[AuthorizationService::class] = $authService;

        $this->mockedSmServices['config'] = [
            'caches' => [
                'default-cache' => [
                    'options' => [
                        'namespace' => 'zfcache',
                    ],
                ],
                'doctrine-cache' => [
                    'options' => [
                        'namespace' => 'doctrine',
                    ],
                ],
                'jwks-cache' => [
                    'options' => [
                        'namespace' => 'jwks',
                    ],
                ],
            ],
        ];

        $this->mockedSmServices['cache.redis.connection'] = $this->redis;

        parent::setUp();
    }

    private function givenSystemUser(bool $isSystemUser = true): void
    {
        $this->currentUser->shouldReceive('isSystemUser')->andReturn($isSystemUser);
    }

    /**
     * Assert a single SCAN happens for $pattern and report no matches.
     */
    private function expectScanFor(string $pattern): void
    {
        $this->redis
            ->shouldReceive('scan')
            ->once()
            ->withArgs(
                static function (&$iterator, string $actualPattern, int $count) use ($pattern): bool {
                    $iterator = 0;

                    return $actualPattern === $pattern
                        && $count === 100;
                }
            )
            ->andReturnFalse();

        $this->redis->shouldNotReceive('del');
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
        $this->givenSystemUser();

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
        $this->givenSystemUser();

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
        self::assertSame(70, $result->toArray()['flags'][Command::RESULT_FLAG_KEYS_DELETED]);
    }

    /**
     * flushAll and pattern can delete keys outside the known namespaces, so they stay with the
     * system user (the CLI) rather than being exposed to every system admin over HTTP.
     */
    #[DataProvider('unscopedOptionProvider')]
    public function testUnscopedOptionsAreForbiddenForNonSystemUsers(array $commandData, string $option): void
    {
        $this->givenSystemUser(false);

        $this->redis->shouldNotReceive('scan');
        $this->redis->shouldNotReceive('del');
        $this->redis->shouldNotReceive('flushDB');

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(sprintf('The "%s" option is restricted to the system user', $option));

        $this->sut->handleCommand(Command::create($commandData));
    }

    public static function unscopedOptionProvider(): array
    {
        return [
            'flushAll' => [['flushAll' => true, 'dryRun' => false], 'flushAll'],
            'pattern' => [['pattern' => 'zfcache:*', 'dryRun' => false], 'pattern'],
        ];
    }

    public function testNamespaceClearIsAllowedForNonSystemUsers(): void
    {
        $this->givenSystemUser(false);

        $this->expectScanFor('zfcache:sys_param*');

        $result = $this->sut->handleCommand(Command::create([
            'namespace' => 'sys_param',
            'dryRun' => true,
        ]));

        self::assertContains('[DRY RUN] Total: would delete 0 keys', $result->toArray()['messages']);
    }

    public function testUnknownNamespaceIsRejectedWithoutTouchingRedis(): void
    {
        $this->redis->shouldNotReceive('scan');
        $this->redis->shouldNotReceive('del');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Unknown cache namespace(s): not_a_valid_namespace');

        $this->sut->handleCommand(Command::create([
            'namespace' => 'not_a_valid_namespace',
            'dryRun' => false,
        ]));
    }

    /**
     * A Redis failure must reach the caller. Folding it into a Result message would return 200,
     * which the internal Clear cache page reads as success.
     */
    public function testRedisExceptionPropagates(): void
    {
        $this->givenSystemUser();

        $this->redis
            ->expects('dbSize')
            ->once()
            ->andThrow(new \RuntimeException('Redis unavailable'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Redis unavailable');

        $this->sut->handleCommand(Command::create([
            'flushAll' => true,
            'dryRun' => true,
        ]));
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

        $this->expectScanFor('custom-cache:user_account*');

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

    /**
     * CQRS entries live in the default pool like everything else, so the pool prefix has to stay
     * on the front of the pattern. Scanning a bare "cqrs*" matches nothing at all.
     */
    public function testCqrsNamespaceScansTheDefaultPoolWithTheSharedPrefix(): void
    {
        $this->expectScanFor('zfcache:cqrs_*');

        $result = $this->sut->handleCommand(Command::create([
            'namespace' => 'cqrs',
            'dryRun' => true,
        ]));

        self::assertSame(
            [
                '[DRY RUN] Would delete 0 keys from namespace "cqrs" '
                    . '(pattern: zfcache:cqrs_*)',
                '[DRY RUN] Total: would delete 0 keys',
            ],
            $result->toArray()['messages']
        );
    }

    /**
     * Doctrine and JWKS are pools of their own rather than prefixes inside the default pool, so
     * clearing them scans the pool prefix alone.
     */
    #[DataProvider('poolNamespaceProvider')]
    public function testPoolNamespacesScanTheirOwnPrefix(string $namespace, string $expectedPattern): void
    {
        $this->expectScanFor($expectedPattern);

        $result = $this->sut->handleCommand(Command::create([
            'namespace' => $namespace,
            'dryRun' => true,
        ]));

        self::assertSame(
            [
                sprintf(
                    '[DRY RUN] Would delete 0 keys from namespace "%s" (pattern: %s)',
                    $namespace,
                    $expectedPattern
                ),
                '[DRY RUN] Total: would delete 0 keys',
            ],
            $result->toArray()['messages']
        );
    }

    public static function poolNamespaceProvider(): array
    {
        return [
            'doctrine' => ['doctrine', 'doctrine:*'],
            'jwks' => ['jwks', 'jwks:*'],
        ];
    }

    /**
     * Pool prefixes are read from configuration, never hardcoded. The Doctrine ORM 3 upgrade
     * renames doctrine-cache's namespace to bust ORM 2 era entries, and this command has to
     * follow it without being edited.
     */
    public function testPoolNamespaceFollowsAConfiguredRename(): void
    {
        $this->sut->setConfig([
            'caches' => [
                'default-cache' => ['options' => ['namespace' => 'zfcache']],
                'doctrine-cache' => ['options' => ['namespace' => 'doctrine-orm3']],
            ],
        ]);

        $this->expectScanFor('doctrine-orm3:*');

        $result = $this->sut->handleCommand(Command::create([
            'namespace' => 'doctrine',
            'dryRun' => true,
        ]));

        self::assertContains(
            '[DRY RUN] Would delete 0 keys from namespace "doctrine" (pattern: doctrine-orm3:*)',
            $result->toArray()['messages']
        );
    }

    public function testMultipleNamespacesAreClearedAndCountsAggregated(): void
    {
        $patterns = [];

        $this->redis
            ->shouldReceive('scan')
            ->twice()
            ->withArgs(
                static function (&$iterator, string $pattern, int $count) use (&$patterns): bool {
                    $iterator = 0;
                    $patterns[] = $pattern;

                    return $count === 100;
                }
            )
            ->andReturn(['key-a', 'key-b'], ['key-c']);

        $this->redis->expects('del')->with(['key-a', 'key-b'])->once();
        $this->redis->expects('del')->with(['key-c'])->once();

        $result = $this->sut->handleCommand(Command::create([
            'namespace' => 'translation_key, cqrs',
            'dryRun' => false,
        ]));

        self::assertSame(['zfcache:translation_key*', 'zfcache:cqrs_*'], $patterns);
        self::assertContains('Total: deleted 3 keys', $result->toArray()['messages']);
        self::assertSame(3, $result->toArray()['flags'][Command::RESULT_FLAG_KEYS_DELETED]);
    }

    /**
     * Pool prefixes are read from configuration and fall back to the default pool when absent,
     * so a pool that quietly loses its config entry would have this command scanning the wrong
     * keys rather than failing. Assert the pools it relies on are actually declared, and that
     * none of them shares the default pool's prefix - clearing Doctrine must not take the whole
     * application cache with it.
     */
    public function testPoolNamespacesAreDeclaredInApplicationConfig(): void
    {
        $config = require dirname(__DIR__, 7) . '/config/autoload/config.global.php';

        $defaultPrefix = $config['caches']['default-cache']['options']['namespace'];
        $pools = (new \ReflectionClass(Handler::class))->getConstant('POOL_NAMESPACES');

        self::assertNotEmpty($pools);

        foreach ($pools as $namespace => $pool) {
            self::assertArrayHasKey(
                $pool,
                $config['caches'],
                sprintf('namespace "%s" clears pool "%s", which is not configured', $namespace, $pool)
            );

            $prefix = $config['caches'][$pool]['options']['namespace'] ?? null;

            self::assertNotEmpty($prefix, sprintf('pool "%s" has no namespace', $pool));
            self::assertNotSame(
                $defaultPrefix,
                $prefix,
                sprintf('pool "%s" shares the default cache prefix, so clearing it would clear everything', $pool)
            );
        }
    }

    /**
     * Guards against the namespace vocabulary and the handler's resolution drifting apart: every
     * value the command advertises has to produce a pattern.
     */
    public function testEveryAdvertisedNamespaceResolvesToAPattern(): void
    {
        $patterns = [];

        $this->redis
            ->shouldReceive('scan')
            ->times(count(Command::NAMESPACES))
            ->withArgs(
                static function (&$iterator, string $pattern, int $count) use (&$patterns): bool {
                    $iterator = 0;
                    $patterns[] = $pattern;

                    return true;
                }
            )
            ->andReturnFalse();

        $this->sut->handleCommand(Command::create([
            'namespace' => implode(',', Command::NAMESPACES),
            'dryRun' => true,
        ]));

        self::assertCount(count(Command::NAMESPACES), $patterns);
        self::assertSame($patterns, array_unique($patterns), 'namespaces must not share a pattern');

        foreach ($patterns as $pattern) {
            self::assertStringEndsWith('*', $pattern);
            self::assertStringNotContainsString('::', $pattern);
        }
    }
}
