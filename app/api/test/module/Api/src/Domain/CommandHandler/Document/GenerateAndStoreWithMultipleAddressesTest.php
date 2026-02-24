<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseCompany as CompaniesHouseCompanyRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as CompaniesHouseCompanyEntity;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseInsolvencyPractitioner;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Service\Document\DocumentGenerator;
use Dvsa\Olcs\Api\Service\Document\NamingService;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStoreWithMultipleAddresses as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\GenerateAndStoreWithMultipleAddresses as CommandHandler;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class GenerateAndStoreWithMultipleAddressesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Document', Document::class);
        $this->mockRepo('Licence', Licence::class);
        $this->mockRepo('CompaniesHouseCompany', CompaniesHouseCompanyRepo::class);

        $this->mockedSmServices['DocumentNamingService'] = m::mock(NamingService::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);
        $this->mockedSmServices['DocumentGenerator'] = m::mock(DocumentGenerator::class);

        parent::setUp();
    }

    /**
     * testHandleCommand
     */
    public function testHandleCommand(): void
    {

        $mockCommand = m::mock(Cmd::class);
        $mockLicence = m::mock(Licence::class);

        $this->setUpMockLicence($mockLicence);
        $this->setUpMockCommand($mockCommand);

        $result = new Result();

        foreach ($this->getAddresses() as $address) {
            $this->expectedSideEffect(GenerateAndStore::class, [], $result);
        }

        $this->sut->handleCommand($mockCommand);
    }

    protected function getAddresses(): mixed
    {
        $addresses = [];
        $addresses['correspondenceAddress'] = $this->addressProvider('correspondenceAddress')['correspondenceAddress'];
        return $addresses;
    }

    public function testEmptyAddress(): void
    {

        $mockCommand = m::mock(Cmd::class);
        $this->setUpMockCommand($mockCommand);
        $mockLicence = m::mock(Licence::class);
        $this->setUpMockLicence($mockLicence, true);
        $result = new Result();
        $addresses = $this->getAddresses();
        /** @var Address $correspondenceAddress */
        $correspondenceAddress = $addresses['correspondenceAddress']->getAddress();
        $correspondenceAddress->setAddressLine1(null);
        $correspondenceAddress->setAddressLine2(null);
        $correspondenceAddress->setAddressLine3(null);
        $correspondenceAddress->setAddressLine4(null);
        $count = 0;
        foreach ($addresses as $key => $address) {
            if ($key != 'correspondenceAddress') {
                $this->expectedSideEffect(GenerateAndStore::class, [], $result);
            }
            $count++;
        }
        $this->sut->handleCommand($mockCommand);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('insolvencyPractitioners')]
    public function testInsolvencyPractitioner(mixed $practitioners, mixed $expected): void
    {
        $mockCommand = m::mock(Cmd::class);
        $mockCommand->shouldReceive('getGenerateCommandData')->andReturn(
            [
                'description' => ' REG 29 Standard',
                'licence' => 1
            ]
        )->getMock()
            ->shouldReceive('getAddressBookmark')->andReturn('licence_holder_address')
            ->getMock()
            ->shouldReceive('getBookmarkBundle')->andReturn([
                'correspondenceCd' => ['address']
            ])->getMock()
            ->shouldReceive('getSendToAddresses')->andReturn([
                'correspondenceAddress' => false,
                'insolvencyPractitionerAddresses' => true
            ]);
        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('getOrganisation->getCompanyOrLlpNo')->andReturn('1234567');
        $this->setUpMockLicence($mockLicence, true);

        $mockPractitioners = [];
        foreach ($practitioners as $practitioner) {
            $mockPractitioner = m::mock(CompaniesHouseInsolvencyPractitioner::class);
            $mockPractitioner->shouldReceive('getName')->andReturn($practitioner['name']);
            $mockPractitioner->shouldReceive('getAddressLine1')->andReturn($practitioner['addressLine1']);
            $mockPractitioner->shouldReceive('getAddressLine2')->andReturn($practitioner['addressLine2']);
            $mockPractitioner->shouldReceive('getAddressLine3')->andReturn($practitioner['addressLine3']);
            $mockPractitioner->shouldReceive('getLocality')->andReturn($practitioner['locality']);
            $mockPractitioner->shouldReceive('getPostalCode')->andReturn($practitioner['postalCode']);
            $mockPractitioners[] = $mockPractitioner;
        }

        $mockCompany = m::mock(CompaniesHouseCompanyEntity::class);
        $mockCompany->shouldReceive('getInsolvencyPractitioners')->andReturn(
            new ArrayCollection($mockPractitioners)
        );

        $this->repoMap['CompaniesHouseCompany']
            ->shouldReceive('getLatestByCompanyNumber')
            ->with('1234567')
            ->andReturn($mockCompany);

        $this->expectedSideEffect(GenerateAndStore::class, [], new Result(), $expected);

        $this->sut->handleCommand($mockCommand);
    }

    protected function addressProvider(mixed $addressType, bool $skipCorrespondence = false): array
    {

        if ($addressType === 'operatingCentresAddresses') {
            return [
                m::mock(LicenceOperatingCentre::class)->shouldReceive('getOperatingCentre')->andReturn(
                    m::mock(OperatingCentre::class)->shouldReceive('getAddress')->andReturn(
                        $this->addressProvider('operatingCentreAddress')
                    )->getMock()
                )->getMock()
            ];
        }
        $contactDetails = m::mock(ContactDetails::class)->makePartial();

        $address = new Address();
        $address->updateAddress(
            "DVSA - " . $addressType,
            "THe Axis Building",
            "Nottingham",
            "Nottinghamshire",
            "Nottingham",
            "NG1 6LP",
            new Country()
        );
        if ($skipCorrespondence) {
            $address = new Address();
        }
        $contactDetails->setAddress($address);
        return [$addressType => $contactDetails];
    }

    /**
     * getMockLicence
     *
     * @param $mockLicence
     */
    protected function setUpMockLicence(mixed $mockLicence, bool $noCorrespondanceAddress = false): void
    {
        $this->repoMap['Licence']->shouldReceive('fetchWithAddressesUsingId')->andReturn(
            $mockLicence->shouldReceive('getCorrespondenceCd')->andReturn(
                $this->addressProvider('correspondenceAddress', $noCorrespondanceAddress)['correspondenceAddress']
            )->getMock()
                ->shouldReceive('getEstablishmentCd')
                ->andReturn(
                    $this->addressProvider('establishmentAddress')['establishmentAddress']
                )
                ->getMock()
                ->shouldReceive('getTransportConsultantCd')
                ->andReturn(
                    $this->addressProvider('transportConsultantAddress')['transportConsultantAddress']
                )
                ->getMock()
                ->shouldReceive('getOrganisation')
                ->andReturn(
                    m::mock(Organisation::class)->shouldReceive('getContactDetails')->andReturn(
                        $this->addressProvider('registeredAddress')['registeredAddress']
                    )->getMock()
                )
                ->getMock()
        );
        $mockLicence->shouldReceive('getOperatingCentres')->andReturn(
            [
                m::mock(LicenceOperatingCentre::class)->shouldReceive('getOperatingCentre')->andReturn(
                    m::mock(OperatingCentre::class)->shouldReceive('getAddress')->andReturn(
                        m::mock(Address::class)->shouldReceive('serialize')->andReturn(
                            $this->addressProvider('operatingCentreAddress')['operatingCentreAddress']->getAddress()->serialize()
                        )->getMock()
                    )->getMock()
                )->getMock()
            ]
        );
    }

    /**
     * setUpMockCommand
     *
     * @param $mockCommand
     */
    private function setUpMockCommand(mixed $mockCommand): void
    {
        $mockCommand->shouldReceive('getGenerateCommandData')->andReturn(
            [
                'description' => ' Last TM letter Licence 1',
                'licence' => 1
            ]
        )->getMock()
            ->shouldReceive('getAddressBookmark')->andReturn('licence_holder_address')
            ->getMock()
            ->shouldReceive('getBookmarkBundle')->andReturn([
                'correspondenceCd' => ['address']
            ])->getMock()
            ->shouldReceive('getSendToAddresses')->andReturn($this->getAddresses());
    }

    public static function insolvencyPractitioners(): array
    {
        return [
            'One practitioner' => [
                'practitioners' => [
                    [
                        'name' => 'Jim',
                        'addressLine1' => 'Address Line 1',
                        'addressLine2' => 'Address Line 2',
                        'addressLine3' => 'Address Line 3',
                        'locality' => 'Somewhere',
                        'postalCode' => 'AB1 2CE',
                    ]
                ],
                'expected' => 1

            ],
            'Two practitioners' => [
                'practitioners' => [
                    [
                        'name' => 'Jim Carrey',
                        'addressLine1' => 'Address Line 1',
                        'addressLine2' => 'Address Line 2',
                        'addressLine3' => 'Address Line 3',
                        'locality' => 'Somewhere',
                        'postalCode' => 'AB1 2CE',
                    ],
                    [
                        'name' => 'Bob Hoskins',
                        'addressLine1' => 'Address Line 1',
                        'addressLine2' => 'Address Line 2',
                        'addressLine3' => 'Address Line 3',
                        'locality' => 'Somewhere',
                        'postalCode' => 'AB1 2CE',
                    ]
                ],
                'expected' => 2
            ],
            'Three practitioners with a same entry' => [
                'practitioners' => [
                    [
                        'name' => 'Jim Carrey',
                        'addressLine1' => 'Address Line 1',
                        'addressLine2' => 'Address Line 2',
                        'addressLine3' => 'Address Line 3',
                        'locality' => 'Somewhere',
                        'postalCode' => 'AB1 2CE',
                    ],
                    [
                        'name' => 'Jim Carrey',
                        'addressLine1' => 'Address Line 1',
                        'addressLine2' => 'Address Line 2',
                        'addressLine3' => 'Address Line 3',
                        'locality' => 'Somewhere',
                        'postalCode' => 'AB1 2CE',
                    ],
                    [
                        'name' => 'Bob Hoskins',
                        'addressLine1' => 'Address Line 1',
                        'addressLine2' => 'Address Line 2',
                        'addressLine3' => 'Address Line 3',
                        'locality' => 'Somewhere',
                        'postalCode' => 'AB1 2CE',
                    ]
                ],
                'expected' => 2
            ]
        ];
    }
}
