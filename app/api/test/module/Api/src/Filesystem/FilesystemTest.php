<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Filesystem;

use Dvsa\Olcs\Api\Filesystem\Filesystem;
use Mockery as m;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;

/**
 * Class FilesystemTest
 * @package Dvsa\OlcsTest\Api\Filesystem
 */
class FilesystemTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testCreateTmpDir(): void
    {
        vfsStream::setup('tmp');
        $sut = new Filesystem();

        $dir = $sut->createTmpDir(vfsStream::url('tmp/'), '', false);

        $this->assertTrue(is_dir($dir));
    }

    public function testCreateTmpDirCleanup(): void
    {
        if (!function_exists('Dvsa\Olcs\Api\Filesystem\register_shutdown_function')) {
            eval(
                'namespace Dvsa\Olcs\Api\Filesystem; function register_shutdown_function ($callback) { $callback(); }'
            );
        }

        vfsStream::setup('tmp');
        $sut = new Filesystem();

        $dir = $sut->createTmpDir(vfsStream::url('tmp/'), '');

        $this->assertFalse(is_dir($dir));
    }

    public function testCreateTmpFile(): void
    {
        vfsStream::setup('tmp');
        $sut = new Filesystem();

        $dir = $sut->createTmpFile(vfsStream::url('tmp/'), '', false);

        $this->assertTrue(file_exists($dir));
    }

    public function testCreateTmpFileCleanup(): void
    {
        if (!function_exists('Dvsa\Olcs\Api\Filesystem\register_shutdown_function')) {
            eval(
                'namespace Dvsa\Olcs\Api\Filesystem; function register_shutdown_function ($callback) { $callback(); }'
            );
        }

        vfsStream::setup('tmp');
        $sut = new Filesystem();

        $dir = $sut->createTmpFile(vfsStream::url('tmp/'), '');

        $this->assertFalse(file_exists($dir));
    }

    public function testCreateTmpFileWithLock(): void
    {
        vfsStream::setup('tmp');

        $sut = new class extends Filesystem {
            protected function getLock($path): LockInterface
            {
                $mock =  m::mock(FlockStore::class, LockInterface::class)
                    ->shouldReceive('isAcquired')
                    ->times(3)
                    ->andReturnFalse()
                    ->shouldReceive('acquire')
                    ->andThrow(LockConflictedException::class)
                    ->times(4)
                    ->getMock();
                return $mock;
            }
        };

        $this->expectException(LockConflictedException::class);

        $dir = $sut->createTmpFile(vfsStream::url('tmp/'), '', false);

        $this->assertFalse(file_exists($dir));
    }
}
