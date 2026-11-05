<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Sms;

use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Sms\SmsNumber;
use PHPUnit\Framework\TestCase;

class SmsNumberTest extends TestCase
{
    private function licenceWith(array $numbersByType): Licence
    {
        $cd = new ContactDetails(new RefData());
        foreach ($numbersByType as $type => $number) {
            $pc = new PhoneContact(new RefData($type));
            $pc->setPhoneNumber($number);
            $cd->getPhoneContacts()->add($pc);
        }

        $licence = new Licence(new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(), new RefData());
        $licence->setCorrespondenceCd($cd);
        return $licence;
    }

    public function testReturnsSmsNumber(): void
    {
        $licence = $this->licenceWith([PhoneContact::TYPE_PRIMARY => '0113', PhoneContact::TYPE_SMS => '07700900123']);
        $this->assertSame('07700900123', SmsNumber::forLicence($licence));
    }

    public function testNullWhenNoSmsRow(): void
    {
        $this->assertNull(SmsNumber::forLicence($this->licenceWith([PhoneContact::TYPE_PRIMARY => '0113'])));
    }

    public function testNullWhenNoCorrespondenceCd(): void
    {
        $licence = new Licence(new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(), new RefData());
        $this->assertNull(SmsNumber::forLicence($licence));
    }

    public function testNullWhenBlank(): void
    {
        $this->assertNull(SmsNumber::forLicence($this->licenceWith([PhoneContact::TYPE_SMS => '  '])));
    }
}
