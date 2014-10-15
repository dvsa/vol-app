<?php

namespace OlcsTest\Filesystem;

use Olcs\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;

/**
 * Class FilesystemTest
 * @package OlcsTest\Filesystem
 */
class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateTmpDir()
    {
        vfsStream::setup('tmp');
        $sut = new Filesystem();

        $dir = $sut->createTmpDir(vfsStream::url('tmp/'));

        $this->assertTrue(is_dir($dir));
    }
}
