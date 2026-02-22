<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Cases\Complaint;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\Cases\Conviction;
use Dvsa\Olcs\Api\Entity\Cases\Statement;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\Opposition\Opposer;
use Dvsa\Olcs\Api\Entity\Opposition\Opposition;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\Prohibition\Prohibition;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement;
use Dvsa\Olcs\Api\Entity\Si\SiCategory;
use Dvsa\Olcs\Api\Entity\Si\SiCategoryType;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruImposed;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyImposedType;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyType;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Tm\TmEmployment;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Laminas\View\Renderer\PhpRenderer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class AbstractSubmissionSectionTestCase extends MockeryTestCase
{
    protected $submissionSection = '';
    protected const LICENCE_STATUS = 'lic_status';
    protected const ORGANISATION_TYPE = 'org_type';
    protected const LICENCE_TYPE = 'lic_type';
    protected const GOODS_OR_PSV = 'goods';
    protected const NATURE_OF_BUSINESS = 'nob1';

    #[DataProvider('sectionTestProvider')]
    public function testGenerateSection(mixed $input, mixed $expectedResult = null): void
    {
        if (!empty($input)) {
            $mockQueryHandler = m::mock(QueryHandlerManager::class);
            $mockViewRenderer = m::mock(PhpRenderer::class);
            $sut = new $this->submissionSection($mockQueryHandler, $mockViewRenderer);

            $this->mockSetRepos($sut);

            $section = $sut->generateSection($input);
            $this->assertEquals($expectedResult, $section);
        } else {
            $this->markTestSkipped('Skipping, no input');
        }
    }

    abstract public static function sectionTestProvider(): array;

    /**
     * Return a case attached to an application
     *
     * @return CasesEntity
     */
    protected static function getApplicationCase(): mixed
    {
        $case = static::getCase();
        $case->setCaseType(new RefData('case_t_app'));
        $application = static::generateApplication(
            852,
            $case->getLicence(),
            Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
            false
        );

        $case->setApplication($application);

        return $case;
    }

    public static function getCase(): mixed
    {
        $openDate = new \DateTime('2012-01-01 15:00:00');
        $caseType = new RefData('case_t_app');
        $caseType->setDescription('case type 1');

        $categorys = new ArrayCollection(['cat1', 'cat2']);
        $outcomes = new ArrayCollection(['out1']);

        $organisation = static::generateOrganisation();
        $licence = static::generateLicence($organisation, 7);

        $application = static::generateApplication(344, $licence, Application::APPLICATION_STATUS_GRANTED);

        $ecmsNo = 'ecms1234';
        $description = 'case description';
        $transportManager = static::generateTransportManager(43);

        $tmApplications = new ArrayCollection();
        $tmApplications->add(
            static::generateTransportManagerApplication(
                522,
                static::generateTransportManager(216),
                Application::APPLICATION_STATUS_GRANTED
            )
        );

        $application->setTransportManagers($tmApplications);

        $tmLicences = new ArrayCollection();
        $tmLicences->add(
            static::generateTransportManagerLicence(
                234,
                $licence,
                $transportManager
            )
        );
        $licence->setTmLicences($tmLicences);

        $transportManager->setTmApplications($tmApplications);
        $transportManager->setTmLicences($tmLicences);
        $transportManager->setEmployments(static::generateArrayCollection('TmEmployment'));
        $transportManager->setOtherLicences(static::generateArrayCollection('OtherLicence'));
        $transportManager->setPreviousConvictions(static::generateArrayCollection('PreviousConviction'));

        $case = new CasesEntity(
            $openDate,
            $caseType,
            $categorys,
            $outcomes,
            $application,
            $licence,
            $transportManager,
            $ecmsNo,
            $description
        );

        $case->setId(99);
        $case->setAnnualTestHistory('ath');

        $case->setComplaints(static::generateComplaints($case));
        $case->setStatements(static::generateStatements($case));
        $case->setOppositions(static::generateOppositions($case));

        $case->setConvictions(static::generateConvictions());
        $case->setConvictionNote('conv_note1');

        $case->setSeriousInfringements(static::generateSeriousInfringements());
        $case->setErruRequest(static::generateErruRequest());
        $case->setPenaltiesNote('pen-notes1');

        $case->setProhibitionNote('prohibition-note');
        $case->setProhibitions(static::generateArrayCollection('Prohibition'));

        $cu = static::generateConditionsUndertakings(
            $case,
            ConditionUndertaking::TYPE_CONDITION,
            29
        );

        $case->setConditionUndertakings($cu);

        return $case;
    }

    protected static function generateRefDataEntity(mixed $id, string $description = 'desc'): mixed
    {
        $refData = new RefData($id);
        $refData->setDescription($id . '-' . $description);

        return $refData;
    }

    protected static function generatePerson(mixed $id): mixed
    {
        $person = new Person($id);
        $person->setId($id);
        $person->setTitle(static::generateRefDataEntity('title'));
        $person->setForename('fn' . $id);
        $person->setFamilyName('sn' . $id);
        $person->setBirthDate(new \DateTime('1977-01-' . $id));
        $person->setBirthPlace('bp');

        return $person;
    }

    protected static function generateTransportManager(mixed $id): mixed
    {
        $tm = new TransportManager($id);
        $tm->setId($id);
        $tm->setVersion(($id + 10));
        $tm->setTmType(static::generateRefDataEntity('tmType'));

        $tm->setHomeCd(static::generateContactDetails(533, ContactDetails::CONTACT_TYPE_REGISTERED_ADDRESS));
        $tm->setWorkCd(static::generateContactDetails(343, ContactDetails::CONTACT_TYPE_CORRESPONDENCE_ADDRESS));

        $tm->setQualifications(static::generateArrayCollection('tmQualification'));

        return $tm;
    }

    protected static function generateTransportManagerApplication(
        mixed $id,
        mixed $transportManager,
        mixed $applicationStatus = Application::APPLICATION_STATUS_UNDER_CONSIDERATION
    ): TransportManagerApplication {
        $entity = new TransportManagerApplication($id);
        $entity->setId($id);
        $entity->setTransportManager($transportManager);
        $entity->setHoursMon(1);
        $entity->setHoursTue(2);
        $entity->setHoursWed(3);
        $entity->setHoursThu(4);
        $entity->setHoursFri(5);
        $entity->setHoursSat(6);
        $entity->setHoursSun(7);

        $organisation = new Organisation();
        $organisationType = static::generateRefDataEntity(static::ORGANISATION_TYPE);
        $organisation->setType($organisationType);
        $organisation->setName('Org name');

        $licence = static::generateLicence($organisation, 55);

        $entity->setApplication(
            static::generateApplication(852, $licence, $applicationStatus, false)
        );

        return $entity;
    }

    protected static function generateTransportManagerLicence(mixed $id, mixed $licence, mixed $transportManager): mixed
    {
        $entity = new TransportManagerLicence($licence, $transportManager);
        $entity->setId($id);
        $entity->setHoursMon(1);
        $entity->setHoursTue(2);
        $entity->setHoursWed(3);
        $entity->setHoursThu(4);
        $entity->setHoursFri(5);
        $entity->setHoursSat(6);
        $entity->setHoursSun(7);

        return $entity;
    }

    protected static function generateOrganisation(): mixed
    {
        $organisation = new Organisation();
        $organisationType = static::generateRefDataEntity(static::ORGANISATION_TYPE);
        $organisation->setType($organisationType);
        $organisation->setName('Org name');
        $organisation->setNatureOfBusiness(static::NATURE_OF_BUSINESS);

        $organisationPersons = new ArrayCollection();
        $organisationPerson = new OrganisationPerson();
        $organisationPerson->setPerson(static::generatePerson(1));
        $organisationPersons->add($organisationPerson);
        $organisation->setOrganisationPersons($organisationPersons);

        $organisationLicences = new ArrayCollection();
        $applications = new ArrayCollection();

        for ($i = 1; $i < 3; $i++) {
            $licence = static::generateLicence($organisation, $i);
            $application = static::generateApplication($i, $licence, Application::APPLICATION_STATUS_GRANTED);

            if ($i == 1) {
                // assign some tms to the first application
                $application->setTransportManagers(
                    static::generateTransportManagerApplications()
                );
            }
            $applications->add($application);

            $applications->add(
                static::generateApplication((100 + $i), $licence, Application::APPLICATION_STATUS_UNDER_CONSIDERATION)
            );

            $licence->setApplications($applications);

            $organisationLicences->add($licence);
        }
        $organisation->setLicences($organisationLicences);

        $organisation->setLeadTcArea(static::generateTrafficArea('B'));

        return $organisation;
    }

    protected static function generateLicence(Organisation $organisation, mixed $id = null): mixed
    {
        $licence = m::mock(Licence::class)->makePartial();
        $licence->initCollections();
        $licence->setOrganisation($organisation);
        $licence->setStatus(static::generateRefDataEntity(static::LICENCE_STATUS));

        $licence->setId($id);
        $licence->setVersion($id);
        $licence->setLicenceType(static::generateRefDataEntity(static::LICENCE_TYPE));
        $licence->setGoodsOrPsv(static::generateRefDataEntity(static::GOODS_OR_PSV));
        $licence->setLicNo('OB12345');
        $licence->setTotAuthTrailers(5);

        $licence->setLicenceVehicles(static::generateLicenceVehicles($licence));

        $licence->setOperatingCentres(static::generateLicenceOperatingCentres($licence));

        $licence->setApplications(static::generateApplications($licence));
        $licence->setTmLicences(static::generateTmLicences($licence));

        $licence->setConditionUndertakings(
            static::generateConditionsUndertakings(
                $licence,
                ConditionUndertaking::TYPE_CONDITION,
                58,
                null,
                null,
                new \DateTime('2014-01-01')
            )
        );

        return $licence;
    }

    protected static function generateLicenceVehicles(mixed $licence): mixed
    {
        $licenceVehicles = new ArrayCollection();
        $vehicle = new Vehicle();

        $lv = new \Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle($licence, $vehicle);
        $lv->setSpecifiedDate(new \DateTime('2000-01-01'));

        $licenceVehicles->add($lv);
        $licenceVehicles->add($lv);
        $licenceVehicles->add($lv);

        return $licenceVehicles;
    }

    protected static function generateTmLicences(Licence $licence): mixed
    {
        $licenceTms = new ArrayCollection();

        $tm = new TransportManager();
        $tm->setTmType(static::generateRefDataEntity('tm_type1'));
        $tm->setId(153);
        $tm->setVersion(306);
        $tm->setHomeCd(static::generateContactDetails(83));

        $tm->setOtherLicences([]);
        $tml = new TransportManagerLicence($licence, $tm);

        $licenceTms->add($tml);

        return $licenceTms;
    }

    protected static function generateTransportManagerApplications(): mixed
    {
        $applicationTms = new ArrayCollection();

        $tm = new TransportManager();
        $tm->setTmType(static::generateRefDataEntity('tm_type1'));
        $tm->setId(153);
        $tm->setVersion(306);
        $tm->setHomeCd(static::generateContactDetails(83));

        $tm->setOtherLicences(static::generateArrayCollection('OtherLicence', 1));
        $tm->setQualifications(static::generateArrayCollection('tmQualification'));

        $tma = new TransportManagerApplication();
        $tma->setTransportManager($tm);

        $applicationTms->add($tma);

        return $applicationTms;
    }

    protected static function generateOtherLicence(mixed $id): mixed
    {
        $entity = new OtherLicence();
        $entity->setId($id);
        $entity->setVersion($id + 2);
        $entity->setLicNo($id . '-licNo');
        $entity->setHolderName($id . '-holderName');

        $organisation = new Organisation();
        $organisationType = static::generateRefDataEntity(static::ORGANISATION_TYPE);
        $organisation->setType($organisationType);
        $organisation->setName('Org name');

        $licence = static::generateLicence($organisation, 55);

        $entity->setApplication(
            static::generateApplication(2255, $licence, Application::APPLICATION_STATUS_UNDER_CONSIDERATION)
        );
        return $entity;
    }

    protected static function generateConditionsUndertakings(
        mixed $parentEntity,
        mixed $condType,
        int $id = 1,
        mixed $addedVia = null,
        mixed $attachTo = null,
        mixed $createdOn = null
    ): ArrayCollection {
        $cu = new ConditionUndertaking(static::generateRefDataEntity($condType), 'Y', 'N');

        $addedViaByParent = null;
        $attachToByParent = null;

        if ($parentEntity instanceof Licence) {
            $addedViaByParent = ConditionUndertaking::ADDED_VIA_LICENCE;
            $attachToByParent = ConditionUndertaking::ATTACHED_TO_LICENCE;
        } elseif ($parentEntity instanceof Application) {
            $addedViaByParent = ConditionUndertaking::ADDED_VIA_APPLICATION;
            $attachToByParent = ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE;

            $cu->setOperatingCentre(static::generateOperatingCentre());
        } elseif ($parentEntity instanceof CasesEntity) {
            $addedViaByParent = ConditionUndertaking::ADDED_VIA_CASE;
            $attachToByParent = ConditionUndertaking::ATTACHED_TO_LICENCE;

            $cu->setCase($parentEntity);
        }

        $cu
            ->setId($id)
            ->setVersion((100 + $id))
            ->setCreatedOn($createdOn ?: new \DateTime('2011-01-23'))
            ->setAddedVia(
                static::generateRefDataEntity($addedVia ?: $addedViaByParent)
            )
            ->setAttachedTo(
                static::generateRefDataEntity($attachTo ?: $attachToByParent)
            );

        $conditionUndertakings = new ArrayCollection();
        $conditionUndertakings->add($cu);

        return $conditionUndertakings;
    }

    protected static function generateTmQualification(mixed $id): mixed
    {
        $entity = new TmQualification();
        $entity->setId($id);
        $entity->setVersion(($id + 4));
        $entity->setQualificationType(static::generateRefDataEntity('tm-qual'));
        $entity->setCountryCode(static::generateCountry('GB'));
        $entity->setSerialNo('12344321');
        $entity->setIssuedDate(new \DateTime('2008-12-04'));

        return $entity;
    }

    protected static function generateLicenceOperatingCentres(mixed $licence): mixed
    {
        $operatingCentres = new ArrayCollection();

        for ($i = 1; $i <= 2; $i++) {
            $operatingCentre = static::generateOperatingCentre($i);
            $loc = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $operatingCentre);
            $loc->setNoOfVehiclesRequired(6);
            $loc->setNoOfTrailersRequired(4);
            $operatingCentres->add($loc);
        }

        return $operatingCentres;
    }

    protected static function generateOperatingCentre(int $i = 1): mixed
    {
        $operatingCentre = new OperatingCentre();
        $operatingCentre->setId($i);
        $operatingCentre->setVersion($i);

        $address = static::generateAddress($i);
        $operatingCentre->setAddress($address);
        return $operatingCentre;
    }

    protected static function generateAddress(mixed $id): mixed
    {
        $address = new Address($id);
        $address->setId($id);
        $address->setAddressLine1($id . '_a1');
        $address->setAddressLine2($id . '_a2');
        $address->setAddressLine3($id . '_a3');
        $address->setTown($id . 't');
        $address->setPostcode('pc' . $id . '1PC');

        return $address;
    }

    protected static function generateApplications(Licence $licence): mixed
    {
        $applications = new ArrayCollection();
        $grantedApp = static::generateApplication(63, $licence, Application::APPLICATION_STATUS_GRANTED);

        $grantedApp->setConditionUndertakings(
            static::generateConditionsUndertakings(
                $grantedApp,
                ConditionUndertaking::TYPE_UNDERTAKING,
                88
            )
        );

        $applications->add(
            static::generateApplication(75, $licence, Application::APPLICATION_STATUS_NOT_SUBMITTED)
        );

        $applications->add(
            static::generateApplication(75, $licence, Application::APPLICATION_STATUS_REFUSED)
        );

        $applications->add(
            static::generateApplication(75, $licence, Application::APPLICATION_STATUS_GRANTED, true)
        );

        $applications->add(
            static::generateApplication(777, $licence, Application::APPLICATION_STATUS_UNDER_CONSIDERATION, true)
        );

        return $applications;
    }

    protected static function generateApplication(mixed $id, Licence $licence, mixed $status, bool $isVariation = false): mixed
    {
        $application = m::mock(Application::class)->makePartial();
        $application->initCollections();
        $application->setLicence($licence);
        $application->setStatus(static::generateRefDataEntity($status));
        $application->setIsVariation($isVariation);

        $application->setId($id);
        $application->setVersion(($id * 2));
        $application->setReceivedDate(new \DateTime('2014-05-05'));
        $application->setGoodsOrPsv(static::generateRefDataEntity('goods'));
        $application->setVehicleType(static::generateRefDataEntity(RefData::APP_VEHICLE_TYPE_HGV));
        $application->setLicenceType(static::generateRefDataEntity('lic_type'));

        $application->setConditionUndertakings(
            static::generateConditionsUndertakings(
                $application,
                ConditionUndertaking::TYPE_UNDERTAKING,
                34
            )
        );

        return $application;
    }

    protected static function generateTrafficArea(mixed $id): mixed
    {
        $ta = new TrafficArea();

        $ta->setId($id);
        $ta->setName('FOO');

        return $ta;
    }

    protected static function generateContactDetails(mixed $id, string $type = 'cd_type'): mixed
    {
        $cd = new ContactDetails(static::generateRefDataEntity($type));
        $cd->setAddress(static::generateAddress($id));
        $cd->setPerson(static::generatePerson(22));
        $cd->setEmailAddress('blah@blah.com');

        return $cd;
    }

    protected static function generateComplaints(CasesEntity $case): mixed
    {
        $complaints = new ArrayCollection();

        // add compliance complaint
        $complaints->add(
            static::generateComplaint(
                253,
                $case,
                static::generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                1,
                '04-05-2006'
            )
        );
        $complaints->add(
            static::generateComplaint(
                543,
                $case,
                static::generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                1,
                '03-05-2006'
            )
        );
        $complaints->add(
            static::generateComplaint(
                563,
                $case,
                static::generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                1,
                null
            )
        );

        // add env complaint
        $complaints->add(
            static::generateComplaint(
                253,
                $case,
                static::generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                0,
                '04-05-2006'
            )
        );
        $complaints->add(
            static::generateComplaint(
                543,
                $case,
                static::generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                0,
                '03-05-2006'
            )
        );
        $complaints->add(
            static::generateComplaint(
                563,
                $case,
                static::generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                0,
                null
            )
        );
        return $complaints;
    }

    protected static function generateComplaint(
        mixed $id,
        CasesEntity $case,
        ContactDetails $contactDetails,
        int $isCompliance = 1,
        mixed $complaintDate = null
    ): Complaint {
        $complaint = new Complaint(
            $case,
            (bool) $isCompliance,
            static::generateRefDataEntity(Complaint::COMPLAIN_STATUS_OPEN),
            new \DateTime($complaintDate ?? 'now'),
            $contactDetails
        );

        if (!$complaintDate) {
            $complaint->setComplaintDate(null);
        }

        $complaint->setId($id);
        $complaint->setVersion(($id + 2));
        $complaint->setIsCompliance($isCompliance);

        if (!$isCompliance) {
            $complaint->setOperatingCentres(new ArrayCollection([static::generateOperatingCentre(633)]));
        }
        return $complaint;
    }

    protected static function generateStatements(CasesEntity $case): mixed
    {
        $statements = new ArrayCollection();

        $statements->add(
            static::generateStatement(253, $case)
        );

        return $statements;
    }

    protected static function generateStatement(mixed $id, CasesEntity $case): mixed
    {
        $entity = new Statement($case, static::generateRefDataEntity('statement_type1'));
        $entity->setId($id);
        $entity->setVersion(($id + 2));
        $entity->setRequestedDate(new \DateTime('2008-08-11'));
        $entity->setRequestorsContactDetails(
            static::generateContactDetails(
                744,
                ContactDetails::CONTACT_TYPE_COMPLAINANT
            )
        );
        $entity->setStoppedDate(new \DateTime('2009-03-26'));
        $entity->setRequestorsBody('req body');
        $entity->setIssuedDate(new \DateTime('2009-03-30'));
        $entity->setVrm('VR12 MAB');

        return $entity;
    }

    protected static function generateOppositions(CasesEntity $case): mixed
    {
        $oppositions = new ArrayCollection();

        $oppositions->add(
            static::generateOpposition(243, $case, null)
        );

        $oppositions->add(
            static::generateOpposition(263, $case, '11-12-2013')
        );

        $oppositions->add(
            static::generateOpposition(253, $case, '10-12-2013')
        );

        return $oppositions;
    }

    protected static function generateOpposition(
        mixed $id,
        CasesEntity $case,
        mixed $raisedDate = null
    ): Opposition {
        $entity = new Opposition(
            $case,
            static::generateOpposer(),
            static::generateRefDataEntity('opposition_type' . $id),
            1,
            1,
            1,
            1,
            0
        );
        $entity->setId($id);
        $entity->setVersion(($id + 2));
        $entity->setRaisedDate($raisedDate ? new \DateTime($raisedDate) : null);

        $grounds = new ArrayCollection();
        $grounds->add(static::generateRefDataEntity('g1'));
        $grounds->add(static::generateRefDataEntity('g2'));
        $entity->setGrounds($grounds);

        return $entity;
    }

    protected static function generateOpposer(int $id = 834): mixed
    {
        $contactDetails = static::generateContactDetails(
            744,
            ContactDetails::CONTACT_TYPE_COMPLAINANT
        );
        $entity = new Opposer(
            $contactDetails,
            static::generateRefDataEntity('opposer_type1'),
            static::generateRefDataEntity('opposition_type1')
        );
        $entity->setId($id);
        $entity->setVersion(($id + 2));

        return $entity;
    }

    protected static function generateConvictions(): mixed
    {
        $convictions = new ArrayCollection();

        $convictions->add(
            static::generateConviction(734, Conviction::DEFENDANT_TYPE_ORGANISATION)
        );

        $convictions->add(
            static::generateConviction(734, Conviction::DEFENDANT_TYPE_DIRECTOR)
        );

        return $convictions;
    }

    protected static function generateConviction(mixed $id, mixed $defendantType): mixed
    {
        $entity = new Conviction();
        $entity->setId($id);
        $entity->setVersion(($id + 2));
        $entity->setOffenceDate(new \DateTime('2007-06-03'));
        $entity->setConvictionDate(new \DateTime('2008-06-03'));
        $entity->setOperatorName('operator1');
        $entity->setCategoryText('cat-text');
        $entity->setCourt('court1');
        $entity->setPenalty('pen1');
        $entity->setMsi('msi1');
        $entity->setIsDeclared(false);
        $entity->setIsDealtWith(true);
        $entity->setPersonFirstname('fn');
        $entity->setPersonLastname('sn');
        $entity->setDefendantType(static::generateRefDataEntity($defendantType));

        return $entity;
    }

    protected static function generatePreviousConviction(mixed $id): mixed
    {
        $entity = new PreviousConviction();
        $entity->setId($id);
        $entity->setVersion(($id + 2));
        $entity->setConvictionDate(new \DateTime('2008-06-03'));
        $entity->setCategoryText('cat-text');
        $entity->setCourtFpn('courtFpn1');
        $entity->setPenalty('pen1');

        return $entity;
    }

    protected static function generateErruRequest(): mixed
    {
        /** @var ErruRequest $entity */
        $entity = m::mock(ErruRequest::class)->makePartial();
        $entity
            ->setNotificationNumber('notificationNo')
            ->setMemberStateCode(static::generateCountry('GB'))
            ->setVrm('erruVrm1')
            ->setTransportUndertakingName('tun')
            ->setOriginatingAuthority('erru_oa');

        return $entity;
    }

    protected static function generateSeriousInfringements(): mixed
    {
        $sis = new ArrayCollection();

        $sis->add(
            static::generateSeriousInfringement(734)
        );

        return $sis;
    }

    protected static function generateSeriousInfringement(mixed $id): mixed
    {
        /** @var SeriousInfringement $entity */
        $entity = m::mock(SeriousInfringement::class)->makePartial();
        $entity->setId($id);
        $entity->setVersion(($id + 2));
        $entity->setSiCategory(static::generateSiCategory(274, 'sicatdesc'));
        $entity->setSiCategoryType(static::generateSiCategoryType(274, 'sicattypedesc'));
        $entity->setInfringementDate(new \DateTime('2009-11-30'));
        $entity->setCheckDate(new \DateTime('2010-07-20'));

        $entity->setAppliedPenalties(static::generateArrayCollection('appliedPenalty'));
        $entity->setImposedErrus(static::generateArrayCollection('imposedErru'));
        $entity->setRequestedErrus(static::generateArrayCollection('requestedErru'));

        return $entity;
    }

    protected static function generateArrayCollection(mixed $entity, int $count = 1): mixed
    {
        $ac = new ArrayCollection();
        $method = 'generate' . ucfirst((string) $entity);
        for ($i = 1; $i <= $count; $i++) {
            $ac->add(
                static::$method($i)
            );
        }

        return $ac;
    }

    protected static function generateAppliedPenalty(mixed $id): mixed
    {
        $entity = new SiPenalty(
            m::mock(SeriousInfringement::class)->makePartial(),
            static::generateSiPenaltyType(533),
            static::generateRequestedErru(),
            'imposed',
            new \DateTime('2013-06-31'),
            new \DateTime('2013-08-31'),
            'imposed reason'
        );
        $entity->setId($id);
        $entity->setVersion(6);

        return $entity;
    }

    protected static function generateImposedErru(int $id = 101): mixed
    {
        /** @var SiPenaltyErruImposed | m\MockInterface $entity */
        $entity = m::mock(SiPenaltyErruImposed::class)->makePartial();
        $entity->setId($id);
        $entity->setVersion(23);
        $entity->setSiPenaltyImposedType(static::generateSiPenaltyImposedType(42));
        $entity->setFinalDecisionDate(new \DateTime('2014-12-31'));
        $entity->setStartDate(new \DateTime('2014-06-31'));
        $entity->setEndDate(new \DateTime('2014-08-31'));
        $entity->setExecuted('executed');

        return $entity;
    }

    protected static function generateRequestedErru(int $id = 101): mixed
    {
        /** @var SiPenaltyErruRequested | m\MockInterface $entity */
        $entity = m::mock(SiPenaltyErruRequested::class)->makePartial();
        $entity->setId($id);
        $entity->setVersion(34);
        $entity->setSiPenaltyRequestedType(static::generateSiPenaltyRequestedType(952));
        $entity->setDuration('duration1');

        return $entity;
    }

    protected static function generateSiPenaltyType(mixed $id): mixed
    {
        $entity = new SiPenaltyType();
        $entity->setId($id);
        $entity->setVersion(6);
        $entity->setDescription($id . '-desc');

        return $entity;
    }

    protected static function generateSiPenaltyImposedType(mixed $id): mixed
    {
        $entity = new SiPenaltyImposedType();
        $entity->setId($id);
        $entity->setVersion(6);
        $entity->setDescription($id . '-desc');

        return $entity;
    }

    protected static function generateSiPenaltyRequestedType(mixed $id): mixed
    {
        $entity = new SiPenaltyRequestedType();
        $entity->setId($id);
        $entity->setVersion(6);
        $entity->setDescription($id . '-desc');

        return $entity;
    }

    protected static function generateCountry(mixed $id, bool $isMemberState = true): mixed
    {
        $entity = new Country();
        $entity->setId($id);
        $entity->setVersion(1);
        $entity->setIsMemberState($isMemberState);
        $entity->setCountryDesc($id . '-desc');

        return $entity;
    }

    protected static function generateSiCategory(mixed $id, mixed $desc): mixed
    {
        $entity = new SiCategory();
        $entity->setId($id);
        $entity->setVersion(($id + 5));
        $entity->setDescription($desc);

        return $entity;
    }

    protected static function generateSiCategoryType(mixed $id, mixed $desc): mixed
    {
        $entity = new SiCategoryType();
        $entity->setId($id);
        $entity->setVersion(($id + 5));
        $entity->setDescription($desc);

        return $entity;
    }

    protected static function generateProhibition(mixed $id): mixed
    {
        $entity = new Prohibition();
        $entity->setId($id);
        $entity->setVersion(($id + 5));
        $entity->setProhibitionDate(new \DateTime('2008-08-11'));
        $entity->setClearedDate(new \DateTime('2012-08-11'));
        $entity->setVrm('VR12 MAB');
        $entity->setIsTrailer(false);
        $entity->setImposedAt('imposed-at');
        $entity->setProhibitionType(static::generateRefDataEntity('prohibition-type1'));

        return $entity;
    }

    protected static function generateTmEmployment(mixed $id): mixed
    {
        $entity = new TmEmployment();
        $entity->setId($id);
        $entity->setVersion(($id + 5));
        $entity->setPosition('Some position');
        $entity->setEmployerName('Employer name');
        $entity->setHoursPerWeek(32);
        $entity->setContactDetails(static::generateContactDetails(54));

        return $entity;
    }

    protected function mockSetRepos(mixed $sut): void
    {
        $sut->setRepos([]);
    }
}
