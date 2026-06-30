<?php

namespace Dvsa\OlcsTest\Transfer\Command\ContactDetail\PhoneContact;

use Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Create;

/**
 * @covers Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Create
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $phoneNr = 'unit_PhoneNr';
        $phoneContactType = 'unit_PhoneContType';
        $contactDetailsId = 9999;

        $data = [
            'phoneNumber' => $phoneNr,
            'phoneContactType' => $phoneContactType,
            'contactDetailsId' => $contactDetailsId,
        ];

        /** @var Create $command */
        $command = Create::create($data);

        static::assertEquals($phoneNr, $command->getPhoneNumber());
        static::assertEquals($phoneContactType, $command->getPhoneContactType());
        static::assertEquals($contactDetailsId, $command->getContactDetailsId());
    }
}
