<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Helper;

use Dvsa\Olcs\Utils\Helper\FileHelper;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
final class FileHelperTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetExtension')]
    public function testGetExtension($path, $expect)
    {
        $this->assertEquals($expect, FileHelper::getExtension($path));
    }

    public static function dpTestGetExtension(): \Iterator
    {
        yield [
            'path' => 'dir/dir/aaa.bbb.ext',
            'expect' => 'ext',
        ];
        yield [
            'path' => 'dir/dir/.ext',
            'expect' => 'ext',
        ];
        yield [
            'path' => 'dir/dir/file_without_ext',
            'expect' => '',
        ];
        yield [
            'path' => null,
            'expect' => '',
        ];
    }
}
