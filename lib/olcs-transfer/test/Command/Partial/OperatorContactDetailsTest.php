<?php

namespace Dvsa\OlcsTest\Transfer\Command\Partial;

use Dvsa\Olcs\Transfer\Command\Partial\OperatorContactDetails;

/**
 * Operator Contact Details Partial test
 */
class OperatorContactDetailsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 69,
            'version' => 1,
            'emailAddress' => 'foo@bar.com',
            'address' => ['address details'],
            'phoneContacts' => [
                ['phoneNumber' => 1],
                ['phoneNumber' => 2],
            ]
        ];

        $command = OperatorContactDetails::create($data);

        $this->assertEquals(69, $command->getId());
        $this->assertEquals(1, $command->getVersion());
        $this->assertEquals('foo@bar.com', $command->getEmailAddress());
        $this->assertEquals(['address details'], $command->getAddress());
        $this->assertEquals(
            [
                ['phoneNumber' => 1],
                ['phoneNumber' => 2],
            ],
            $command->getPhoneContacts()
        );
    }
}
