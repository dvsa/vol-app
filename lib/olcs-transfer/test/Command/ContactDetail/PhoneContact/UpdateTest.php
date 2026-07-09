<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\ContactDetail\PhoneContact;

use Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Update;

/**
 * @covers Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Update
 */
final class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $id = 9999;
        $phoneNr = 'unit_PhoneNr';
        $phoneContactType = 'unit_PhoneContType';
        $contactDetailsId = 9999;

        $data = [
            'id' => $id,
            'phoneNumber' => $phoneNr,
            'phoneContactType' => $phoneContactType,
            'contactDetailsId' => $contactDetailsId,
        ];

        /** @var Update $command */
        $command = Update::create($data);

        $this->assertEquals($id, $command->getId());
        $this->assertEquals($phoneNr, $command->getPhoneNumber());
        $this->assertEquals($phoneContactType, $command->getPhoneContactType());
        $this->assertEquals($contactDetailsId, $command->getContactDetailsId());
    }
}
