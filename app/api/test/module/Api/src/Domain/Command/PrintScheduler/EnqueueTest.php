<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\PrintScheduler;

use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue;

/**
 * Enqueue
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class EnqueueTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = Enqueue::create(
            [
                'documentId' => 12,
                'documents' => [101, 102],
                'type' => 'TYPE',
                'jobName' => 'JOB_NAME',
                'user' => 1,
                'copies' => 10,
            ]
        );

        $this->assertEquals(12, $command->getDocumentId());
        $this->assertEquals([101, 102], $command->getDocuments());
        $this->assertEquals('TYPE', $command->getType());
        $this->assertEquals('JOB_NAME', $command->getJobName());
        $this->assertEquals(1, $command->getUser());
        $this->assertEquals(10, $command->getCopies());
        $this->assertEquals(false, $command->getIsDiscPrinting());
        $this->assertEquals(
            [
                'documentId' => 12,
                'documents' => [101, 102],
                'type' => 'TYPE',
                'jobName' => 'JOB_NAME',
                'user' => 1,
                'copies' => 10,
                'isDiscPrinting' => false,
            ],
            $command->getArrayCopy()
        );
    }
}
