<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\IrhpPermit;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermit\GeneratePermitDocument;

/**
 * Generate Permit Document Test
 */
class GeneratePermitDocumentTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = GeneratePermitDocument::create([
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
