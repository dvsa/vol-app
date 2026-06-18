<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Create;

/**
 * Create test
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'irhpPermitRange' => 21,
            'irhpPermitApplication' => 2323,
        ];

        $command = Create::create($data);

        $this->assertEquals($data['irhpPermitRange'], $command->getIrhpPermitRange());
        $this->assertEquals($data['irhpPermitApplication'], $command->getIrhpPermitApplication());
    }
}
