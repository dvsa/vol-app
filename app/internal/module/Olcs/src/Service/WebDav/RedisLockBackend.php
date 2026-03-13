<?php

declare(strict_types=1);

namespace Olcs\Service\WebDav;

use Sabre\DAV\Locks\Backend\AbstractBackend;
use Sabre\DAV\Locks\LockInfo;

class RedisLockBackend extends AbstractBackend
{
    private const KEY_PREFIX = 'webdav_lock:';

    public function __construct(
        private readonly \Redis $redis,
    ) {
    }

    #[\Override]
    public function getLocks($uri, $returnChildLocks): array
    {
        $locks = [];

        // Check for an exact match
        $data = $this->redis->get(self::KEY_PREFIX . $uri);
        if ($data !== false) {
            $lockInfo = unserialize($data, ['allowed_classes' => [LockInfo::class]]);
            if ($lockInfo instanceof LockInfo) {
                $locks[] = $lockInfo;
            }
        }

        if ($returnChildLocks) {
            $pattern = self::KEY_PREFIX . $uri . '/*';
            $keys = [];
            $cursor = null;
            do {
                $result = $this->redis->scan($cursor, $pattern, 100);
                if ($result !== false) {
                    $keys = array_merge($keys, $result);
                }
            } while ($cursor > 0);
            foreach ($keys as $key) {
                $data = $this->redis->get($key);
                if ($data !== false) {
                    $lockInfo = unserialize($data, ['allowed_classes' => [LockInfo::class]]);
                    if ($lockInfo instanceof LockInfo) {
                        $locks[] = $lockInfo;
                    }
                }
            }
        }

        // Also check parent paths for depth-infinity locks
        $parts = explode('/', trim($uri, '/'));
        $currentPath = '';
        foreach ($parts as $part) {
            $currentPath .= '/' . $part;
            if ($currentPath === '/' . trim($uri, '/')) {
                break;
            }
            $data = $this->redis->get(self::KEY_PREFIX . $currentPath);
            if ($data !== false) {
                $lockInfo = unserialize($data, ['allowed_classes' => [LockInfo::class]]);
                if ($lockInfo instanceof LockInfo && $lockInfo->depth !== 0) {
                    $locks[] = $lockInfo;
                }
            }
        }

        return $locks;
    }

    #[\Override]
    public function lock($uri, LockInfo $lockInfo): bool
    {
        $lockInfo->uri = $uri;
        $key = self::KEY_PREFIX . $uri;
        $ttl = max($lockInfo->timeout, 1);

        return $this->redis->setex($key, $ttl, serialize($lockInfo));
    }

    #[\Override]
    public function unlock($uri, LockInfo $lockInfo): bool
    {
        $key = self::KEY_PREFIX . $uri;
        $existing = $this->redis->get($key);

        if ($existing === false) {
            return false;
        }

        $existingLock = unserialize($existing, ['allowed_classes' => [LockInfo::class]]);
        if (!$existingLock instanceof LockInfo || $existingLock->token !== $lockInfo->token) {
            return false;
        }

        return (bool) $this->redis->del($key);
    }
}
