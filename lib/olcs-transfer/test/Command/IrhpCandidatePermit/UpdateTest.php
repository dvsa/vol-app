<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Update;

/**
 * Update test
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'irhpPermitRange' => 21,
            'id' => 4545,
        ];

        $command = Update::create($data);

        $this->assertEquals($data['irhpPermitRange'], $command->getIrhpPermitRange());
        $this->assertEquals($data['id'], $command->getId());
    }
}
