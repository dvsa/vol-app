<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\ChangeOfEntity;

use Dvsa\Olcs\Transfer\Command\ChangeOfEntity\CreateChangeOfEntity as Cmd;

/**
 * Create Change Of Entity command test
 */
final class CreateChangeOfEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'oldLicenceNo' => 'oldNo',
            'oldOrganisationName' => 'oldName',
            'applicationId' => 69,
        ];

        $command = Cmd::create($data);

        $this->assertEquals(69, $command->getApplicationId());
        $this->assertEquals('oldNo', $command->getOldLicenceNo());
        $this->assertEquals('oldName', $command->getOldOrganisationName());
    }
}
