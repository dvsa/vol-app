<?php

namespace Dvsa\OlcsTest\Transfer\Command\ContactDetail\PhoneContact;

use Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Update;

/**
 * @covers Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Update
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
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

        static::assertEquals($id, $command->getId());
        static::assertEquals($phoneNr, $command->getPhoneNumber());
        static::assertEquals($phoneContactType, $command->getPhoneContactType());
        static::assertEquals($contactDetailsId, $command->getContactDetailsId());
    }
}
