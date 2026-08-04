<?php

declare(strict_types=1);

namespace CommonTest\Filesystem;

use Common\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;
use Mockery as m;

/**
 * Class FilesystemTest
 * @package CommonTest\Filesystem
 */
final class FilesystemTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateTmpDir(): void
    {
        vfsStream::setup('tmp');
        $sut = new Filesystem();

        $dir = $sut->createTmpDir(vfsStream::url('tmp/'));

        $this->assertDirectoryExists($dir);
    }

    public function testCreateTmpFile(): void
    {
        vfsStream::setup('tmp');
        $sut = new Filesystem();

        $dir = $sut->createTmpFile(vfsStream::url('tmp/'));

        $this->assertFileExists($dir);
    }

    public function testCreateTmpFileWithLock(): void
    {
        vfsStream::setup('tmp');

        $sut = new class extends Filesystem {
            #[\Override]
            protected function getLock(string $path): LockInterface
            {
                return m::mock(FlockStore::class, LockInterface::class)
                    ->shouldReceive('acquire')
                    ->andThrow(LockConflictedException::class)
                    ->times(3)
                    ->getMock();
            }
        };

        $this->expectException(LockConflictedException::class);

        $dir = $sut->createTmpFile(vfsStream::url('tmp/'), '');

        $this->assertFileDoesNotExist($dir);
    }
}
