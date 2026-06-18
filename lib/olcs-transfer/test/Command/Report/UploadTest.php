<?php

namespace Dvsa\OlcsTest\Transfer\Command\Report;

use Dvsa\Olcs\Transfer\Command\Report\Upload;

/**
 * Upload test
 */
class UploadTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'reportType' => 'type',
            'filename' => 'file_name.txt',
            'content' => 'file content',
            'templateSlug' => 'imaslug',
            'name' => 'imaname'
        ];

        $command = Upload::create($data);

        $this->assertEquals('type', $command->getReportType());
        $this->assertEquals('file_name.txt', $command->getFilename());
        $this->assertEquals('file content', $command->getContent());
        $this->assertEquals(
            [
                'reportType' => 'type',
                'filename' => 'file_name.txt',
                'content' => 'file content',
                'templateSlug' => 'imaslug',
                'name' => 'imaname'
            ],
            $command->getArrayCopy()
        );
    }
}
