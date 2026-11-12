<?php

declare(strict_types=1);

/**
 * Save Addresses test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\SaveAddresses;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails;
use Dvsa\Olcs\Api\Domain\Repository\PhoneContact;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact as PhoneContactEntity;
use Dvsa\Olcs\Api\Domain\Command\Licence\SaveAddresses as Cmd;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;

/**
 * Save Addresses test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class SaveAddressesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new SaveAddresses();
        $this->mockRepo('Licence', Licence::class);
        $this->mockRepo('ContactDetails', ContactDetails::class);
        $this->mockRepo('PhoneContact', PhoneContact::class);
        $this->mockRepo('Address', Address::class);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
            'EventHistoryCreator' => m::mock(EventHistoryCreator::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            \Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact::TYPE_PRIMARY,
            \Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact::TYPE_SECONDARY,
            \Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact::TYPE_SMS,
        ];

        $this->references = [
            ContactDetailsEntity::class => [
                50 => m::mock(ContactDetailsEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithFullyPopulatedNewData(): void
    {
        $data = [
            'id' => 10,
            'correspondence' => [
                'id' => '',
                'version' => '',
                'fao' => 'foo bar'
            ],
            'correspondenceAddress' => [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
            ],
            'contact' => [
                'phone_primary' => '01131231234',
                'phone_primary_id' => '',
                'phone_primary_version' => '',

                'phone_secondary' => '01131231234',
                'phone_secondary_id' => '',
                'phone_secondary_version' => '',

                'email' => 'contact@email.com'
            ],
            'establishment' => [
                'id' => '',
                'version' => ''
            ],
            'establishmentAddress' => [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Est Address 1',
                'town' => 'Est Leeds',
                'postcode' => 'LS8 5NF',
                'countryCode' => 'GB',
            ],
            'consultant' => [
                'add-transport-consultant' => 'Y',
                'writtenPermissionToEngage' => 'Y',
                'transportConsultantName' => 'A TC',
                'address' => [
                    'id' => '',
                    'version' => '',
                    'addressLine1' => 'Con Address 1',
                    'town' => 'Con Leeds',
                    'postcode' => 'LS7 4NF',
                    'countryCode' => 'GB',
                ],
                'contact' => [
                    'phone_primary' => '01131231234',
                    'phone_primary_id' => '',
                    'phone_primary_version' => '',

                    'phone_secondary' => '01131231234',
                    'phone_secondary_id' => '',
                    'phone_secondary_version' => '',

                    'email' => 'tc@email.com'
                ]
            ]
        ];

        $command = Cmd::create($data);

        $correspondenceCd = m::mock(ContactDetailsEntity::class)
            ->shouldReceive('setFao')
            ->with('foo bar')
            ->shouldReceive('setEmailAddress')
            ->with('contact@email.com')
            ->shouldReceive('getVersion')
            ->andReturn(null, 1)
            ->shouldReceive('getPhoneContacts')
            ->andReturn(
                m::mock()
                ->shouldReceive('add')
                ->times(2)
                ->getMock()
            )
            ->getMock();

        $transportConsultantCd = m::mock(ContactDetailsEntity::class)
            ->shouldReceive('setFao')
            ->with('A TC')
            ->shouldReceive('setEmailAddress')
            ->with('tc@email.com')
            ->shouldReceive('setWrittenPermissionToEngage')
            ->with('Y')
            ->shouldReceive('getVersion')
            ->andReturn(null, 1)
            ->shouldReceive('getPhoneContacts')
            ->andReturn(
                m::mock()
                ->shouldReceive('add')
                ->times(2)
                ->getMock()
            )
            ->getMock();

        $licence = m::mock(LicenceEntity::class)
            ->makePartial()
            ->shouldReceive('setCorrespondenceCd')
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($correspondenceCd)
            ->shouldReceive('getTransportConsultantCd')
            ->andReturn($transportConsultantCd)
            ->getMock();

        $this->expectedLicenceCacheClear($licence);

        $result = new Result();

        $result->setFlag('hasChanged', true);
        $result->addId('contactDetails', 50);

        $this->expectedSideEffect(
            SaveAddress::class,
            [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'addressLine2' => null,
                'addressLine3' => null,
                'addressLine4' => null,
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
                'contactType' => 'ct_corr'
            ],
            $result
        );

        $result = new Result();

        $result->setFlag('hasChanged', true);
        $result->addId('contactDetails', 51);

        $this->expectedSideEffect(
            SaveAddress::class,
            [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Est Address 1',
                'addressLine2' => null,
                'addressLine3' => null,
                'addressLine4' => null,
                'town' => 'Est Leeds',
                'postcode' => 'LS8 5NF',
                'countryCode' => 'GB',
                'contactType' => 'ct_est'
            ],
            $result
        );

        $result = new Result();

        $result->setFlag('hasChanged', true);
        $result->addId('contactDetails', 52);

        $this->expectedSideEffect(
            SaveAddress::class,
            [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Con Address 1',
                'addressLine2' => null,
                'addressLine3' => null,
                'addressLine4' => null,
                'town' => 'Con Leeds',
                'postcode' => 'LS7 4NF',
                'countryCode' => 'GB',
                'contactType' => 'ct_tcon'
            ],
            $result
        );

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence)
            ->once()
            ->shouldReceive('save')
            ->with($licence)
            ->once()
            ->getMock();

        $this->repoMap['ContactDetails']->shouldReceive('save')
            ->with($correspondenceCd)
            ->shouldReceive('save')
            ->with($transportConsultantCd);

        $this->repoMap['PhoneContact']->shouldReceive('save')
            // correspondence phone contact +
            // transport consultant phone contacts
            ->times(4);

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($correspondenceCd, EventHistoryTypeEntity::EVENT_CODE_CHANGE_CORRESPONDENCE_ADDRESS, null, $licence)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                // @NOTE: this index gets overridden...
                'contactDetails' => 52
            ],
            'messages' => [
                'Contact details updated',
                'Phone contact primary created',
                'Phone contact secondary created',
                'Phone contact primary created',
                'Phone contact secondary created',
                'Transport consultant updated',
            ],
            'flags' => ['hasChanged' => true, 'isDirty' => true]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    private function smsCommandData(array $contact): array
    {
        return [
            'id' => 10,
            'correspondence' => ['id' => '', 'version' => '', 'fao' => 'foo bar'],
            'correspondenceAddress' => [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
            ],
            'contact' => $contact + ['email' => 'contact@email.com'],
        ];
    }

    private function expectSmsCorrespondenceSetup(Cmd $command, $phoneContacts): array
    {
        $correspondenceCd = m::mock(ContactDetailsEntity::class)
            ->shouldReceive('setFao')
            ->shouldReceive('setEmailAddress')
            ->shouldReceive('getVersion')
            ->andReturn(1)
            ->shouldReceive('getPhoneContacts')
            ->andReturn($phoneContacts)
            ->getMock();

        $licence = m::mock(LicenceEntity::class)
            ->makePartial()
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($correspondenceCd)
            ->getMock();

        $this->expectedLicenceCacheClear($licence);

        $result = new Result();
        $result->setFlag('hasChanged', false);
        $this->expectedSideEffect(
            SaveAddress::class,
            [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'addressLine2' => null,
                'addressLine3' => null,
                'addressLine4' => null,
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
                'contactType' => 'ct_corr',
            ],
            $result
        );

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence)
            ->shouldReceive('save')
            ->with($licence);
        $this->repoMap['ContactDetails']->shouldReceive('save')->with($correspondenceCd);

        return [$licence, $correspondenceCd];
    }

    public function testSmsNumberCreatedNormalised(): void
    {
        $command = Cmd::create($this->smsCommandData([
            'phone_sms' => '07700 900123',
            'phone_sms_id' => '',
            'phone_sms_version' => '',
        ]));

        $phoneContacts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->expectSmsCorrespondenceSetup($command, $phoneContacts);

        $this->repoMap['PhoneContact']->shouldReceive('save')
            ->once()
            ->with(m::on(function ($pc) {
                return $pc instanceof PhoneContactEntity
                    && $pc->getPhoneContactType()->getId() === PhoneContactEntity::TYPE_SMS
                    && $pc->getPhoneNumber() === '+447700900123';
            }));

        $result = $this->sut->handleCommand($command);

        $this->assertTrue($result->getFlag('smsChanged'));
        $this->assertFalse($result->getFlag('isDirty'));
        $this->assertSame(['Phone contact sms created'], $result->getMessages());
        $this->assertCount(1, $phoneContacts);
    }

    public function testDisplayedSmsNumberResavedIsNotAChange(): void
    {
        // the form shows +447700900123 as 07700 900123 and posts it back like that
        $command = Cmd::create($this->smsCommandData([
            'phone_sms' => '07700 900123',
            'phone_sms_id' => 99,
            'phone_sms_version' => 3,
        ]));

        [, $correspondenceCd] = $this->expectSmsCorrespondenceSetup($command, m::mock());

        $existing = m::mock(PhoneContactEntity::class)->makePartial();
        $existing->setContactDetails($correspondenceCd);
        $existing->setPhoneNumber('+447700900123');
        $existing->shouldReceive('getVersion')->andReturn(3);
        $existing->shouldReceive('getId')->andReturn(99);

        $this->repoMap['PhoneContact']->shouldReceive('fetchById')->with(99, m::any(), 3)->andReturn($existing);
        $this->repoMap['PhoneContact']->shouldReceive('save')->once()->with($existing);

        $result = $this->sut->handleCommand($command);

        $this->assertSame('+447700900123', $existing->getPhoneNumber());
        $this->assertNull($result->getFlag('smsChanged'));
        $this->assertSame([], $result->getMessages());
    }

    private function smsRow(int $id, string $number, string $type = PhoneContactEntity::TYPE_SMS): PhoneContactEntity
    {
        $row = m::mock(PhoneContactEntity::class)->makePartial();
        $row->setPhoneContactType(new RefData($type));
        $row->setPhoneNumber($number);
        $row->shouldReceive('getId')->andReturn($id);

        return $row;
    }

    public function testStaleFormWithoutSmsIdUpdatesTheExistingRow(): void
    {
        // the form was loaded before the SMS row existed, so it posts no id
        $command = Cmd::create($this->smsCommandData([
            'phone_sms' => '07700 900456',
            'phone_sms_id' => '',
            'phone_sms_version' => '',
        ]));

        $existing = $this->smsRow(99, '+447700900123');
        $existing->shouldReceive('getVersion')->andReturn(3, 4);
        $phoneContacts = new \Doctrine\Common\Collections\ArrayCollection([$existing]);
        [$licence] = $this->expectSmsCorrespondenceSetup($command, $phoneContacts);

        $this->repoMap['PhoneContact']->shouldReceive('save')->once()->with($existing);
        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->once()
            ->with($existing, EventHistoryTypeEntity::EVENT_CODE_CHANGE_CORRESPONDENCE_ADDRESS, null, $licence);

        $result = $this->sut->handleCommand($command);

        $this->assertCount(1, $phoneContacts);
        $this->assertSame('+447700900456', $existing->getPhoneNumber());
        $this->assertTrue($result->getFlag('smsChanged'));
        $this->assertSame(['Phone contact sms updated'], $result->getMessages());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('clearedSmsIds')]
    public function testClearingSmsDeletesEverySmsRow(string|int $postedId): void
    {
        $command = Cmd::create($this->smsCommandData([
            'phone_sms' => '',
            'phone_sms_id' => $postedId,
            'phone_sms_version' => $postedId === '' ? '' : 3,
        ]));

        $first = $this->smsRow(98, '+447700900001');
        $second = $this->smsRow(99, '+447700900002');
        $primary = $this->smsRow(97, '0113 496 0000', PhoneContactEntity::TYPE_PRIMARY);
        $phoneContacts = new \Doctrine\Common\Collections\ArrayCollection([$first, $second, $primary]);
        [, $correspondenceCd] = $this->expectSmsCorrespondenceSetup($command, $phoneContacts);
        $second->setContactDetails($correspondenceCd);

        if ($postedId !== '') {
            $this->repoMap['PhoneContact']->shouldReceive('fetchById')->with(99, m::any(), 3)->andReturn($second);
        }
        $this->repoMap['PhoneContact']->shouldReceive('delete')->once()->with($first);
        $this->repoMap['PhoneContact']->shouldReceive('delete')->once()->with($second);
        $this->repoMap['PhoneContact']->shouldReceive('delete')->never()->with($primary);

        $result = $this->sut->handleCommand($command);

        $this->assertSame([$primary], array_values($phoneContacts->toArray()));
        $this->assertTrue($result->getFlag('smsChanged'));
        $this->assertSame(['Phone contact sms deleted'], $result->getMessages());
    }

    /** A licence with an existing correspondence contact details, for checks that stop before anything is saved */
    private function licenceWithCorrespondence(Cmd $command, ?int $addressId = null): array
    {
        $address = m::mock(AddressEntity::class);
        $address->shouldReceive('getId')->andReturn($addressId);

        $correspondenceCd = m::mock(ContactDetailsEntity::class)->makePartial();
        $correspondenceCd->shouldReceive('getAddress')->andReturn($address);
        $correspondenceCd->shouldReceive('getVersion')->andReturn(1);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getCorrespondenceCd')->andReturn($correspondenceCd);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->andReturn($licence);
        $this->repoMap['Licence']->shouldReceive('save')->never();
        $this->repoMap['ContactDetails']->shouldReceive('save');

        return [$licence, $correspondenceCd];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('phoneRowsOnOtherContactDetails')]
    public function testPhoneContactFromOtherContactDetailsIsRefused(string $type, string $number): void
    {
        $command = Cmd::create($this->smsCommandData([
            'phone_' . $type => $number,
            'phone_' . $type . '_id' => 55,
            'phone_' . $type . '_version' => 2,
        ]));

        $result = new Result();
        $result->setFlag('hasChanged', false);
        $this->expectedSideEffect(SaveAddress::class, [], $result);
        $this->licenceWithCorrespondence($command);

        $otherRow = m::mock(PhoneContactEntity::class)->makePartial();
        $otherRow->setContactDetails(m::mock(ContactDetailsEntity::class));
        $otherRow->shouldReceive('getId')->andReturn(55);

        $this->repoMap['PhoneContact']->shouldReceive('fetchById')->with(55, m::any(), 2)->andReturn($otherRow);
        $this->repoMap['PhoneContact']->shouldReceive('save')->never();
        $this->repoMap['PhoneContact']->shouldReceive('delete')->never();

        $this->expectException(ForbiddenException::class);

        $this->sut->handleCommand($command);
    }

    public function testPhoneRowsMoveWhenTheSaveReplacesTheContactDetails(): void
    {
        // contact details with no address get replaced by new ones when an address is saved
        $command = Cmd::create($this->smsCommandData([
            'phone_primary' => '01131231234',
            'phone_primary_id' => 55,
            'phone_primary_version' => 2,
        ]));

        $oldCd = new ContactDetailsEntity(new RefData(ContactDetailsEntity::CONTACT_TYPE_CORRESPONDENCE_ADDRESS));
        $newCd = new ContactDetailsEntity(new RefData(ContactDetailsEntity::CONTACT_TYPE_CORRESPONDENCE_ADDRESS));
        $row = new PhoneContactEntity(new RefData(PhoneContactEntity::TYPE_PRIMARY));
        $row->setId(55);
        $row->setVersion(2);
        $row->setPhoneNumber('01131231234');
        $row->setContactDetails($oldCd);
        $oldCd->getPhoneContacts()->add($row);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setCorrespondenceCd($oldCd);
        $this->expectedLicenceCacheClear($licence);

        $result = new Result();
        $result->addId('contactDetails', 900);
        $result->setFlag('hasChanged', true);
        $this->expectedSideEffect(SaveAddress::class, [], $result);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->andReturn($licence);
        $this->references[ContactDetailsEntity::class][900] = $newCd;
        $this->repoMap['Licence']->shouldReceive('save')->once()->with($licence);
        $this->repoMap['ContactDetails']->shouldReceive('save')->with($newCd);
        $this->repoMap['PhoneContact']->shouldReceive('fetchById')->with(55, m::any(), 2)->andReturn($row);
        $this->repoMap['PhoneContact']->shouldReceive('save')->once()->with($row);

        $this->sut->handleCommand($command);

        $this->assertSame($newCd, $licence->getCorrespondenceCd());
        $this->assertSame($newCd, $row->getContactDetails());
        $this->assertTrue($newCd->getPhoneContacts()->contains($row));
        $this->assertFalse($oldCd->getPhoneContacts()->contains($row));
    }

    public static function phoneRowsOnOtherContactDetails(): array
    {
        return [
            'primary changed' => ['primary', '01131231234'],
            'primary cleared' => ['primary', ''],
            'secondary changed' => ['secondary', '01131231234'],
            'sms changed' => ['sms', '07700900123'],
            'sms cleared' => ['sms', ''],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('postedAddressIds')]
    public function testCorrespondenceAddressMustBeTheLicencesOwn(?int $currentAddressId): void
    {
        $data = $this->smsCommandData([]);
        $data['correspondenceAddress']['id'] = 77;
        $data['correspondenceAddress']['version'] = 1;
        $command = Cmd::create($data);

        $this->licenceWithCorrespondence($command, $currentAddressId);
        $this->repoMap['PhoneContact']->shouldReceive('save')->never();

        $this->expectException(ForbiddenException::class);

        $this->sut->handleCommand($command);
    }

    public static function postedAddressIds(): array
    {
        return ['another address' => [78], 'licence has no address yet' => [null]];
    }

    public static function clearedSmsIds(): array
    {
        return ['posted id' => [99], 'stale form, no id' => ['']];
    }

    public function testInvalidSmsNumberThrows(): void
    {
        $command = Cmd::create($this->smsCommandData(['phone_sms' => '0906 123 4567']));

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->andReturn($licence);

        try {
            $this->sut->handleCommand($command);
            $this->fail('Expected ValidationException');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertSame(
                ['phone_sms' => ['contact-number-sms-invalid']],
                $e->getMessages()
            );
        }
    }

    public function testMissingSmsKeysLeavesExistingRow(): void
    {
        $command = Cmd::create($this->smsCommandData([
            'phone_primary' => '',
            'phone_primary_id' => '',
            'phone_primary_version' => '',
        ]));

        // an existing SMS row on the contact must not be touched
        $this->expectSmsCorrespondenceSetup($command, m::mock());
        $this->repoMap['PhoneContact']->shouldReceive('save')->never();
        $this->repoMap['PhoneContact']->shouldReceive('delete')->never();
        $this->repoMap['PhoneContact']->shouldReceive('fetchById')->never();

        $result = $this->sut->handleCommand($command);

        $this->assertNull($result->getFlag('smsChanged'));
        $this->assertFalse($result->getFlag('isDirty'));
    }

    public function testHandleExistingCorrespondenceUpdateWithNoChangeAndDifferentPhoneNumbers(): void
    {
        $data = [
            'id' => 10,
            'correspondence' => [
                'id' => '',
                'version' => '',
                'fao' => 'foo bar'
            ],
            'correspondenceAddress' => [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
            ],
            'contact' => [
                'phone_primary' => '01131231234',
                'phone_primary_id' => '1',
                'phone_primary_version' => '1',

                'phone_secondary' => '01131231234',
                'phone_secondary_id' => '2',
                'phone_secondary_version' => '1',

                'email' => 'contact@email.com'
            ]
        ];

        $command = Cmd::create($data);

        $correspondenceCd = m::mock(ContactDetailsEntity::class)
            ->shouldReceive('setFao')
            ->with('foo bar')
            ->shouldReceive('setEmailAddress')
            ->with('contact@email.com')
            ->shouldReceive('getVersion')
            ->andReturn(1)
            ->getMock();

        $licence = m::mock(LicenceEntity::class)
            ->makePartial()
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($correspondenceCd)
            ->getMock();

        $this->expectedLicenceCacheClear($licence);

        $result = new Result();

        $result->setFlag('hasChanged', false);

        $this->expectedSideEffect(
            SaveAddress::class,
            [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'addressLine2' => null,
                'addressLine3' => null,
                'addressLine4' => null,
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
                'contactType' => 'ct_corr'
            ],
            $result
        );

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence)
            ->once()
            ->shouldReceive('save')
            ->with($licence)
            ->once()
            ->getMock();

        $this->repoMap['ContactDetails']->shouldReceive('save')
            ->with($correspondenceCd);

        $this->repoMap['PhoneContact']->shouldReceive('save')
            ->times(2)
            ->shouldReceive('fetchById')
            ->with(1, 1, 1)
            ->andReturn(
                m::mock(PhoneContactEntity::class)
                ->makePartial()
                ->shouldReceive('setContactDetails')
                ->with($correspondenceCd)
                ->shouldReceive('getContactDetails')
                ->andReturn($correspondenceCd)
                ->shouldReceive('getVersion')
                ->andReturn(2)
                ->getMock()
            )
            ->shouldReceive('fetchById')
            ->with(2, 1, 1)
            ->andReturn(
                m::mock(PhoneContactEntity::class)
                ->makePartial()
                ->shouldReceive('setContactDetails')
                ->with($correspondenceCd)
                ->shouldReceive('getContactDetails')
                ->andReturn($correspondenceCd)
                ->shouldReceive('getVersion')
                ->andReturn(2)
                ->getMock()
            )
            ->getMock();

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with(m::type(PhoneContactEntity::class), EventHistoryTypeEntity::EVENT_CODE_CHANGE_CORRESPONDENCE_ADDRESS, null, $licence)
            ->twice();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Phone contact primary updated',
                'Phone contact secondary updated',
            ],
            'flags' => ['hasChanged' => 1, 'isDirty' => 1]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleExistingCorrespondenceUpdateWithNoChangeAndDeletedPhoneNumbers(): void
    {
        $data = [
            'id' => 10,
            'correspondence' => [
                'id' => '',
                'version' => '',
                'fao' => 'foo bar'
            ],
            'correspondenceAddress' => [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
            ],
            'contact' => [
                'phone_primary' => '',
                'phone_primary_id' => '1',
                'phone_primary_version' => '1',

                'phone_secondary' => '',
                'phone_secondary_id' => '2',
                'phone_secondary_version' => '1',

                'email' => 'contact@email.com'
            ]
        ];

        $command = Cmd::create($data);

        $correspondenceCd = m::mock(ContactDetailsEntity::class)
            ->shouldReceive('setFao')
            ->with('foo bar')
            ->shouldReceive('setEmailAddress')
            ->with('contact@email.com')
            ->shouldReceive('getVersion')
            ->andReturn(1)
            ->shouldReceive('getPhoneContacts')
            ->andReturn(
                m::mock()
                ->shouldReceive('removeElement')
                ->times(2)
                ->getMock()
            )
            ->getMock();

        $licence = m::mock(LicenceEntity::class)
            ->makePartial()
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($correspondenceCd)
            ->getMock();

        $this->expectedLicenceCacheClear($licence);

        $result = new Result();

        $result->setFlag('hasChanged', false);

        $this->expectedSideEffect(
            SaveAddress::class,
            [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'addressLine2' => null,
                'addressLine3' => null,
                'addressLine4' => null,
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
                'contactType' => 'ct_corr'
            ],
            $result
        );

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence)
            ->once()
            ->shouldReceive('save')
            ->with($licence)
            ->once()
            ->getMock();

        $this->repoMap['ContactDetails']->shouldReceive('save')
            ->with($correspondenceCd);

        $this->repoMap['PhoneContact']->shouldReceive('delete')
            ->times(2)
            ->shouldReceive('fetchById')
            ->with(1, 1, 1)
            ->andReturn(
                m::mock(PhoneContactEntity::class)
                ->makePartial()
                ->shouldReceive('setContactDetails')
                ->with($correspondenceCd)
                ->shouldReceive('getContactDetails')
                ->andReturn($correspondenceCd)
                ->shouldReceive('getId')
                ->andReturn(1)
                ->getMock()
            )
            ->shouldReceive('fetchById')
            ->with(2, 1, 1)
            ->andReturn(
                m::mock(PhoneContactEntity::class)
                ->makePartial()
                ->shouldReceive('setContactDetails')
                ->with($correspondenceCd)
                ->shouldReceive('getContactDetails')
                ->andReturn($correspondenceCd)
                ->shouldReceive('getId')
                ->andReturn(2)
                ->getMock()
            )
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Phone contact primary deleted',
                'Phone contact secondary deleted',
            ],
            'flags' => ['isDirty' => 1, 'hasChanged' => 1]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleExistingCorrespondenceUpdateWithNoChangeAndDeletedTransportConsultant(): void
    {
        $data = [
            'id' => 10,
            'correspondence' => [
                'id' => '',
                'version' => '',
                'fao' => 'foo bar'
            ],
            'correspondenceAddress' => [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
            ],
            'contact' => [
                'phone_primary' => '01131231234',
                'phone_primary_id' => '1',
                'phone_primary_version' => '1',

                'phone_secondary' => '01131231234',
                'phone_secondary_id' => '2',
                'phone_secondary_version' => '1',

                'email' => 'contact@email.com'
            ],
            'consultant' => [
                'add-transport-consultant' => 'N'
            ]
        ];

        $command = Cmd::create($data);

        $correspondenceCd = m::mock(ContactDetailsEntity::class)
            ->shouldReceive('setFao')
            ->with('foo bar')
            ->shouldReceive('setEmailAddress')
            ->with('contact@email.com')
            ->shouldReceive('getVersion')
            ->andReturn(1)
            ->getMock();

        $transportConsultantCd = m::mock(ContactDetailsEntity::class);
        $transportConsultantAddress = m::mock(AddressEntity::class);
        $transportConsultantCd->shouldReceive('getAddress')->andReturn($transportConsultantAddress);
        $licence = m::mock(LicenceEntity::class)
            ->makePartial()
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($correspondenceCd)
            ->shouldReceive('getTransportConsultantCd')
            ->andReturn($transportConsultantCd)
            ->shouldReceive('setTransportConsultantCd')
            ->with(null)
            ->getMock();

        $this->expectedLicenceCacheClear($licence);

        $result = new Result();

        $result->setFlag('hasChanged', false);

        $this->expectedSideEffect(
            SaveAddress::class,
            [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'addressLine2' => null,
                'addressLine3' => null,
                'addressLine4' => null,
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
                'contactType' => 'ct_corr'
            ],
            $result
        );

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence)
            ->once()
            ->shouldReceive('save')
            ->with($licence)
            ->once()
            ->getMock();

        $this->repoMap['ContactDetails']->shouldReceive('save')
            ->with($correspondenceCd);
        $this->repoMap['ContactDetails']->shouldReceive('delete')
            ->with($transportConsultantCd);

        $this->repoMap['PhoneContact']->shouldReceive('save')
            ->times(2)
            ->shouldReceive('fetchById')
            ->with(1, 1, 1)
            ->andReturn(
                m::mock(PhoneContactEntity::class)
                ->makePartial()
                ->shouldReceive('setContactDetails')
                ->with($correspondenceCd)
                ->shouldReceive('getContactDetails')
                ->andReturn($correspondenceCd)
                ->getMock()
            )
            ->shouldReceive('fetchById')
            ->with(2, 1, 1)
            ->andReturn(
                m::mock(PhoneContactEntity::class)
                ->makePartial()
                ->shouldReceive('setContactDetails')
                ->with($correspondenceCd)
                ->shouldReceive('getContactDetails')
                ->andReturn($correspondenceCd)
                ->getMock()
            )
            ->getMock();

        $this->repoMap['Address']->shouldReceive('delete')
                                 ->once()
                                 ->with($transportConsultantAddress);
        $this->repoMap['Address']->shouldReceive('refresh')
                                 ->once()
                                 ->with($transportConsultantAddress);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Transport consultant deleted'
            ],
            'flags' => ['isDirty' => 1, 'hasChanged' => 1]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleExistingCorrespondenceUpdateWithNoChangeAndDeletedTcWithoutCd(): void
    {
        $data = [
            'id' => 10,
            'correspondence' => [
                'id' => '',
                'version' => '',
                'fao' => 'foo bar'
            ],
            'correspondenceAddress' => [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
            ],
            'contact' => [
                'phone_primary' => '01131231234',
                'phone_primary_id' => '1',
                'phone_primary_version' => '1',

                'phone_secondary' => '01131231234',
                'phone_secondary_id' => '2',
                'phone_secondary_version' => '1',

                'email' => 'contact@email.com'
            ],
            'consultant' => [
                'add-transport-consultant' => 'N'
            ]
        ];

        $command = Cmd::create($data);

        $correspondenceCd = m::mock(ContactDetailsEntity::class)
            ->shouldReceive('setFao')
            ->with('foo bar')
            ->shouldReceive('setEmailAddress')
            ->with('contact@email.com')
            ->shouldReceive('getVersion')
            ->andReturn(1)
            ->getMock();

        $licence = m::mock(LicenceEntity::class)
            ->makePartial()
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($correspondenceCd)
            ->shouldReceive('getTransportConsultantCd')
            ->andReturn(null)
            ->shouldReceive('setTransportConsultantCd')
            ->with(null)
            ->getMock();

        $this->expectedLicenceCacheClear($licence);

        $result = new Result();

        $result->setFlag('hasChanged', false);

        $this->expectedSideEffect(
            SaveAddress::class,
            [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'addressLine2' => null,
                'addressLine3' => null,
                'addressLine4' => null,
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
                'contactType' => 'ct_corr'
            ],
            $result
        );

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence)
            ->once()
            ->shouldReceive('save')
            ->with($licence)
            ->once()
            ->getMock();

        $this->repoMap['ContactDetails']->shouldReceive('save')
            ->with($correspondenceCd);

        $this->repoMap['PhoneContact']->shouldReceive('save')
            ->times(2)
            ->shouldReceive('fetchById')
            ->with(1, 1, 1)
            ->andReturn(
                m::mock(PhoneContactEntity::class)
                    ->makePartial()
                    ->shouldReceive('setContactDetails')
                    ->with($correspondenceCd)
                    ->shouldReceive('getContactDetails')
                    ->andReturn($correspondenceCd)
                    ->getMock()
            )
            ->shouldReceive('fetchById')
            ->with(2, 1, 1)
            ->andReturn(
                m::mock(PhoneContactEntity::class)
                    ->makePartial()
                    ->shouldReceive('setContactDetails')
                    ->with($correspondenceCd)
                    ->shouldReceive('getContactDetails')
                    ->andReturn($correspondenceCd)
                    ->getMock()
            )
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [],
            'flags' => ['isDirty' => false, 'hasChanged' => false]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleExistingCorrespondenceUpdateWithNoChangeAndDeletedTcWithoutAddress(): void
    {
        $data = [
            'id' => 10,
            'correspondence' => [
                'id' => '',
                'version' => '',
                'fao' => 'foo bar'
            ],
            'correspondenceAddress' => [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
            ],
            'contact' => [
                'phone_primary' => '01131231234',
                'phone_primary_id' => '1',
                'phone_primary_version' => '1',

                'phone_secondary' => '01131231234',
                'phone_secondary_id' => '2',
                'phone_secondary_version' => '1',

                'email' => 'contact@email.com'
            ],
            'consultant' => [
                'add-transport-consultant' => 'N'
            ]
        ];

        $command = Cmd::create($data);

        $correspondenceCd = m::mock(ContactDetailsEntity::class)
            ->shouldReceive('setFao')
            ->with('foo bar')
            ->shouldReceive('setEmailAddress')
            ->with('contact@email.com')
            ->shouldReceive('getVersion')
            ->andReturn(1)
            ->getMock();

        $transportConsultantCd = m::mock(ContactDetailsEntity::class);
        $transportConsultantCd->shouldReceive('getAddress')->andReturn(null);
        $licence = m::mock(LicenceEntity::class)
            ->makePartial()
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($correspondenceCd)
            ->shouldReceive('getTransportConsultantCd')
            ->andReturn($transportConsultantCd)
            ->shouldReceive('setTransportConsultantCd')
            ->with(null)
            ->getMock();

        $this->expectedLicenceCacheClear($licence);

        $result = new Result();

        $result->setFlag('hasChanged', false);

        $this->expectedSideEffect(
            SaveAddress::class,
            [
                'id' => '',
                'version' => '',
                'addressLine1' => 'Address 1',
                'addressLine2' => null,
                'addressLine3' => null,
                'addressLine4' => null,
                'town' => 'Leeds',
                'postcode' => 'LS9 6NF',
                'countryCode' => 'GB',
                'contactType' => 'ct_corr'
            ],
            $result
        );

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($licence)
            ->once()
            ->shouldReceive('save')
            ->with($licence)
            ->once()
            ->getMock();

        $this->repoMap['ContactDetails']->shouldReceive('save')
            ->with($correspondenceCd);
        $this->repoMap['ContactDetails']->shouldReceive('delete')
            ->with($transportConsultantCd);

        $this->repoMap['PhoneContact']->shouldReceive('save')
            ->times(2)
            ->shouldReceive('fetchById')
            ->with(1, 1, 1)
            ->andReturn(
                m::mock(PhoneContactEntity::class)
                    ->makePartial()
                    ->shouldReceive('setContactDetails')
                    ->with($correspondenceCd)
                    ->shouldReceive('getContactDetails')
                    ->andReturn($correspondenceCd)
                    ->getMock()
            )
            ->shouldReceive('fetchById')
            ->with(2, 1, 1)
            ->andReturn(
                m::mock(PhoneContactEntity::class)
                    ->makePartial()
                    ->shouldReceive('setContactDetails')
                    ->with($correspondenceCd)
                    ->shouldReceive('getContactDetails')
                    ->andReturn($correspondenceCd)
                    ->getMock()
            )
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Transport consultant deleted'
            ],
            'flags' => ['isDirty' => true, 'hasChanged' => true]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
