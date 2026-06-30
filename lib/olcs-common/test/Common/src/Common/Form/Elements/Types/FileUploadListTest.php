<?php

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Types\FileUploadList;
use Common\Service\Helper\UrlHelperService;

/**
 * FileUploadListTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FileUploadListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the element configuration
     */
    public function testElement(): void
    {
        $files = [
            [
                'identifier' => 'hgafdjklhaldsf',
                'filename' => 'someFile.png',
                'description' => 'someFile',
                'size' => 50,
                'id' => 7,
                'version' => 1
            ],
            [
                'identifier' => 'hgafdjklhalsdgs',
                'filename' => 'someOtherFile.png',
                'description' => 'someOtherFile',
                'size' => 5000,
                'id' => 8,
                'version' => 1
            ],
            [
                'identifier' => 'hdsfgafdjklhalsdgs',
                'filename' => 'anotherFile.png',
                'description' => 'anotherFile',
                'size' => 50000000,
                'id' => 9,
                'version' => 1
            ],
            [
                'identifier' => 'hdsfgafdjklhalsdgs',
                'filename' => 'document.pdf',
                'description' => 'A document that cant be previewed',
                'size' => 20000000,
                'id' => 10,
                'version' => 3
            ],
            [
                'identifier' => 'bar',
                'filename' => 'tiffimage.tiff',
                'description' => 'A document that cant be previewed',
                'size' => 20000000,
                'id' => 11,
                'version' => 3
            ]
        ];

        $mockUrl = $this->createPartialMock(UrlHelperService::class, ['fromRoute']);
        $mockUrl->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValue('url'));

        $element = new FileUploadList();
        $element->setOption('preview_images', true);
        $element->setFiles($files, $mockUrl);

        $this->assertTrue($element->has('file-7'));
        $this->assertTrue($element->get('file-7')->has('id'));
        $this->assertTrue($element->get('file-7')->has('version'));
        $this->assertTrue($element->get('file-7')->has('link'));
        $this->assertTrue($element->get('file-7')->has('remove'));
        $this->assertTrue($element->get('file-7')->has('preview'));

        $this->assertTrue($element->has('file-8'));
        $this->assertTrue($element->get('file-8')->has('id'));
        $this->assertTrue($element->get('file-8')->has('version'));
        $this->assertTrue($element->get('file-8')->has('link'));
        $this->assertTrue($element->get('file-8')->has('remove'));
        $this->assertTrue($element->get('file-8')->has('preview'));

        $this->assertTrue($element->has('file-9'));
        $this->assertTrue($element->get('file-9')->has('id'));
        $this->assertTrue($element->get('file-9')->has('version'));
        $this->assertTrue($element->get('file-9')->has('link'));
        $this->assertTrue($element->get('file-9')->has('remove'));
        $this->assertTrue($element->get('file-9')->has('preview'));

        // test document doesnt have preview
        $this->assertTrue($element->has('file-10'));
        $this->assertTrue($element->get('file-10')->has('id'));
        $this->assertTrue($element->get('file-10')->has('version'));
        $this->assertTrue($element->get('file-10')->has('link'));
        $this->assertTrue($element->get('file-10')->has('remove'));
        $this->assertFalse($element->get('file-10')->has('preview'));

        // image with no preview available
        $this->assertTrue($element->has('file-11'));
        $this->assertTrue($element->get('file-11')->has('id'));
        $this->assertTrue($element->get('file-11')->has('version'));
        $this->assertTrue($element->get('file-11')->has('link'));
        $this->assertTrue($element->get('file-11')->has('remove'));
        $this->assertTrue($element->get('file-11')->has('preview'));
        $this->assertEquals(
            'Preview is not available',
            $element->get('file-11')->get('preview')->getValue()
        );
    }
}
