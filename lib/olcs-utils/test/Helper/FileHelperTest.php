<?php

namespace Dvsa\OlcsTest\Utils\Helper;

use Dvsa\Olcs\Utils\Helper\FileHelper;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class FileHelperTest extends \PHPUnit\Framework\TestCase
{
    /** @dataProvider dpTestGetExtension */
    public function testGetExtension($path, $expect)
    {
        static::assertEquals($expect, FileHelper::getExtension($path));
    }

    public function dpTestGetExtension()
    {
        return [
            [
                'path' => 'dir/dir/aaa.bbb.ext',
                'expect' => 'ext',
            ],
            [
                'path' => 'dir/dir/.ext',
                'expect' => 'ext',
            ],
            [
                'path' => 'dir/dir/file_without_ext',
                'expect' => '',
            ],
            [
                'path' => null,
                'expect' => '',
            ],
        ];
    }
}
