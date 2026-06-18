<?php

namespace CommonTest\Filter;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Filter\DecompressUploadToTmp;
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
class DecompressUploadToTmpTest extends MockeryTestCase
{
    public function testFilter(): void
    {
        $filename = 'testFile.zip';
        $tmpDir = '/tmp/';
        $extractDir = '/tmp/zipUvf4glz/';

        if (!function_exists('Common\Filter\register_shutdown_function')) {
            eval('namespace Common\Filter; function register_shutdown_function ($callback) { $callback(); }');
        }

        $mockFilter = m::mock(\Laminas\Filter\Decompress::class);
        $mockFilter->shouldReceive('filter')->with($filename);
        $mockFilter->shouldReceive('getAdapterOptions')->andReturn(['target' => '']);
        $mockFilter->shouldReceive('setAdapterOptions')->with(['target' => $extractDir]);

        $mockFileSystem = m::mock(\Common\Filesystem\Filesystem::class);
        $mockFileSystem->shouldReceive('createTmpDir')->with($tmpDir, 'zip')->andReturn($extractDir);
        $mockFileSystem->shouldReceive('remove')->with($extractDir);

        $sut = new DecompressUploadToTmp();
        $sut->setDecompressFilter($mockFilter);
        $sut->setTempRootDir($tmpDir);
        $sut->setFileSystem($mockFileSystem);

        $result = $sut->filter(['tmp_name' => $filename]);

        $this->assertEquals(['tmp_name' => $filename, 'extracted_dir' => $extractDir], $result);
    }
}
