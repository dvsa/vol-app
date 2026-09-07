<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cache;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\RedisAwareInterface;
use Dvsa\Olcs\Api\Domain\RedisAwareTrait;
use Dvsa\Olcs\Transfer\Command\Cache\Clear as ClearCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;

/**
 * Cache Clear Command Handler
 *
 */
class Clear extends AbstractCommandHandler implements
    RedisAwareInterface,
    ConfigAwareInterface,
    AuthAwareInterface
{
    use RedisAwareTrait;
    use ConfigAwareTrait;
    use AuthAwareTrait;

    /**
     * Namespaces that are a whole PSR-6 pool, mapped to the 'caches' config key defining them.
     *
     * These pools share one Redis connection with the application cache and are kept apart only
     * by the prefix their adapter applies, so clearing one is a matter of scanning that prefix.
     */
    private const array POOL_NAMESPACES = [
        ClearCmd::NAMESPACE_DOCTRINE => 'doctrine-cache',
        ClearCmd::NAMESPACE_JWKS => 'jwks-cache',
    ];

    /**
     * The default-cache pool that every other namespace writes into.
     */
    private const string DEFAULT_CACHE = 'default-cache';

    /**
     * Namespaces whose key prefix is not simply the namespace name.
     *
     * Only CQRS differs: its keys are md5 hashes of the query, so CachingQueryService stamps
     * them with a prefix to make them identifiable, and the separator is an underscore because
     * PSR-6 reserves the colon.
     */
    private const array KEY_PREFIX_OVERRIDES = [
        ClearCmd::NAMESPACE_CQRS => CacheEncryption::CQRS_CACHE_PREFIX,
    ];

    /**
     * Handle cache clear command
     *
     * @param CommandInterface|ClearCmd $command
     * @return Result
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        $dryRun = $command->getDryRun() ?? false;

        if ($command->getFlushAll()) {
            $this->assertUnscopedClearAllowed('flushAll');

            return $this->flushAll($dryRun);
        }

        if ($command->getNamespace()) {
            return $this->clearByNamespace($command->getNamespace(), $dryRun);
        }

        if ($command->getPattern()) {
            $this->assertUnscopedClearAllowed('pattern');

            return $this->clearByPattern($command->getPattern(), $dryRun);
        }

        $this->result->addMessage('No cache clearing operation specified');
        return $this->result;
    }

    /**
     * Guard the two options that can delete keys outside the known namespaces.
     *
     * Both predate this command being reachable over HTTP, where the CLI's interactive
     * confirmation does not apply. The namespace route covers everything the admin UI needs, so
     * the blunter options stay with the system user - in practice the batch:cache-clear command.
     *
     * @throws ForbiddenException
     */
    private function assertUnscopedClearAllowed(string $option): void
    {
        if ($this->isSystemUser()) {
            return;
        }

        throw new ForbiddenException(
            sprintf('The "%s" option is restricted to the system user', $option)
        );
    }

    /**
     * Flush all Redis cache
     *
     * @param bool $dryRun
     * @return Result
     */
    private function flushAll(bool $dryRun): Result
    {
        $redis = $this->getRedis();

        if ($dryRun) {
            $keyCount = $redis->dbSize();
            $this->result->addMessage(sprintf(
                '[DRY RUN] Would flush all Redis cache (%d keys)',
                $keyCount
            ));
            return $this->result;
        }

        $keyCountBefore = $redis->dbSize();
        $redis->flushDB();
        $keyCountAfter = $redis->dbSize();

        $this->result->addMessage(sprintf(
            'Flushed all Redis cache: %d keys deleted',
            $keyCountBefore - $keyCountAfter
        ));
        $this->result->setFlag(ClearCmd::RESULT_FLAG_KEYS_DELETED, $keyCountBefore - $keyCountAfter);

        return $this->result;
    }

    /**
     * Clear cache by namespace(s)
     *
     * @param string $namespaces Comma-separated list
     * @param bool $dryRun
     * @return Result
     * @throws BadRequestException
     */
    private function clearByNamespace(string $namespaces, bool $dryRun): Result
    {
        $namespaceList = array_map(trim(...), explode(',', $namespaces));

        $unknown = array_diff($namespaceList, ClearCmd::NAMESPACES);

        if ($unknown !== []) {
            throw new BadRequestException(sprintf(
                'Unknown cache namespace(s): %s. Available: %s',
                implode(', ', $unknown),
                implode(', ', ClearCmd::NAMESPACES)
            ));
        }

        $totalDeleted = 0;

        foreach ($namespaceList as $namespace) {
            $pattern = $this->resolvePattern($namespace);
            $deleted = $this->deleteByPattern($pattern, $dryRun);
            $totalDeleted += $deleted;

            if ($dryRun) {
                $this->result->addMessage(sprintf(
                    '[DRY RUN] Would delete %d keys from namespace "%s" (pattern: %s)',
                    $deleted,
                    $namespace,
                    $pattern
                ));
            } else {
                $this->result->addMessage(sprintf(
                    'Deleted %d keys from namespace "%s" (pattern: %s)',
                    $deleted,
                    $namespace,
                    $pattern
                ));
            }
        }

        if ($dryRun) {
            $this->result->addMessage(sprintf('[DRY RUN] Total: would delete %d keys', $totalDeleted));
        } else {
            $this->result->addMessage(sprintf('Total: deleted %d keys', $totalDeleted));
        }

        $this->result->setFlag(ClearCmd::RESULT_FLAG_KEYS_DELETED, $totalDeleted);

        return $this->result;
    }

    /**
     * Clear cache by custom pattern
     *
     * @param string $pattern
     * @param bool $dryRun
     * @return Result
     */
    private function clearByPattern(string $pattern, bool $dryRun): Result
    {
        $deleted = $this->deleteByPattern($pattern, $dryRun);

        if ($dryRun) {
            $this->result->addMessage(sprintf(
                '[DRY RUN] Would delete %d keys matching pattern "%s"',
                $deleted,
                $pattern
            ));
        } else {
            $this->result->addMessage(sprintf(
                'Deleted %d keys matching pattern "%s"',
                $deleted,
                $pattern
            ));
        }

        $this->result->setFlag(ClearCmd::RESULT_FLAG_KEYS_DELETED, $deleted);

        return $this->result;
    }

    /**
     * Delete keys matching a pattern using SCAN
     *
     * @param string $pattern
     * @param bool $dryRun
     * @return int Number of keys that would be/were deleted
     */
    private function deleteByPattern(string $pattern, bool $dryRun): int
    {
        $redis = $this->getRedis();
        $iterator = null;
        $count = 0;
        $batchSize = 100;

        // Use SCAN to iterate through keys matching the pattern
        do {
            $keys = $redis->scan($iterator, $pattern, $batchSize);

            if ($keys !== false && !empty($keys)) {
                $count += count($keys);

                if (!$dryRun) {
                    // Delete in batches for efficiency
                    $redis->del($keys);
                }
            }
        } while ($iterator > 0);

        return $count;
    }

    /**
     * Resolve a logical namespace to the Redis key pattern that matches it.
     *
     * A namespace is either a pool of its own (doctrine, jwks) or a prefix within the default
     * cache pool (everything else). Both forms are built from the pool prefix recorded in
     * configuration rather than a literal, so renaming a pool - as the Doctrine ORM 3 upgrade
     * does when it moves doctrine-cache to its own namespace - needs no change here.
     *
     * @param string $namespace one of ClearCmd::NAMESPACES
     * @return string e.g. 'zfcache:sys_param*', 'doctrine-orm3:*'
     */
    private function resolvePattern(string $namespace): string
    {
        if (isset(self::POOL_NAMESPACES[$namespace])) {
            return $this->poolPrefix(self::POOL_NAMESPACES[$namespace]) . ':*';
        }

        $keyPrefix = self::KEY_PREFIX_OVERRIDES[$namespace] ?? $namespace;

        return $this->poolPrefix(self::DEFAULT_CACHE) . ':' . $keyPrefix . '*';
    }

    /**
     * The prefix a cache pool's adapter puts in front of every key it writes.
     *
     * Deliberately mirrors Api\Service\Cache\DefaultCacheFactory, fallback included: the pattern
     * scanned for here has to match the prefix that factory actually applied, so if the two ever
     * disagree the clear silently matches nothing.
     */
    private function poolPrefix(string $pool): string
    {
        return $this->getConfig()['caches'][$pool]['options']['namespace'] ?? 'zfcache';
    }
}
