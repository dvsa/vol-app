<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpPermitRange;

use Dvsa\Olcs\Transfer\Command\IrhpPermitRange\Create;

/**
 * Create test
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'irhpPermitStock' => '1',
            'prefix' => 'UK',
            'fromNo' => 1,
            'toNo' => 2,
            'lostReplacement' => 1,
            'ssReserve' => 1,
            'countrys' => ['DE'],
        ];

        $command = Create::create($data);

        $this->assertEquals($data['irhpPermitStock'], $command->getIrhpPermitStock());
        $this->assertEquals('emissions_cat_na', $command->getEmissionsCategory());
        $this->assertEquals($data['prefix'], $command->getPrefix());
        $this->assertEquals($data['fromNo'], $command->getFromNo());
        $this->assertEquals($data['toNo'], $command->getToNo());
        $this->assertEquals($data['lostReplacement'], $command->getIsLostReplacement());
        $this->assertEquals($data['ssReserve'], $command->getSsReserve());
        $this->assertEquals($data['countrys'], $command->getRestrictedCountries());
        $this->assertNull($command->getJourney());
        $this->assertNull($command->getCabotage());
    }

    public function testStructureWithOptionalData()
    {
        $data = [
            'irhpPermitStock' => '1',
            'prefix' => 'UK',
            'fromNo' => 1,
            'toNo' => 2,
            'lostReplacement' => 1,
            'ssReserve' => 1,
            'countrys' => ['DE'],
            'emissionsCategory' => 'emissions_cat_euro5',
            'journey' => 'journey_single',
            'cabotage' => 1,
        ];

        $command = Create::create($data);

        $this->assertEquals($data['irhpPermitStock'], $command->getIrhpPermitStock());
        $this->assertEquals('emissions_cat_euro5', $command->getEmissionsCategory());
        $this->assertEquals($data['prefix'], $command->getPrefix());
        $this->assertEquals($data['fromNo'], $command->getFromNo());
        $this->assertEquals($data['toNo'], $command->getToNo());
        $this->assertEquals($data['lostReplacement'], $command->getIsLostReplacement());
        $this->assertEquals($data['ssReserve'], $command->getSsReserve());
        $this->assertEquals($data['countrys'], $command->getRestrictedCountries());
        $this->assertEquals($data['journey'], $command->getJourney());
        $this->assertEquals($data['cabotage'], $command->getCabotage());
    }
}
