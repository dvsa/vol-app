<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\Create;

/**
 * Create test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'irhpPermitType' => 2,
            'licence' => 7,
            'irhpPermitStock' => 3
        ];

        $command = Create::create($data);

        $this->assertEquals($data['irhpPermitType'], $command->getIrhpPermitType());
        $this->assertEquals($data['licence'], $command->getLicence());
        $this->assertEquals($data['irhpPermitStock'], $command->getIrhpPermitStock());
    }
}
