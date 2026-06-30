<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdatePeriod;

/**
 * Update Period test
 */
class UpdatePeriodTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 22,
            'irhpPermitStock' => 2323
        ];

        $command = UpdatePeriod::create($data);

        $this->assertEquals(22, $command->getId());
        $this->assertEquals(2323, $command->getIrhpPermitStock());
    }
}
