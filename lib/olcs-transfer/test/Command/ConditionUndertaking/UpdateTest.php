<?php

namespace Dvsa\OlcsTest\Transfer\Command\ConditionUndertaking;

use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Update;

/**
 * Update test
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 1,
            'version' => 2,
            'type' => 'cdt_con',
            'conditionCategory' => 'cu_cat_env',
            'notes' => 'notes',
            'fulfilled' => 'Y',
            'attachedTo' => 'cat_lic',
            'operatingCentre' => 1,
        ];

        $command = Update::create($data);

        $this->assertEquals(1, $command->getId());
        $this->assertEquals(2, $command->getVersion());
        $this->assertEquals('cdt_con', $command->getType());
        $this->assertEquals('cu_cat_env', $command->getConditionCategory());
        $this->assertEquals('notes', $command->getNotes());
        $this->assertEquals('Y', $command->getFulfilled());
        $this->assertEquals('cat_lic', $command->getAttachedTo());
        $this->assertEquals(1, $command->getOperatingCentre());
    }
}
