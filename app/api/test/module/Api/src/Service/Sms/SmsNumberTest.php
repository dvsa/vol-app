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

    private function sms(string $number, int $id, ?string $modified, ?string $created = '2026-01-01 00:00:00'): PhoneContact
    {
        $pc = new PhoneContact(new RefData(PhoneContact::TYPE_SMS));
        $pc->setId($id);
        $pc->setPhoneNumber($number);
        $pc->setCreatedOn($created === null ? null : new \DateTime($created));
        $pc->setLastModifiedOn($modified === null ? null : new \DateTime($modified));

        return $pc;
    }

    public function testDuplicatesUseTheMostRecentlyModified(): void
    {
        $cd = new ContactDetails(new RefData());
        $cd->getPhoneContacts()->add($this->sms('+447700900001', 1, '2026-03-01 00:00:00'));
        $cd->getPhoneContacts()->add($this->sms('+447700900002', 2, '2026-05-01 00:00:00'));
        $cd->getPhoneContacts()->add($this->sms('+447700900003', 3, null, '2026-04-01 00:00:00'));
        $licence = new Licence(new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(), new RefData());
        $licence->setCorrespondenceCd($cd);

        $this->assertSame('+447700900002', SmsNumber::forLicence($licence));
        $this->assertSame(2, SmsNumber::latestContact($cd)->getId());
        $this->assertSame([2, 3, 1], array_map(fn ($pc) => $pc->getId(), SmsNumber::smsContacts($cd)));
    }

    public function testSameTimeFallsBackToHighestId(): void
    {
        $cd = new ContactDetails(new RefData());
        $cd->getPhoneContacts()->add($this->sms('+447700900007', 7, null));
        $cd->getPhoneContacts()->add($this->sms('+447700900009', 9, null));

        $this->assertSame(9, SmsNumber::latestContact($cd)->getId());
    }

    public function testNoContactDetailsMeansNoRows(): void
    {
        $this->assertSame([], SmsNumber::smsContacts(null));
        $this->assertNull(SmsNumber::latestContact(null));
    }
}
