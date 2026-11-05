<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdateSmsNumber;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
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
        $this->mockedSmServices['EventHistoryCreator'] = m::mock(EventHistoryCreator::class);

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

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')->never();

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
        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->once()
            ->with($existing, EventHistoryType::EVENT_CODE_CHANGE_CORRESPONDENCE_ADDRESS, null, $licence);

        $this->sut->handleCommand($command);

        $this->assertSame('+447700900123', $existing->getPhoneNumber());
        $this->assertCount(1, $cd->getPhoneContacts());
    }

    public function testUnchangedNumberWritesNoHistory(): void
    {
        $command = Cmd::create(['id' => 7, 'smsNumber' => '07700 900123']);

        $cd = new ContactDetails(new RefData(ContactDetails::CONTACT_TYPE_CORRESPONDENCE_ADDRESS));
        $existing = new PhoneContact(new RefData(PhoneContact::TYPE_SMS));
        $existing->setPhoneNumber('+447700900123');
        $existing->setContactDetails($cd);
        $cd->getPhoneContacts()->add($existing);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->andReturn($this->licenceWith($cd));
        $this->repoMap['PhoneContact']->shouldReceive('save')->once()->with($existing);
        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')->never();

        $this->sut->handleCommand($command);
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

    /** Continuation must not be blocked, so this logs and saves nothing. */
    public function testNoCorrespondenceCdLogsAndSavesNothing(): void
    {
        $command = Cmd::create(['id' => 7, 'smsNumber' => '07700900123']);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getCorrespondenceCd')->andReturnNull();
        $licence->shouldReceive('getId')->andReturn(7);
        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->andReturn($licence);
        $this->repoMap['PhoneContact']->shouldReceive('save')->never();
        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')->never();

        $logger = m::mock(\Psr\Log\LoggerInterface::class);
        $logger->shouldReceive('warning')->once()->with(m::type('string'), ['licence' => 7]);
        \Olcs\Logging\Log\Logger::setLogger($logger);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['SMS number not saved, licence has no correspondence address'], $result->getMessages());
    }
}
