<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Search;

use Dvsa\Olcs\Api\Domain\QueryHandler\Search\Licence as LicenceQueryHandler;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence as TransportManagerLicenceEntity;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle as VehicleEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence as OtherLicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary as CompanySubsidiaryEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Transfer\Query\Search\Licence as Qry;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see LicenceQueryHandler
 */
class LicenceTest extends QueryHandlerTestCase
{
    public $entityAccessLogger;
    public $authorizationService;
    /**
     * @var LicenceQueryHandler
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function handleQueryIsCallable(): void
    {
        // Assert
        $this->assertIsCallable($this->sut->handleQuery(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('handleQueryIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function handleQueryLegacyTest(): void
    {
        $query = Qry::create(['id' => 1]);
        $licenceId = 7;

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence
            ->shouldReceive('getAllTradingNames')
            ->andReturn(['foo', 'bar']);

        /** @var TrafficAreaEntity $trafficArea */
        $trafficArea = m::mock(TrafficAreaEntity::class);
        $trafficArea
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);
        $licence->setTrafficArea($trafficArea);

        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class);
        $organisation
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);

        /** @var PersonEntity $person */
        $person = m::mock(PersonEntity::class)->makePartial();
        $person->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);

        $organisationPersons = [
            $person
        ];

        $organisation
            ->shouldReceive('getOrganisationPersons')
            ->andReturn($organisationPersons);

        $licence->setOrganisation($organisation);

        /** @var TransportManagerLicenceEntity $person */
        $tmLicence = m::mock(TransportManagerLicenceEntity::class)->makePartial();
        $tmLicence->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);

        $tmLicences = [
            $tmLicence
        ];

        $licence->setTmLicences($tmLicences);

        /** @var OperatingCentreEntity $operatingCentre */
        $operatingCentre = m::mock(OperatingCentreEntity::class)->makePartial();
        $operatingCentre->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);

        $operatingCentres = [
            $operatingCentre
        ];

        $licence->setOperatingCentres($operatingCentres);

        /** @var VehicleEntity $vehicle */
        $vehicle = m::mock(VehicleEntity::class)->makePartial();
        $vehicle->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);

        $licenceVehicles = [
            $vehicle
        ];

        $licence->setLicenceVehicles($licenceVehicles);

        $licence->shouldReceive('getActiveVehiclesCount')
            ->andReturn(count($licenceVehicles));
        $licence->shouldReceive('getActiveVehicles')
            ->andReturn($licenceVehicles);
        $licence->shouldReceive('getPiRecordCount')
            ->andReturn(2);
        $licence->shouldReceive('getActiveCommunityLicences')
            ->andReturn(['foo' => 'bar']);

        /** @var CompanySubsidiaryEntity $companySubsidiary */
        $companySubsidiary = m::mock(CompanySubsidiaryEntity::class)->makePartial();
        $companySubsidiary->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);

        $companySubsidiaries = [
            $companySubsidiary
        ];

        $licence->setCompanySubsidiaries($companySubsidiaries);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);
        $application->shouldReceive('getOutOfRepresentationDate')
            ->andReturn('OOR');
        $application->shouldReceive('determinePublishedDate')
            ->andReturn('23/09/2000');
        $application->shouldReceive('determinePublicationNo')
            ->andReturn('1234');
        $application->shouldReceive('getOutOfOppositionDate')
            ->andReturn('OOO');
        $application->shouldReceive('hasOpposition')
            ->andReturn(true);
        $applications = [
            $application
        ];

        $licence->setApplications($applications);

        /** @var ConditionUndertakingEntity $conditionUndertaking */
        $conditionUndertaking = m::mock(ConditionUndertakingEntity::class)->makePartial();
        $conditionUndertaking->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);

        $conditionUndertakings = [
            $conditionUndertaking
        ];

        $licence->setConditionUndertakings($conditionUndertakings);

        /** @var OtherLicenceEntity $otherLicence */
        $otherLicence = m::mock(OtherLicenceEntity::class)->makePartial();
        $otherLicence->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);

        $otherLicences = [
            $otherLicence
        ];

        $licence->shouldReceive('getOtherActiveLicences')
            ->andReturn($otherLicences);

        /** @var AddressEntity $address */
        $address = m::mock(AddressEntity ::class)->makePartial();

        /** @var PhoneContactEntity $phoneContact */
        $phoneContact = m::mock(PhoneContactEntity::class)->makePartial();
        $phoneContact->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);
        $phoneContacts = [
            $phoneContact
        ];

        /** @var ContactDetailsEntity $contactDetails */
        $contactDetails = m::mock(ContactDetailsEntity::class);
        $contactDetails
            ->shouldReceive('getPhoneContacts')
            ->andReturn($phoneContacts)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('getAddress')
            ->andReturn($address)
            ->shouldReceive('getEmailAddress')
            ->andReturn('emailAddressFoo');

        $licence->setCorrespondenceCd($contactDetails);

        $licence
            ->setId($licenceId)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licence);

        $organisation->shouldReceive('getDisqualificationStatus')->with()->once()->andReturn('DIS_STATUS');

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\QueryHandler\Result::class, $result);
        $this->entityAccessLogger->shouldHaveReceived('logAccessToEntity', [$licence]);
    }

    public function setUp(): void
    {
        $this->sut = new LicenceQueryHandler();
        $this->mockRepo('Licence', LicenceRepo::class);

        $this->authorizationService = m::mock(AuthorizationService::class);
        $this->authorizationService->shouldReceive('getIdentity->getUser->isAnonymous')->withNoArgs()->andReturn(true);

        $this->mockedSmServices = [
            AuthorizationService::class => $this->authorizationService,
        ];

        parent::setUp();
    }
}
