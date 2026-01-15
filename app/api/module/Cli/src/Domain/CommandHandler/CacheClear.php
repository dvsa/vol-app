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
 */
class CacheClear extends AbstractCommandHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    /**
     * Valid cache namespace identifiers from CacheEncryption service
     */
    private const VALID_NAMESPACES = [
        'user_account',
        'sys_param',
        'sys_param_list',
        'translation_key',
        'translation_replacement',
        'storage',
        'secretsmanager',
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
            if (!in_array($namespace, self::VALID_NAMESPACES, true)) {
                $this->result->addMessage(sprintf('Unknown namespace: %s', $namespace));
                continue;
            }

            $pattern = $this->getNamespacePrefix($namespace) . '*';
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
     * Get the cache namespace prefix from configuration
     *
     * @param string $namespace The cache namespace identifier (e.g., 'user_account')
     * @return string The full namespace prefix (e.g., 'zfcache:user_account')
     */
    private function getNamespacePrefix(string $namespace): string
    {
        $options = $this->getCacheOptions();
        $cacheNamespace = $options->getNamespace() ?? 'zfcache';

        return $cacheNamespace . ':' . $namespace;
    }

    /**
     * Get the cache adapter options
     *
     * @return \Laminas\Cache\Storage\Adapter\AdapterOptions
     * @throws \RuntimeException
     */
    private function getCacheOptions(): \Laminas\Cache\Storage\Adapter\AdapterOptions
    {
        $cacheStorage = $this->getCacheStorage();
        return $cacheStorage->getOptions();
    }

    /**
     * Get the underlying cache storage adapter
     *
     * @return RedisAdapter
     * @throws \RuntimeException
     */
    private function getCacheStorage(): RedisAdapter
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

        return $cacheStorage;
    }

    /**
     * Get the underlying Redis resource from CacheEncryption service
     *
     *
     * @return \Redis
     * @throws \RuntimeException
     */
    private function getRedisResource(): \Redis
    {
        $cacheStorage = $this->getCacheStorage();

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
