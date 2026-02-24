<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\PrintScheduler;

use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\PrintJob;

/**
 * PrintJob Test
 */
class PrintJobTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = PrintJob::create(
            [
                'id' => 200,
                'documents' => [101, 102],
                'title' => 'TITLE',
                'user' => 1,
                'copies' => 10,
            ]
        );

        $this->assertEquals(200, $command->getId());
        $this->assertEquals([101, 102], $command->getDocuments());
        $this->assertEquals('TITLE', $command->getTitle());
        $this->assertEquals(1, $command->getUser());
        $this->assertEquals(10, $command->getCopies());
        $this->assertEquals(
            [
                'id' => 200,
                'documents' => [101, 102],
                'title' => 'TITLE',
                'user' => 1,
                'copies' => 10,
            ],
            $command->getArrayCopy()
        );
    }
}
