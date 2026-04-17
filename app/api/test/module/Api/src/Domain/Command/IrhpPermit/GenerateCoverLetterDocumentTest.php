<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\IrhpPermit;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermit\GenerateCoverLetterDocument;

/**
 * Generate Cover Letter Document Test
 */
class GenerateCoverLetterDocumentTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = GenerateCoverLetterDocument::create([
            'irhpPermit' => 2,
        ]);

        $this->assertEquals(2, $command->getIrhpPermit());
        $this->assertEquals(
            [
                'irhpPermit' => 2,
            ],
            $command->getArrayCopy()
        );
    }
}
