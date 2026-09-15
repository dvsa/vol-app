<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Document;

use Dvsa\Olcs\Api\Domain\Command\Document\UploadCsv as UploadCsvCmd;

/**
 * @see UploadCsvCmd
 */
final class UploadCsvTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $csvContent = ['content'];
        $fileDescription = 'file description';
        $category = 555;
        $subCategory = 666;
        $user = 777;

        $command = UploadCsvCmd::create(
            [
                'csvContent' => $csvContent,
                'fileDescription' => $fileDescription,
                'category' => $category,
                'subCategory' => $subCategory,
                'user' => $user,
            ]
        );

        $this->assertEquals($csvContent, $command->getCsvContent());
        $this->assertEquals($fileDescription, $command->getFileDescription());
        $this->assertEquals($category, $command->getCategory());
        $this->assertEquals($subCategory, $command->getSubCategory());
        $this->assertEquals($user, $command->getUser());
    }
}
