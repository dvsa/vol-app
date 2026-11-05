<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdateSmsNumber;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateSmsNumber as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

final class UpdateSmsNumberTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->refData = [PhoneContact::TYPE_SMS];
        $this->sut = new UpdateSmsNumber();
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('PhoneContact', Repository\PhoneContact::class);

        parent::setUp();
    }

    private function licenceWith(ContactDetails $cd): Licence
    {
        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getCorrespondenceCd')->andReturn($cd);
        return $licence;
    }

    public function testCreatesRow(): void
    {
        $command = Cmd::create(['id' => 7, 'smsNumber' => '07700 900123']);

        $cd = new ContactDetails(new RefData(ContactDetails::CONTACT_TYPE_CORRESPONDENCE_ADDRESS));
        $licence = $this->licenceWith($cd);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->andReturn($licence);
        $this->repoMap['PhoneContact']->shouldReceive('save')->once()
            ->with(m::on(function (PhoneContact $pc) use ($cd) {
                return $pc->getPhoneNumber() === '+447700900123'
                    && $pc->getContactDetails() === $cd;
            }));

        $result = $this->sut->handleCommand($command);

        $this->assertCount(1, $cd->getPhoneContacts());
        $this->assertNotEmpty($result->getMessages());
    }

    public function testUpdatesExistingRow(): void
    {
        $command = Cmd::create(['id' => 7, 'smsNumber' => '07700900123']);

        $cd = new ContactDetails(new RefData(ContactDetails::CONTACT_TYPE_CORRESPONDENCE_ADDRESS));
        $existing = new PhoneContact(new RefData(PhoneContact::TYPE_SMS));
        $existing->setPhoneNumber('+447700900999');
        $existing->setContactDetails($cd);
        $cd->getPhoneContacts()->add($existing);
        $licence = $this->licenceWith($cd);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->andReturn($licence);
        $this->repoMap['PhoneContact']->shouldReceive('save')->once()->with($existing);

        $this->sut->handleCommand($command);

        $this->assertSame('+447700900123', $existing->getPhoneNumber());
        $this->assertCount(1, $cd->getPhoneContacts());
    }

    public function testInvalidThrows(): void
    {
        $command = Cmd::create(['id' => 7, 'smsNumber' => '01632 960001']);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)
            ->andReturn(m::mock(Licence::class));
        $this->repoMap['PhoneContact']->shouldReceive('save')->never();

        try {
            $this->sut->handleCommand($command);
            $this->fail('expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame(['smsNumber' => ['contact-number-sms-invalid']], $e->getMessages());
        }
    }

    public function testNoCorrespondenceCdThrows(): void
    {
        $command = Cmd::create(['id' => 7, 'smsNumber' => '07700900123']);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getCorrespondenceCd')->andReturnNull();
        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->andReturn($licence);

        $this->expectException(ValidationException::class);
        $this->sut->handleCommand($command);
    }
}
