<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Document;

use Dvsa\Olcs\Api\Domain\Command\Document\UploadCsv as UploadCsvCmd;

/**
 * @see UploadCsvCmd
 */
class UploadCsvTest extends \PHPUnit\Framework\TestCase
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

        static::assertEquals($csvContent, $command->getCsvContent());
        static::assertEquals($fileDescription, $command->getFileDescription());
        static::assertEquals($category, $command->getCategory());
        static::assertEquals($subCategory, $command->getSubCategory());
        static::assertEquals($user, $command->getUser());
    }
}
