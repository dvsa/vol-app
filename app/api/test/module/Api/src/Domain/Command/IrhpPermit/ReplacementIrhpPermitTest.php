<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\IrhpPermit;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermit\ReplacementIrhpPermit;

/**
 * Close IRHP Permit Window Test
 */
class ReplacementIrhpPermitTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = ReplacementIrhpPermit::create([
            'replaces' => 725,
            'irhpPermitRange' => 2,
            'permitNumber' => 2342
        ]);

        $this->assertEquals(725, $command->getReplaces());
        $this->assertEquals(2, $command->getIrhpPermitRange());
        $this->assertEquals(2342, $command->getPermitNumber());
        $this->assertEquals(
            [
                'replaces' => 725,
                'irhpPermitRange' => 2,
                'permitNumber' => 2342
            ],
            $command->getArrayCopy()
        );
    }
}
