<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Cli\Domain\Command\CacheClear as CacheClearCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Laminas\Cache\Storage\Adapter\Redis as RedisAdapter;

/**
 * Cache Clear Command Handler
 *
 * Handles Redis cache clearing operations with support for full flush,
 * namespace-based clearing, and pattern-based clearing.
 *
 * Uses the CacheAwareInterface pattern for automatic dependency injection
 * of the CacheEncryption service via AbstractCommandHandler initializer.
 *
 * @author OLCS Team
 */
class CacheClear extends AbstractCommandHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    /**
     * Cache namespace identifiers from CacheEncryption service
     */
    private const NAMESPACES = [
        'user_account',
        'sys_param',
        'sys_param_list',
        'translation_key',
        'translation_replacement',
        'storage',
        'secretsmanager',
    ];

    /**
     * Cache namespace prefixes (zfcache: prefix + identifier)
     */
    private const NAMESPACE_PREFIXES = [
        'user_account' => 'zfcache:user_account',
        'sys_param' => 'zfcache:sys_param',
        'sys_param_list' => 'zfcache:sys_param_list',
        'translation_key' => 'zfcache:translation_key',
        'translation_replacement' => 'zfcache:translation_replacement',
        'storage' => 'zfcache:storage',
        'secretsmanager' => 'zfcache:secretsmanager',
    ];

    /**
     * Handle cache clear command
     *
     * @param CommandInterface|CacheClearCmd $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $dryRun = $command->getDryRun() ?? false;

        try {
            if ($command->getFlushAll()) {
                return $this->flushAll($dryRun);
            }

            if ($command->getNamespace()) {
                return $this->clearByNamespace($command->getNamespace(), $dryRun);
            }

            if ($command->getPattern()) {
                return $this->clearByPattern($command->getPattern(), $dryRun);
            }

            $this->result->addMessage('No cache clearing operation specified');
            return $this->result;
        } catch (\Exception $e) {
            $this->result->addMessage('Error clearing cache: ' . $e->getMessage());
            return $this->result;
        }
    }

    /**
     * Flush all Redis cache
     *
     * @param bool $dryRun
     * @return Result
     */
    private function flushAll(bool $dryRun): Result
    {
        $redis = $this->getRedisResource();

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

        return $this->result;
    }

    /**
     * Clear cache by namespace(s)
     *
     * @param string $namespaces Comma-separated list
     * @param bool $dryRun
     * @return Result
     */
    private function clearByNamespace(string $namespaces, bool $dryRun): Result
    {
        $namespaceList = array_map('trim', explode(',', $namespaces));
        $totalDeleted = 0;

        foreach ($namespaceList as $namespace) {
            if (!isset(self::NAMESPACE_PREFIXES[$namespace])) {
                $this->result->addMessage(sprintf('Unknown namespace: %s', $namespace));
                continue;
            }

            $pattern = self::NAMESPACE_PREFIXES[$namespace] . '*';
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
        $redis = $this->getRedisResource();
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
     * Get the underlying Redis resource from CacheEncryption service
     *
     * The CacheEncryption service wraps a StorageInterface (Redis adapter).
     * We need to access the underlying Redis connection for direct operations
     * like FLUSHDB and SCAN that aren't exposed by the abstraction layers.
     *
     * @return \Redis
     * @throws \RuntimeException
     */
    private function getRedisResource(): \Redis
    {
        // CacheEncryption has a private $cache property of type StorageInterface
        // We need to use reflection to access it since there's no getter
        $reflection = new \ReflectionClass($this->getCache());
        $cacheProperty = $reflection->getProperty('cache');
        $cacheProperty->setAccessible(true);
        $cacheStorage = $cacheProperty->getValue($this->getCache());

        if (!$cacheStorage instanceof RedisAdapter) {
            throw new \RuntimeException('Cache adapter is not Redis');
        }

        // Get resource manager and ID from options
        $options = $cacheStorage->getOptions();
        $resourceManager = $options->getResourceManager();
        $resourceId = $options->getResourceId();

        // Get the actual Redis resource with the ID
        $redis = $resourceManager->getResource($resourceId);

        if (!$redis instanceof \Redis) {
            throw new \RuntimeException('Could not get Redis resource');
        }

        return $redis;
    }
}
