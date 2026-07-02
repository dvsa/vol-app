<?php

namespace CommonTest\Filter;

use Laminas\Filter\Decompress;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Filter\DecompressToTmp;
use Mockery as m;

/**
 * Class DecompressUploadToTmpTest
 *
 * This test mocks register_shutdown_function in the Common\Filter namespace, this mock will affect other tests in the
 * same namespace.
 *
 * @package CommonTest\Filter
 * @group UnsafeMocking
 */
class DecompressToTmpTest extends MockeryTestCase
{
    public function testFilter(): void
    {
        $filename = 'testFile.zip';
        $tmpDir = '/tmp/';
        $extractDir = '/tmp/zipUvf4glz/';
        $filePath = '/tmp/zipUvf4glz/testFile/';

        if (!function_exists('Common\Filter\register_shutdown_function')) {
            eval('namespace Common\Filter; function register_shutdown_function ($callback) { $callback(); }');
        }

        $mockFilter = m::mock(Decompress::class);
        $mockFilter->shouldReceive('filter')->with($filename)->andReturn($filePath);
        $mockFilter->shouldReceive('setTarget')->with($extractDir);

        $mockFileSystem = m::mock(\Common\Filesystem\Filesystem::class);
        $mockFileSystem->shouldReceive('createTmpDir')->with($tmpDir, 'zip')->andReturn($extractDir);
        $mockFileSystem->shouldReceive('remove')->with($extractDir);

        $mockFilter->shouldReceive('getAdapterOptions')->once()->andReturn([]);
        $mockFilter->shouldReceive('setAdapterOptions')->once()->andReturn(Decompress::class);

        $sut = new DecompressToTmp();
        $sut->setDecompressFilter($mockFilter);
        $sut->setTempRootDir($tmpDir);
        $sut->setFileSystem($mockFileSystem);

        $result = $sut->filter($filename);

        $this->assertEquals($filePath, $result);
    }
}
