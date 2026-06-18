<?php

namespace Dvsa\OlcsTest\Transfer\Command\Organisation;

use Dvsa\Olcs\Transfer\Command\Organisation\TransferTo;

/**
 * Transfer To test
 */
class TransferToTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'receivingOrganisation' => 222,
            'licenceIds' => [1, 2, 3],
        ];

        $command = TransferTo::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(222, $command->getReceivingOrganisation());
        $this->assertEquals([1, 2, 3], $command->getLicenceIds());
    }
}
