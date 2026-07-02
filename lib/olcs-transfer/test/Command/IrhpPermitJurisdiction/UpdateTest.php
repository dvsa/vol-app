<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpPermitJurisdiction;

use Dvsa\Olcs\Transfer\Command\IrhpPermitJurisdiction\Update;

/**
 * Update test
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'trafficAreas' => [['B' => 100]],
        ];

        $command = Update::create($data);

        $this->assertEquals([['B' => 100]], $command->getTrafficAreas());
    }
}
