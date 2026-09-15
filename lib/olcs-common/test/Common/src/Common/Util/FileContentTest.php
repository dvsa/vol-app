<?php

declare(strict_types=1);

namespace CommonTest\Util;

use Common\Util\FileContent;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Util\FileContent::class)]
final class FileContentTest extends \PHPUnit\Framework\TestCase
{
    public function testFileContent(): void
    {
        $mimeType = 'mimeType';

        $fileContent = new FileContent('foo.pdf', $mimeType);

        $this->assertEquals('foo.pdf', $fileContent->getFileName());
        $this->assertEquals($mimeType, $fileContent->getMimeType());
        $this->assertSame('foo.pdf', (string)$fileContent);
    }
}
