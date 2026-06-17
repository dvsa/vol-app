<?php

namespace Dvsa\OlcsTest\Transfer\Command\CommunityLic;

use Dvsa\Olcs\Transfer\Command\CommunityLic\Annul;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Command\CommunityLic\Annul
 */
class AnnulTest extends MockeryTestCase
{
    public function testStructure()
    {
        $data = [
            'application' => 'unit_App',
            'licence' => 'unit_Lic',
            'communityLicenceIds' => ['unit_1', 'unit_2'],
            'checkOfficeCopy' => 'unit_Check',
        ];

        $command = Annul::create($data);

        $this->assertEquals('unit_App', $command->getApplication());
        $this->assertEquals('unit_Lic', $command->getLicence());
        $this->assertEquals(['unit_1', 'unit_2'], $command->getCommunityLicenceIds());
        $this->assertEquals('unit_Check', $command->getCheckOfficeCopy());
    }
}
