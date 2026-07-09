<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\ContactDetail\PhoneContact;

use Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Create;

/**
 * @covers Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Create
 */
final class CreateTest extends \PHPUnit\Framework\TestCase
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

        $this->assertEquals($phoneNr, $command->getPhoneNumber());
        $this->assertEquals($phoneContactType, $command->getPhoneContactType());
        $this->assertEquals($contactDetailsId, $command->getContactDetailsId());
    }
}
