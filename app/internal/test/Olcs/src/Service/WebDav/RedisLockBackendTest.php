<?php

declare(strict_types=1);

namespace OlcsTest\Service\WebDav;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Olcs\Service\WebDav\RedisLockBackend;
use Sabre\DAV\Locks\LockInfo;

#[\PHPUnit\Framework\Attributes\CoversClass(RedisLockBackend::class)]
class RedisLockBackendTest extends MockeryTestCase
{
    private \Redis&MockInterface $redis;
    private RedisLockBackend $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis = Mockery::mock(\Redis::class);
        $this->sut = new RedisLockBackend($this->redis);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lockCallsRedisSetexAndReturnsTrue(): void
    {
        $lockInfo = new LockInfo();
        $lockInfo->token = 'test-token-123';
        $lockInfo->timeout = 300;

        $uri = '/documents/test.rtf';

        $this->redis
            ->shouldReceive('setex')
            ->once()
            ->with('webdav_lock:' . $uri, 300, Mockery::type('string'))
            ->andReturn(true);

        $result = $this->sut->lock($uri, $lockInfo);

        $this->assertTrue($result);
        $this->assertEquals($uri, $lockInfo->uri);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lockUsesMinimumTtlOfOneWhenTimeoutIsZero(): void
    {
        $lockInfo = new LockInfo();
        $lockInfo->token = 'test-token-123';
        $lockInfo->timeout = 0;

        $uri = '/documents/test.rtf';

        $this->redis
            ->shouldReceive('setex')
            ->once()
            ->with('webdav_lock:' . $uri, 1, Mockery::type('string'))
            ->andReturn(true);

        $result = $this->sut->lock($uri, $lockInfo);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unlockDeletesKeyWhenTokenMatches(): void
    {
        $uri = '/documents/test.rtf';

        $existingLock = new LockInfo();
        $existingLock->token = 'matching-token';
        $existingLock->uri = $uri;

        $requestLock = new LockInfo();
        $requestLock->token = 'matching-token';

        $this->redis
            ->shouldReceive('get')
            ->once()
            ->with('webdav_lock:' . $uri)
            ->andReturn(serialize($existingLock));

        $this->redis
            ->shouldReceive('del')
            ->once()
            ->with('webdav_lock:' . $uri)
            ->andReturn(1);

        $result = $this->sut->unlock($uri, $requestLock);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unlockReturnsFalseWhenTokenDoesNotMatch(): void
    {
        $uri = '/documents/test.rtf';

        $existingLock = new LockInfo();
        $existingLock->token = 'existing-token';
        $existingLock->uri = $uri;

        $requestLock = new LockInfo();
        $requestLock->token = 'different-token';

        $this->redis
            ->shouldReceive('get')
            ->once()
            ->with('webdav_lock:' . $uri)
            ->andReturn(serialize($existingLock));

        $result = $this->sut->unlock($uri, $requestLock);

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unlockReturnsFalseWhenKeyDoesNotExist(): void
    {
        $uri = '/documents/test.rtf';

        $requestLock = new LockInfo();
        $requestLock->token = 'any-token';

        $this->redis
            ->shouldReceive('get')
            ->once()
            ->with('webdav_lock:' . $uri)
            ->andReturn(false);

        $result = $this->sut->unlock($uri, $requestLock);

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getLocksReturnsExactMatchLock(): void
    {
        $uri = '/documents/test.rtf';

        $lockInfo = new LockInfo();
        $lockInfo->token = 'lock-token';
        $lockInfo->uri = $uri;

        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:' . $uri)
            ->andReturn(serialize($lockInfo));

        // Parent path lookup for /documents
        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:/documents')
            ->andReturn(false);

        $result = $this->sut->getLocks($uri, false);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(LockInfo::class, $result[0]);
        $this->assertEquals('lock-token', $result[0]->token);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getLocksReturnsEmptyArrayWhenNoLocksExist(): void
    {
        $uri = '/documents/test.rtf';

        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:' . $uri)
            ->andReturn(false);

        // Parent path lookup for /documents
        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:/documents')
            ->andReturn(false);

        $result = $this->sut->getLocks($uri, false);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getLocksIncludesChildLocksWhenRequested(): void
    {
        $uri = '/documents';

        $parentLock = new LockInfo();
        $parentLock->token = 'parent-lock';
        $parentLock->uri = $uri;

        $childLock = new LockInfo();
        $childLock->token = 'child-lock';
        $childLock->uri = $uri . '/test.rtf';

        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:' . $uri)
            ->andReturn(serialize($parentLock));

        $this->redis
            ->shouldReceive('scan')
            ->once()
            ->andReturnUsing(function (&$cursor, $pattern, $count) {
                $cursor = 0;
                return ['webdav_lock:/documents/test.rtf'];
            });

        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:/documents/test.rtf')
            ->andReturn(serialize($childLock));

        $result = $this->sut->getLocks($uri, true);

        $this->assertCount(2, $result);
        $this->assertEquals('parent-lock', $result[0]->token);
        $this->assertEquals('child-lock', $result[1]->token);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getLocksIncludesParentDepthInfinityLocks(): void
    {
        $uri = '/documents/subdir/test.rtf';

        $parentLock = new LockInfo();
        $parentLock->token = 'parent-depth-infinity';
        $parentLock->uri = '/documents';
        $parentLock->depth = \Sabre\DAV\Server::DEPTH_INFINITY;

        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:' . $uri)
            ->andReturn(false);

        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:/documents')
            ->andReturn(serialize($parentLock));

        // Parent path lookup for /documents/subdir
        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:/documents/subdir')
            ->andReturn(false);

        $result = $this->sut->getLocks($uri, false);

        $this->assertCount(1, $result);
        $this->assertEquals('parent-depth-infinity', $result[0]->token);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getLocksExcludesParentDepthZeroLocks(): void
    {
        $uri = '/documents/subdir/test.rtf';

        $parentLock = new LockInfo();
        $parentLock->token = 'parent-depth-zero';
        $parentLock->uri = '/documents';
        $parentLock->depth = 0;

        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:' . $uri)
            ->andReturn(false);

        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:/documents')
            ->andReturn(serialize($parentLock));

        // Parent path lookup for /documents/subdir
        $this->redis
            ->shouldReceive('get')
            ->with('webdav_lock:/documents/subdir')
            ->andReturn(false);

        $result = $this->sut->getLocks($uri, false);

        $this->assertEmpty($result);
    }
}
