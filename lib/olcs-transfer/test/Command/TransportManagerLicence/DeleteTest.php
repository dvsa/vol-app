<?php

namespace Dvsa\OlcsTest\Transfer\Command\TransportManagerLicence;

use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\Delete;

class DeleteTest extends \PHPUnit\Framework\TestCase
{
    public function testSetYesNo(): void
    {
        $command = Delete::create(['yesNo' => 1]);
        $command->setYesNo(0);
        $this->assertEquals(0, $command->getYesNo());
    }

    public function testGetYesNo(): void
    {
        $command = Delete::create(['yesNo' => 1]);
        $this->assertEquals(1, $command->getYesNo());
    }
}
