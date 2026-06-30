<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpPermitStock;

use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Create;

/**
 * Create test
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'irhpPermitType' => '1',
            'validFrom' => '2029-01-01',
            'validTo' => '2029-12-31',
            'initialStock' => 1400,
            'businessProcess' => 'app_business_process_apg',
            'applicationPathGroup' => 2,
            'periodNameKey' => 'this.is.a.key',
            'hiddenSs' => false,
            'permitCategory' => 'permit_cat_hors_contingent',
        ];

        $command = Create::create($data);

        $this->assertEquals($data['validFrom'], $command->getValidFrom());
        $this->assertEquals($data['validTo'], $command->getValidTo());
        $this->assertEquals($data['irhpPermitType'], $command->getIrhpPermitType());
        $this->assertEquals($data['initialStock'], $command->getInitialStock());
        $this->assertEquals('emissions_cat_na', $command->getEmissionsCategory());
        $this->assertEquals('app_business_process_apg', $command->getBusinessProcess());
        $this->assertEquals(2, $command->getApplicationPathGroup());
        $this->assertEquals('this.is.a.key', $command->getPeriodNameKey());
        $this->assertEquals('permit_cat_hors_contingent', $command->getPermitCategory());
        $this->assertFalse($command->getHiddenSs());
    }

    public function testStructureWithEmissionsCategory()
    {
        $data = [
            'irhpPermitType' => '1',
            'validFrom' => '2029-01-01',
            'validTo' => '2029-12-31',
            'initialStock' => 1400,
            'emissionsCategory' => 'emissions_cat_euro5',
            'hiddenSs' => true,
            'permitCategory' => 'permit_cat_hors_contingent',
        ];

        $command = Create::create($data);

        $this->assertEquals($data['validFrom'], $command->getValidFrom());
        $this->assertEquals($data['validTo'], $command->getValidTo());
        $this->assertEquals($data['irhpPermitType'], $command->getIrhpPermitType());
        $this->assertEquals($data['initialStock'], $command->getInitialStock());
        $this->assertEquals('permit_cat_hors_contingent', $command->getPermitCategory());
        $this->assertEquals('emissions_cat_euro5', $command->getEmissionsCategory());
        $this->assertTrue($command->getHiddenSs());
    }
}
