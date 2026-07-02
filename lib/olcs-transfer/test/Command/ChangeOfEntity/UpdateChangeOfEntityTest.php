<?php

namespace Dvsa\OlcsTest\Transfer\Command\ChangeOfEntity;

use Dvsa\Olcs\Transfer\Command\ChangeOfEntity\UpdateChangeOfEntity as Cmd;

/**
 * Update Change Of Entity command test
 */
class UpdateChangeOfEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'version' => 222,
            'oldLicenceNo' => 'oldNo',
            'oldOrganisationName' => 'oldName',
        ];

        $command = Cmd::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(222, $command->getVersion());
        $this->assertEquals('oldNo', $command->getOldLicenceNo());
        $this->assertEquals('oldName', $command->getOldOrganisationName());
    }
}
