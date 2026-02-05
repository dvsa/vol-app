<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\AcquiredRights\Model\ApplicationReference;
use Dvsa\Olcs\AcquiredRights\Service\AcquiredRightsService;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\TmQualification as TmQualificationRepo;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TransportManagerApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\Tm\TmEmployment;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateDetails as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication\UpdateDetails::class)]
class UpdateDetailsTest extends AbstractCommandHandlerTestCase
{
    protected $loggedInUser;

    /** @var  CommandHandler\TransportManagerApplication\UpdateDetails */
    protected $sut;

    /** @var AcquiredRightsService|m\Mock */
    protected $acquiredRightsServiceMock;

    public function setUp(): void
    {
        $this->acquiredRightsServiceMock = m::mock(AcquiredRightsService::class)->shouldIgnoreMissing();
        $this->sut = new CommandHandler\TransportManagerApplication\UpdateDetails($this->acquiredRightsServiceMock);
        $this->mockRepo('TransportManagerApplication', TransportManagerApplicationRepo::class);
        $this->mockRepo('ContactDetails', ContactDetailsRepo::class);
        $this->mockRepo('TmQualification', TmQualificationRepo::class);
        $this->mockRepo('Address', \Dvsa\Olcs\Api\Domain\Repository\Address::class);
        $this->mockRepo('TmEmployment', \Dvsa\Olcs\Api\Domain\Repository\TmEmployment::class);
        $this->mockRepo('OtherLicence', \Dvsa\Olcs\Api\Domain\Repository\OtherLicence::class);
        $this->mockRepo('PreviousConviction', \Dvsa\Olcs\Api\Domain\Repository\PreviousConviction::class);
        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'TM_TYPE',
            'ct_tm',
            'tmap_st_awaiting_signature',
            TmQualification::QUALIFICATION_TYPE_LGVAR,
            TmQualification::QUALIFICATION_TYPE_NILGVAR,
        ];

        $this->references = [
            \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre::class => [
                12 => m::mock(\Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre::class),
                65 => m::mock(\Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre::class),
            ],
            Country::class => [
                Country::ID_GERMANY => m::mock(Country::class),
                Country::ID_UNITED_KINGDOM => m::mock(Country::class),
            ]
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('commandDataProvider')]
    public function testHandleCommand(mixed $commandData): void
    {
        $command = Command::create($commandData);
        $tma = new TransportManagerApplication();
        $tma->setId(863);
        $tma->setTransportManager(new TransportManager());
        $tma->getTransportManager()->setHomeCd(
            new ContactDetails(
                m::mock(RefData::class)
            )
        );
        $tma->getTransportManager()->getHomeCd()->setPerson(new Person());

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')->once()
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 234)->andReturn($tma);

        $this->repoMap['Address']->shouldReceive('save')->once();
        $this->repoMap['ContactDetails']->shouldReceive('save')->once();

        if ($commandData['dob'] === null) {
            $this->expectException(ValidationException::class);
            $this->sut->handleCommand($command);
        } else {
            $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once();
            $this->maybeDeleteAssociatedData($commandData);
            $result = $this->sut->handleCommand($command);
            $this->assertSame(['Transport Manager Application ID 863 updated'], $result->getMessages());
        }
    }

    private function maybeDeleteAssociatedData(mixed $commandData): void
    {
        if ($commandData['submit'] !== 'Y') {
            return;
        }
        if ($commandData['hasOtherLicences'] === 'N') {
            $otherLicence = m::mock(OtherLicence::class);
            $this->repoMap['OtherLicence']
                ->shouldReceive('fetchForTransportManagerApplication')
                ->andReturn([$otherLicence]);
            $this->repoMap['OtherLicence']
                ->shouldReceive('delete')
                ->with($otherLicence);
        }
        if ($commandData['hasPreviousLicences'] === 'N') {
            $previousLicence = m::mock(OtherLicence::class);
            $this->repoMap['OtherLicence']
                ->shouldReceive('fetchByTransportManager')
                ->andReturn([$previousLicence]);
            $this->repoMap['OtherLicence']
                ->shouldReceive('delete')
                ->with($previousLicence);
        }
        if ($commandData['hasOtherEmployment'] === 'N') {
            $otherEmployment = m::mock(TmEmployment::class);
            $this->repoMap['TmEmployment']
                ->shouldReceive('fetchByTransportManager')
                ->andReturn([$otherEmployment]);
            $this->repoMap['TmEmployment']
                ->shouldReceive('delete')
                ->with($otherEmployment);
        }
        if ($commandData['hasConvictions'] === 'N') {
            $previousConviction = m::mock(PreviousConviction::class);
            $this->repoMap['PreviousConviction']
                ->shouldReceive('fetchByTransportManager')
                ->andReturn([$previousConviction]);
            $this->repoMap['PreviousConviction']
                ->shouldReceive('delete')
                ->with($previousConviction);
        }
    }

    public static function commandDataProvider(): array
    {
        $commands = [
            'valid_command' => [
                [
                    'id' => 863,
                    'version' => 234,
                    'email' => 'fred@fred.com',
                    'placeOfBirth' => 'Leeds',
                    'dob' => '2015-11-26',
                    'homeAddress' => [
                        'addressLine1' => 'LINE_1',
                        'addressLine2' => 'LINE_2',
                        'addressLine3' => 'LINE_3',
                        'addressLine4' => 'LINE_4',
                        'town' => 'TOWN',
                        'postcode' => 'POSTCODE',
                        'countryCode' => Country::ID_UNITED_KINGDOM,
                    ],
                    'workAddress' => [
                        'addressLine1' => 'W_LINE_1',
                        'addressLine2' => 'W_LINE_2',
                        'addressLine3' => 'W_LINE_3',
                        'addressLine4' => 'W_LINE_4',
                        'town' => 'W_TOWN',
                        'postcode' => 'W_POSTCODE',
                        'countryCode' => Country::ID_GERMANY,
                    ],
                    'tmType' => 'TM_TYPE',
                    'isOwner' => 'Y',
                    'hoursMon' => 1,
                    'hoursTue' => 2,
                    'hoursWed' => 3,
                    'hoursThu' => 4,
                    'hoursFri' => 5,
                    'hoursSat' => 6,
                    'hoursSun' => 7,
                    'hasOtherLicences' => null,
                    'hasOtherEmployment' => null,
                    'hasConvictions' => null,
                    'hasPreviousLicences' => null,
                    'additionalInfo' => 'TEXT',
                    'submit' => 'Y',

                ]
            ],
            'invalid_command' => [
                [
                    'id' => 863,
                    'version' => 234,
                    'email' => 'fred@fred.com',
                    'placeOfBirth' => 'Leeds',
                    'dob' => null,
                    'homeAddress' => [
                        'addressLine1' => 'LINE_1',
                        'addressLine2' => 'LINE_2',
                        'addressLine3' => 'LINE_3',
                        'addressLine4' => 'LINE_4',
                        'town' => 'TOWN',
                        'postcode' => 'POSTCODE',
                        'countryCode' => Country::ID_UNITED_KINGDOM,
                    ],
                    'workAddress' => [
                        'addressLine1' => 'W_LINE_1',
                        'addressLine2' => 'W_LINE_2',
                        'addressLine3' => 'W_LINE_3',
                        'addressLine4' => 'W_LINE_4',
                        'town' => 'W_TOWN',
                        'postcode' => 'W_POSTCODE',
                        'countryCode' => Country::ID_GERMANY,
                    ],
                    'tmType' => 'TM_TYPE',
                    'isOwner' => 'Y',
                    'hoursMon' => 1,
                    'hoursTue' => 2,
                    'hoursWed' => 3,
                    'hoursThu' => 4,
                    'hoursFri' => 5,
                    'hoursSat' => 6,
                    'hoursSun' => 7,
                    'hasOtherLicences' => null,
                    'hasOtherEmployment' => null,
                    'hasConvictions' => null,
                    'hasPreviousLicences' => null,
                    'additionalInfo' => 'TEXT',
                    'submit' => 'Y',

                ]
            ],
            'valid_command_with_associated_data_no_delete' => [
                [
                    'id' => 863,
                    'version' => 234,
                    'email' => 'fred@fred.com',
                    'placeOfBirth' => 'Leeds',
                    'dob' => '2015-11-26',
                    'homeAddress' => [
                        'addressLine1' => 'LINE_1',
                        'addressLine2' => 'LINE_2',
                        'addressLine3' => 'LINE_3',
                        'addressLine4' => 'LINE_4',
                        'town' => 'TOWN',
                        'postcode' => 'POSTCODE',
                        'countryCode' => Country::ID_UNITED_KINGDOM,
                    ],
                    'workAddress' => [
                        'addressLine1' => 'W_LINE_1',
                        'addressLine2' => 'W_LINE_2',
                        'addressLine3' => 'W_LINE_3',
                        'addressLine4' => 'W_LINE_4',
                        'town' => 'W_TOWN',
                        'postcode' => 'W_POSTCODE',
                        'countryCode' => Country::ID_GERMANY,
                    ],
                    'tmType' => 'TM_TYPE',
                    'isOwner' => 'Y',
                    'hoursMon' => 1,
                    'hoursTue' => 2,
                    'hoursWed' => 3,
                    'hoursThu' => 4,
                    'hoursFri' => 5,
                    'hoursSat' => 6,
                    'hoursSun' => 7,
                    'hasOtherLicences' => 'Y',
                    'hasOtherEmployment' => 'Y',
                    'hasConvictions' => 'Y',
                    'hasPreviousLicences' => 'Y',
                    'additionalInfo' => 'TEXT',
                    'submit' => 'Y',
                ]
            ],
            'valid_command_with_associated_data_no_delete_no_submit' => [
                [
                    'id' => 863,
                    'version' => 234,
                    'email' => 'fred@fred.com',
                    'placeOfBirth' => 'Leeds',
                    'dob' => '2015-11-26',
                    'homeAddress' => [
                        'addressLine1' => 'LINE_1',
                        'addressLine2' => 'LINE_2',
                        'addressLine3' => 'LINE_3',
                        'addressLine4' => 'LINE_4',
                        'town' => 'TOWN',
                        'postcode' => 'POSTCODE',
                        'countryCode' => Country::ID_UNITED_KINGDOM,
                    ],
                    'workAddress' => [
                        'addressLine1' => 'W_LINE_1',
                        'addressLine2' => 'W_LINE_2',
                        'addressLine3' => 'W_LINE_3',
                        'addressLine4' => 'W_LINE_4',
                        'town' => 'W_TOWN',
                        'postcode' => 'W_POSTCODE',
                        'countryCode' => Country::ID_GERMANY,
                    ],
                    'tmType' => 'TM_TYPE',
                    'isOwner' => 'Y',
                    'hoursMon' => 1,
                    'hoursTue' => 2,
                    'hoursWed' => 3,
                    'hoursThu' => 4,
                    'hoursFri' => 5,
                    'hoursSat' => 6,
                    'hoursSun' => 7,
                    'hasOtherLicences' => 'Y',
                    'hasOtherEmployment' => 'Y',
                    'hasConvictions' => 'Y',
                    'hasPreviousLicences' => 'Y',
                    'additionalInfo' => 'TEXT',
                    'submit' => 'N',
                ]
            ],
            'valid_command_with_associated_data_delete_on_submit' => [
                [
                    'id' => 863,
                    'version' => 234,
                    'email' => 'fred@fred.com',
                    'placeOfBirth' => 'Leeds',
                    'dob' => '2015-11-26',
                    'homeAddress' => [
                        'addressLine1' => 'LINE_1',
                        'addressLine2' => 'LINE_2',
                        'addressLine3' => 'LINE_3',
                        'addressLine4' => 'LINE_4',
                        'town' => 'TOWN',
                        'postcode' => 'POSTCODE',
                        'countryCode' => Country::ID_UNITED_KINGDOM,
                    ],
                    'workAddress' => [
                        'addressLine1' => 'W_LINE_1',
                        'addressLine2' => 'W_LINE_2',
                        'addressLine3' => 'W_LINE_3',
                        'addressLine4' => 'W_LINE_4',
                        'town' => 'W_TOWN',
                        'postcode' => 'W_POSTCODE',
                        'countryCode' => Country::ID_GERMANY,
                    ],
                    'tmType' => 'TM_TYPE',
                    'isOwner' => 'Y',
                    'hoursMon' => 1,
                    'hoursTue' => 2,
                    'hoursWed' => 3,
                    'hoursThu' => 4,
                    'hoursFri' => 5,
                    'hoursSat' => 6,
                    'hoursSun' => 7,
                    'hasOtherLicences' => 'N',
                    'hasOtherEmployment' => 'N',
                    'hasConvictions' => 'N',
                    'hasPreviousLicences' => 'N',
                    'additionalInfo' => 'TEXT',
                    'submit' => 'Y',
                ]
            ],
        ];

        return $commands;
    }

    public static function dpHandleCommandWithLgvAcquiredRightsReferenceNumber(): array
    {
        return [
            'NI' => [
                'isNi' => true,
                'expectedQualificationType' => TmQualification::QUALIFICATION_TYPE_NILGVAR,
            ],
            'non-NI' => [
                'isNi' => false,
                'expectedQualificationType' => TmQualification::QUALIFICATION_TYPE_LGVAR,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandWithLgvAcquiredRightsReferenceNumber')]
    public function testHandleCommandWithLgvAcquiredRightsReferenceNumber(mixed $isNi, mixed $expectedQualificationType): void
    {
        $commandData = [
            'id' => 863,
            'version' => 234,
            'dob' => '01 Aug 1990',
            'lgvAcquiredRightsReferenceNumber' => 'ABC1234',
            'homeAddress' => [
                'addressLine1' => 'LINE_1',
                'addressLine2' => 'LINE_2',
                'addressLine3' => 'LINE_3',
                'addressLine4' => 'LINE_4',
                'town' => 'TOWN',
                'postcode' => 'POSTCODE',
                'countryCode' => Country::ID_UNITED_KINGDOM,
            ],
            'workAddress' => [
                'addressLine1' => 'W_LINE_1',
                'addressLine2' => 'W_LINE_2',
                'addressLine3' => 'W_LINE_3',
                'addressLine4' => 'W_LINE_4',
                'town' => 'W_TOWN',
                'postcode' => 'W_POSTCODE',
                'countryCode' => Country::ID_GERMANY,
            ],
            'submit' => 'N',
        ];

        $command = Command::create($commandData);

        $tm = m::mock(TransportManager::class)->makePartial();
        $tm->shouldReceive('hasLgvAcquiredRightsQualification')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('isNi')
            ->withNoArgs()
            ->once()
            ->andReturn($isNi);

        $tma = new TransportManagerApplication();
        $tma->setId(863);
        $tma->setTransportManager($tm);
        $tma->getTransportManager()->setHomeCd(
            new ContactDetails(
                m::mock(RefData::class)
            )
        );
        $tma->getTransportManager()->getHomeCd()->setPerson(new Person());
        $tma->setApplication($application);

        $statusUpdateOn = new \DateTimeImmutable();

        $this->repoMap['TmQualification']
            ->shouldReceive('save')
            ->withArgs(function ($tmQualification) use ($tm, $expectedQualificationType, $statusUpdateOn) {
                $this->assertSame(
                    $tm,
                    $tmQualification->getTransportManager()
                );
                $this->assertSame(
                    $this->references[Country::class][Country::ID_UNITED_KINGDOM],
                    $tmQualification->getCountryCode()
                );
                $this->assertSame(
                    $this->refData[$expectedQualificationType],
                    $tmQualification->getQualificationType()
                );
                $this->assertEquals(
                    $statusUpdateOn,
                    $tmQualification->getIssuedDate()
                );
                $this->assertEquals('ABC1234', $tmQualification->getSerialNo());
                return true;
            })
            ->once();

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 234)
            ->andReturn($tma);

        $this->repoMap['Address']->shouldReceive('save')->once();
        $this->repoMap['ContactDetails']->shouldReceive('save')->once();

        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once();

        $this->acquiredRightsServiceMock->shouldReceive('isCheckEnabled')->once()->andReturn(true);
        $this->acquiredRightsServiceMock->shouldReceive('verifyAcquiredRightsByReference')->andReturn(new ApplicationReference(
            '6fcf9551-ade4-4b48-b078-6db59559a182',
            'ABC1234',
            ApplicationReference::APPLICATION_STATUS_SUBMITTED,
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            $statusUpdateOn,
        ));

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Transport Manager Application ID 863 updated'], $result->getMessages());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandWithLgvAcquiredRightsReferenceNumber')]
    public function testHandleCommandWithLgvAcquiredRightsReferenceNumberWithCheckDisabled(mixed $isNi, mixed $expectedQualificationType): void
    {
        $commandData = [
            'id' => 863,
            'version' => 234,
            'dob' => '01 Aug 1990',
            'lgvAcquiredRightsReferenceNumber' => 'ABC1234',
            'homeAddress' => [
                'addressLine1' => 'LINE_1',
                'addressLine2' => 'LINE_2',
                'addressLine3' => 'LINE_3',
                'addressLine4' => 'LINE_4',
                'town' => 'TOWN',
                'postcode' => 'POSTCODE',
                'countryCode' => Country::ID_UNITED_KINGDOM,
            ],
            'workAddress' => [
                'addressLine1' => 'W_LINE_1',
                'addressLine2' => 'W_LINE_2',
                'addressLine3' => 'W_LINE_3',
                'addressLine4' => 'W_LINE_4',
                'town' => 'W_TOWN',
                'postcode' => 'W_POSTCODE',
                'countryCode' => Country::ID_GERMANY,
            ],
            'submit' => 'N',
        ];

        $command = Command::create($commandData);

        $tm = m::mock(TransportManager::class)->makePartial();
        $tm->shouldReceive('hasLgvAcquiredRightsQualification')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('isNi')
            ->withNoArgs()
            ->once()
            ->andReturn($isNi);

        $tma = new TransportManagerApplication();
        $tma->setId(863);
        $tma->setTransportManager($tm);
        $tma->getTransportManager()->setHomeCd(
            new ContactDetails(
                m::mock(RefData::class)
            )
        );
        $tma->getTransportManager()->getHomeCd()->setPerson(new Person());
        $tma->setApplication($application);

        $this->repoMap['TmQualification']
            ->shouldReceive('save')
            ->withArgs(function ($tmQualification) use ($tm, $expectedQualificationType) {
                $this->assertSame(
                    $tm,
                    $tmQualification->getTransportManager()
                );
                $this->assertSame(
                    $this->references[Country::class][Country::ID_UNITED_KINGDOM],
                    $tmQualification->getCountryCode()
                );
                $this->assertSame(
                    $this->refData[$expectedQualificationType],
                    $tmQualification->getQualificationType()
                );
                $this->assertNull($tmQualification->getIssuedDate());
                $this->assertEquals('ABC1234', $tmQualification->getSerialNo());
                return true;
            })
            ->once();

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 234)
            ->andReturn($tma);

        $this->repoMap['Address']->shouldReceive('save')->once();
        $this->repoMap['ContactDetails']->shouldReceive('save')->once();

        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once();

        $this->acquiredRightsServiceMock->shouldReceive('isCheckEnabled')->andReturn(false);
        $this->acquiredRightsServiceMock->shouldNotReceive('verifyAcquiredRightsByReference');

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Transport Manager Application ID 863 updated'], $result->getMessages());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandWithLgvAcquiredRightsReferenceNumber')]
    public function testHandleCommandWithLgvAcquiredRightsReferenceNumberWithMissingStatusUpdateOn(mixed $isNi, mixed $expectedQualificationType): void
    {
        $commandData = [
            'id' => 863,
            'version' => 234,
            'dob' => '01 Aug 1990',
            'lgvAcquiredRightsReferenceNumber' => 'ABC1234',
            'homeAddress' => [
                'addressLine1' => 'LINE_1',
                'addressLine2' => 'LINE_2',
                'addressLine3' => 'LINE_3',
                'addressLine4' => 'LINE_4',
                'town' => 'TOWN',
                'postcode' => 'POSTCODE',
                'countryCode' => Country::ID_UNITED_KINGDOM,
            ],
            'workAddress' => [
                'addressLine1' => 'W_LINE_1',
                'addressLine2' => 'W_LINE_2',
                'addressLine3' => 'W_LINE_3',
                'addressLine4' => 'W_LINE_4',
                'town' => 'W_TOWN',
                'postcode' => 'W_POSTCODE',
                'countryCode' => Country::ID_GERMANY,
            ],
            'submit' => 'N',
        ];

        $command = Command::create($commandData);

        $tm = m::mock(TransportManager::class)->makePartial();
        $tm->shouldReceive('hasLgvAcquiredRightsQualification')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('isNi')
            ->withNoArgs()
            ->once()
            ->andReturn($isNi);

        $tma = new TransportManagerApplication();
        $tma->setId(863);
        $tma->setTransportManager($tm);
        $tma->getTransportManager()->setHomeCd(
            new ContactDetails(
                m::mock(RefData::class)
            )
        );
        $tma->getTransportManager()->getHomeCd()->setPerson(new Person());
        $tma->setApplication($application);

        $this->repoMap['TmQualification']
            ->shouldReceive('save')
            ->withArgs(function ($tmQualification) use ($tm, $expectedQualificationType) {
                $this->assertSame(
                    $tm,
                    $tmQualification->getTransportManager()
                );
                $this->assertSame(
                    $this->references[Country::class][Country::ID_UNITED_KINGDOM],
                    $tmQualification->getCountryCode()
                );
                $this->assertSame(
                    $this->refData[$expectedQualificationType],
                    $tmQualification->getQualificationType()
                );
                $this->assertNull($tmQualification->getIssuedDate());
                $this->assertEquals('ABC1234', $tmQualification->getSerialNo());
                return true;
            })
            ->once();

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 234)
            ->andReturn($tma);

        $this->repoMap['Address']->shouldReceive('save')->once();
        $this->repoMap['ContactDetails']->shouldReceive('save')->once();

        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once();

        $this->acquiredRightsServiceMock->shouldReceive('isCheckEnabled')->andReturn(false);
        $this->acquiredRightsServiceMock->shouldReceive('verifyAcquiredRightsByReference')->andReturn(new ApplicationReference(
            '6fcf9551-ade4-4b48-b078-6db59559a182',
            'ABC1234',
            ApplicationReference::APPLICATION_STATUS_SUBMITTED,
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            null,
        ));

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Transport Manager Application ID 863 updated'], $result->getMessages());
    }

    public function testHandleCommandWithLgvAcquiredRightsReferenceNumberAndQualificationAlreadySet(): void
    {
        $commandData = [
            'id' => 863,
            'version' => 234,
            'lgvAcquiredRightsReferenceNumber' => 'ABC1234',
            'homeAddress' => [
                'addressLine1' => 'LINE_1',
                'addressLine2' => 'LINE_2',
                'addressLine3' => 'LINE_3',
                'addressLine4' => 'LINE_4',
                'town' => 'TOWN',
                'postcode' => 'POSTCODE',
                'countryCode' => Country::ID_UNITED_KINGDOM,
            ],
            'workAddress' => [
                'addressLine1' => 'W_LINE_1',
                'addressLine2' => 'W_LINE_2',
                'addressLine3' => 'W_LINE_3',
                'addressLine4' => 'W_LINE_4',
                'town' => 'W_TOWN',
                'postcode' => 'W_POSTCODE',
                'countryCode' => Country::ID_GERMANY,
            ],
            'submit' => 'N',
        ];

        $command = Command::create($commandData);

        $tm = m::mock(TransportManager::class)->makePartial();
        $tm->shouldReceive('hasLgvAcquiredRightsQualification')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $tma = new TransportManagerApplication();
        $tma->setId(863);
        $tma->setTransportManager($tm);
        $tma->getTransportManager()->setHomeCd(
            new ContactDetails(
                m::mock(RefData::class)
            )
        );
        $tma->getTransportManager()->getHomeCd()->setPerson(new Person());

        $this->repoMap['TmQualification']
            ->shouldReceive('save')
            ->never();

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, 234)
            ->andReturn($tma);

        $this->repoMap['Address']->shouldReceive('save')->once();
        $this->repoMap['ContactDetails']->shouldReceive('save')->once();

        $this->repoMap['TransportManagerApplication']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Transport Manager Application ID 863 updated'], $result->getMessages());
    }
}
