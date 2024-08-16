<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Doctrine\Common\Collections\ArrayCollection;
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

/**
 * Class AbstractSubmissionSectionTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
abstract class AbstractSubmissionSectionTest extends MockeryTestCase
{
    protected $submissionSection = '';
    protected $licenceStatus = 'lic_status';
    protected $organisationType = 'org_type';
    protected $licenceType = 'lic_type';
    protected $goodsOrPsv = 'goods';
    protected $natureOfBusiness = 'nob1';

    /**
     * @dataProvider sectionTestProvider
     */
    public function testGenerateSection($input = null, $expectedResult = null)
    {
        if (!empty($input)) {
            $mockQueryHandler = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class);
            $mockViewRenderer = m::mock(PhpRenderer::class);
            $sut = new $this->submissionSection($mockQueryHandler, $mockViewRenderer);

            $this->mockSetRepos($sut);

            $section = $sut->generateSection($input);
            $this->assertEquals($expectedResult, $section);
        } else {
            $this->markTestSkipped('Skipping, no input');
        }
    }

    /**
     * Filter provider
     *
     * @return array
     */
    abstract public function sectionTestProvider();

    /**
     * Return a case attached to an application
     *
     * @return CasesEntity
     */
    protected function getApplicationCase()
    {
        $case = $this->getCase();
        $case->setCaseType(new RefData('case_t_app'));
        $application = $this->generateApplication(
            852,
            $case->getLicence(),
            Application::APPLICATION_STATUS_UNDER_CONSIDERATION,
            false
        );

        $case->setApplication($application);

        return $case;
    }

    protected function getCase()
    {
        $openDate = new \DateTime('2012-01-01 15:00:00');
        $caseType = new RefData('case_t_app');
        $caseType->setDescription('case type 1');

        $categorys = new ArrayCollection(['cat1', 'cat2']);
        $outcomes = new ArrayCollection(['out1']);

        $organisation = $this->generateOrganisation();
        $licence = $this->generateLicence($organisation, 7);

        $application = $this->generateApplication(344, $licence, Application::APPLICATION_STATUS_GRANTED);

        $ecmsNo = 'ecms1234';
        $description = 'case description';
        $transportManager = $this->generateTransportManager(43);

        $tmApplications = new ArrayCollection();
        $tmApplications->add(
            $this->generateTransportManagerApplication(
                522,
                $this->generateTransportManager(216),
                Application::APPLICATION_STATUS_GRANTED
            )
        );

        $application->setTransportManagers($tmApplications);

        $tmLicences = new ArrayCollection();
        $tmLicences->add(
            $this->generateTransportManagerLicence(
                234,
                $licence,
                $transportManager
            )
        );
        $licence->setTmLicences($tmLicences);

        $transportManager->setTmApplications($tmApplications);
        $transportManager->setTmLicences($tmLicences);
        $transportManager->setEmployments($this->generateArrayCollection('TmEmployment'));
        $transportManager->setOtherLicences($this->generateArrayCollection('OtherLicence'));
        $transportManager->setPreviousConvictions($this->generateArrayCollection('PreviousConviction'));

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

        $case->setComplaints($this->generateComplaints($case));
        $case->setStatements($this->generateStatements($case));
        $case->setOppositions($this->generateOppositions($case));

        $case->setConvictions($this->generateConvictions());
        $case->setConvictionNote('conv_note1');

        $case->setSeriousInfringements($this->generateSeriousInfringements());
        $case->setErruRequest($this->generateErruRequest());
        $case->setPenaltiesNote('pen-notes1');

        $case->setProhibitionNote('prohibition-note');
        $case->setProhibitions($this->generateArrayCollection('Prohibition'));

        $cu = $this->generateConditionsUndertakings(
            $case,
            ConditionUndertaking::TYPE_CONDITION,
            29
        );

        $case->setConditionUndertakings($cu);

        return $case;
    }

    protected function generateRefDataEntity($id, $description = 'desc')
    {
        $refData = new RefData($id);
        $refData->setDescription($id . '-' . $description);

        return $refData;
    }

    protected function generatePerson($id)
    {
        $person = new Person($id);
        $person->setId($id);
        $person->setTitle($this->generateRefDataEntity('title'));
        $person->setForename('fn' . $id);
        $person->setFamilyName('sn' . $id);
        $person->setBirthDate(new \DateTime('1977-01-' . $id));
        $person->setBirthPlace('bp');

        return $person;
    }

    protected function generateTransportManager($id)
    {
        $tm = new TransportManager($id);
        $tm->setId($id);
        $tm->setVersion(($id + 10));
        $tm->setTmType($this->generateRefDataEntity('tmType'));

        $tm->setHomeCd($this->generateContactDetails(533, ContactDetails::CONTACT_TYPE_REGISTERED_ADDRESS));
        $tm->setWorkCd($this->generateContactDetails(343, ContactDetails::CONTACT_TYPE_CORRESPONDENCE_ADDRESS));

        $tm->setQualifications($this->generateArrayCollection('tmQualification'));

        return $tm;
    }

    protected function generateTransportManagerApplication(
        $id,
        $transportManager,
        $applicationStatus = Application::APPLICATION_STATUS_UNDER_CONSIDERATION
    ) {
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
        $organisationType = $this->generateRefDataEntity($this->organisationType);
        $organisation->setType($organisationType);
        $organisation->setName('Org name');

        $licence = $this->generateLicence($organisation, 55);

        $entity->setApplication(
            $this->generateApplication(852, $licence, $applicationStatus, false)
        );

        return $entity;
    }

    protected function generateTransportManagerLicence($id, $licence, $transportManager)
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

    protected function generateOrganisation()
    {
        $organisation = new Organisation();
        $organisationType = $this->generateRefDataEntity($this->organisationType);
        $organisation->setType($organisationType);
        $organisation->setName('Org name');
        $organisation->setNatureOfBusiness($this->natureOfBusiness);

        $organisationPersons = new ArrayCollection();
        $organisationPerson = new OrganisationPerson();
        $organisationPerson->setPerson($this->generatePerson(1));
        $organisationPersons->add($organisationPerson);
        $organisation->setOrganisationPersons($organisationPersons);

        $organisationLicences = new ArrayCollection();
        $applications = new ArrayCollection();

        for ($i = 1; $i < 3; $i++) {
            $licence = $this->generateLicence($organisation, $i);
            $application = $this->generateApplication($i, $licence, Application::APPLICATION_STATUS_GRANTED);

            if ($i == 1) {
                // assign some tms to the first application
                $application->setTransportManagers(
                    $this->generateTransportManagerApplications()
                );
            }
            $applications->add($application);

            $applications->add(
                $this->generateApplication((100 + $i), $licence, Application::APPLICATION_STATUS_UNDER_CONSIDERATION)
            );

            $licence->setApplications($applications);

            $organisationLicences->add($licence);
        }
        $organisation->setLicences($organisationLicences);

        $organisation->setLeadTcArea($this->generateTrafficArea('B'));

        return $organisation;
    }

    protected function generateLicence(Organisation $organisation, $id = null)
    {
        $licence = m::mock(Licence::class)->makePartial();
        $licence->initCollections();
        $licence->setOrganisation($organisation);
        $licence->setStatus($this->generateRefDataEntity($this->licenceStatus));

        $licence->setId($id);
        $licence->setVersion($id);
        $licence->setLicenceType($this->generateRefDataEntity($this->licenceType));
        $licence->setGoodsOrPsv($this->generateRefDataEntity($this->goodsOrPsv));
        $licence->setLicNo('OB12345');
        $licence->setTotAuthTrailers(5);

        $licence->setLicenceVehicles($this->generateLicenceVehicles($licence));

        $licence->setOperatingCentres($this->generateLicenceOperatingCentres($licence));

        $licence->setApplications($this->generateApplications($licence));
        $licence->setTmLicences($this->generateTmLicences($licence));

        $licence->setConditionUndertakings(
            $this->generateConditionsUndertakings(
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

    protected function generateLicenceVehicles($licence)
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

    protected function generateTmLicences(Licence $licence)
    {
        $licenceTms = new ArrayCollection();

        $tm = new TransportManager();
        $tm->setTmType($this->generateRefDataEntity('tm_type1'));
        $tm->setId(153);
        $tm->setVersion(306);
        $tm->setHomeCd($this->generateContactDetails(83));

        $tm->setOtherLicences([]);
        $tml = new TransportManagerLicence($licence, $tm);

        $licenceTms->add($tml);

        return $licenceTms;
    }

    protected function generateTransportManagerApplications()
    {
        $applicationTms = new ArrayCollection();

        $tm = new TransportManager();
        $tm->setTmType($this->generateRefDataEntity('tm_type1'));
        $tm->setId(153);
        $tm->setVersion(306);
        $tm->setHomeCd($this->generateContactDetails(83));

        $tm->setOtherLicences($this->generateArrayCollection('OtherLicence', 1));
        $tm->setQualifications($this->generateArrayCollection('tmQualification'));

        $tma = new TransportManagerApplication();
        $tma->setTransportManager($tm);

        $applicationTms->add($tma);

        return $applicationTms;
    }

    protected function generateOtherLicence($id)
    {
        $entity = new OtherLicence();
        $entity->setId($id);
        $entity->setVersion($id + 2);
        $entity->setLicNo($id . '-licNo');
        $entity->setHolderName($id . '-holderName');

        $organisation = new Organisation();
        $organisationType = $this->generateRefDataEntity($this->organisationType);
        $organisation->setType($organisationType);
        $organisation->setName('Org name');

        $licence = $this->generateLicence($organisation, 55);

        $entity->setApplication(
            $this->generateApplication(2255, $licence, Application::APPLICATION_STATUS_UNDER_CONSIDERATION)
        );
        return $entity;
    }

    protected function generateConditionsUndertakings(
        $parentEntity,
        $condType,
        $id = 1,
        $addedVia = null,
        $attachTo = null,
        $createdOn = null
    ) {
        $cu = new ConditionUndertaking($this->generateRefDataEntity($condType), 'Y', 'N');

        $addedViaByParent = null;
        $attachToByParent = null;

        if ($parentEntity instanceof Licence) {
            $addedViaByParent = ConditionUndertaking::ADDED_VIA_LICENCE;
            $attachToByParent = ConditionUndertaking::ATTACHED_TO_LICENCE;
        } elseif ($parentEntity instanceof Application) {
            $addedViaByParent = ConditionUndertaking::ADDED_VIA_APPLICATION;
            $attachToByParent = ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE;

            $cu->setOperatingCentre($this->generateOperatingCentre());
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
                $this->generateRefDataEntity($addedVia ?: $addedViaByParent)
            )
            ->setAttachedTo(
                $this->generateRefDataEntity($attachTo ?: $attachToByParent)
            );

        $conditionUndertakings = new ArrayCollection();
        $conditionUndertakings->add($cu);

        return $conditionUndertakings;
    }

    protected function generateTmQualification($id)
    {
        $entity = new TmQualification();
        $entity->setId($id);
        $entity->setVersion(($id + 4));
        $entity->setQualificationType($this->generateRefDataEntity('tm-qual'));
        $entity->setCountryCode($this->generateCountry('GB'));
        $entity->setSerialNo('12344321');
        $entity->setIssuedDate(new \DateTime('2008-12-04'));

        return $entity;
    }

    protected function generateLicenceOperatingCentres($licence)
    {
        $operatingCentres = new ArrayCollection();

        for ($i = 1; $i <= 2; $i++) {
            $operatingCentre = $this->generateOperatingCentre($i);
            $loc = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre($licence, $operatingCentre);
            $loc->setNoOfVehiclesRequired(6);
            $loc->setNoOfTrailersRequired(4);
            $operatingCentres->add($loc);
        }

        return $operatingCentres;
    }

    protected function generateOperatingCentre($i = 1)
    {
        $operatingCentre = new OperatingCentre();
        $operatingCentre->setId($i);
        $operatingCentre->setVersion($i);

        $address = $this->generateAddress($i);
        $operatingCentre->setAddress($address);
        return $operatingCentre;
    }

    protected function generateAddress($id)
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

    protected function generateApplications(Licence $licence)
    {
        $applications = new ArrayCollection();
        $grantedApp = $this->generateApplication(63, $licence, Application::APPLICATION_STATUS_GRANTED);

        $grantedApp->setConditionUndertakings(
            $this->generateConditionsUndertakings(
                $grantedApp,
                ConditionUndertaking::TYPE_UNDERTAKING,
                88
            )
        );

        $applications->add(
            $this->generateApplication(75, $licence, Application::APPLICATION_STATUS_NOT_SUBMITTED)
        );

        $applications->add(
            $this->generateApplication(75, $licence, Application::APPLICATION_STATUS_REFUSED)
        );

        $applications->add(
            $this->generateApplication(75, $licence, Application::APPLICATION_STATUS_GRANTED, true)
        );

        $applications->add(
            $this->generateApplication(777, $licence, Application::APPLICATION_STATUS_UNDER_CONSIDERATION, true)
        );

        return $applications;
    }

    protected function generateApplication($id, Licence $licence, $status, $isVariation = false)
    {
        $application = m::mock(Application::class)->makePartial();
        $application->initCollections();
        $application->setLicence($licence);
        $application->setStatus($this->generateRefDataEntity($status));
        $application->setIsVariation($isVariation);

        $application->setId($id);
        $application->setVersion(($id * 2));
        $application->setReceivedDate(new \DateTime('2014-05-05'));
        $application->setGoodsOrPsv($this->generateRefDataEntity('goods'));
        $application->setVehicleType($this->generateRefDataEntity(RefData::APP_VEHICLE_TYPE_HGV));
        $application->setLicenceType($this->generateRefDataEntity('lic_type'));

        $application->setConditionUndertakings(
            $this->generateConditionsUndertakings(
                $application,
                ConditionUndertaking::TYPE_UNDERTAKING,
                34
            )
        );

        return $application;
    }

    protected function generateTrafficArea($id)
    {
        $ta = new TrafficArea();

        $ta->setId($id);
        $ta->setName('FOO');

        return $ta;
    }

    protected function generateContactDetails($id, $type = 'cd_type')
    {
        $cd = new ContactDetails($this->generateRefDataEntity($type));
        $cd->setAddress($this->generateAddress($id));
        $cd->setPerson($this->generatePerson(22));
        $cd->setEmailAddress('blah@blah.com');

        return $cd;
    }

    protected function generateComplaints(CasesEntity $case)
    {
        $complaints = new ArrayCollection();

        // add compliance complaint
        $complaints->add(
            $this->generateComplaint(
                253,
                $case,
                $this->generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                1,
                '04-05-2006'
            )
        );
        $complaints->add(
            $this->generateComplaint(
                543,
                $case,
                $this->generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                1,
                '03-05-2006'
            )
        );
        $complaints->add(
            $this->generateComplaint(
                563,
                $case,
                $this->generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                1,
                null
            )
        );

        // add env complaint
        $complaints->add(
            $this->generateComplaint(
                253,
                $case,
                $this->generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                0,
                '04-05-2006'
            )
        );
        $complaints->add(
            $this->generateComplaint(
                543,
                $case,
                $this->generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                0,
                '03-05-2006'
            )
        );
        $complaints->add(
            $this->generateComplaint(
                563,
                $case,
                $this->generateContactDetails(423, ContactDetails::CONTACT_TYPE_COMPLAINANT),
                0,
                null
            )
        );
        return $complaints;
    }

    protected function generateComplaint(
        $id,
        CasesEntity $case,
        ContactDetails $contactDetails,
        $isCompliance = 1,
        $complaintDate = null
    ) {
        $complaint = new Complaint(
            $case,
            (bool) $isCompliance,
            $this->generateRefDataEntity(Complaint::COMPLAIN_STATUS_OPEN),
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
            $complaint->setOperatingCentres(new ArrayCollection([$this->generateOperatingCentre(633)]));
        }
        return $complaint;
    }

    protected function generateStatements(CasesEntity $case)
    {
        $statements = new ArrayCollection();

        $statements->add(
            $this->generateStatement(253, $case)
        );

        return $statements;
    }

    protected function generateStatement($id, CasesEntity $case)
    {
        $entity = new Statement($case, $this->generateRefDataEntity('statement_type1'));
        $entity->setId($id);
        $entity->setVersion(($id + 2));
        $entity->setRequestedDate(new \DateTime('2008-08-11'));
        $entity->setRequestorsContactDetails(
            $this->generateContactDetails(
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

    protected function generateOppositions(CasesEntity $case)
    {
        $oppositions = new ArrayCollection();

        $oppositions->add(
            $this->generateOpposition(243, $case, null)
        );

        $oppositions->add(
            $this->generateOpposition(263, $case, '11-12-2013')
        );

        $oppositions->add(
            $this->generateOpposition(253, $case, '10-12-2013')
        );

        return $oppositions;
    }

    protected function generateOpposition(
        $id,
        CasesEntity $case,
        $raisedDate = null
    ) {
        $entity = new Opposition(
            $case,
            $this->generateOpposer(),
            $this->generateRefDataEntity('opposition_type' . $id),
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
        $grounds->add($this->generateRefDataEntity('g1'));
        $grounds->add($this->generateRefDataEntity('g2'));
        $entity->setGrounds($grounds);

        return $entity;
    }

    protected function generateOpposer($id = 834)
    {
        $contactDetails = $this->generateContactDetails(
            744,
            ContactDetails::CONTACT_TYPE_COMPLAINANT
        );
        $entity = new Opposer(
            $contactDetails,
            $this->generateRefDataEntity('opposer_type1'),
            $this->generateRefDataEntity('opposition_type1')
        );
        $entity->setId($id);
        $entity->setVersion(($id + 2));

        return $entity;
    }

    protected function generateConvictions()
    {
        $convictions = new ArrayCollection();

        $convictions->add(
            $this->generateConviction(734, Conviction::DEFENDANT_TYPE_ORGANISATION)
        );

        $convictions->add(
            $this->generateConviction(734, Conviction::DEFENDANT_TYPE_DIRECTOR)
        );

        return $convictions;
    }

    protected function generateConviction($id, $defendantType)
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
        $entity->setDefendantType($this->generateRefDataEntity($defendantType));

        return $entity;
    }

    protected function generatePreviousConviction($id)
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

    protected function generateErruRequest()
    {
        /** @var ErruRequest $entity */
        $entity = m::mock(ErruRequest::class)->makePartial();
        $entity
            ->setNotificationNumber('notificationNo')
            ->setMemberStateCode($this->generateCountry('GB'))
            ->setVrm('erruVrm1')
            ->setTransportUndertakingName('tun')
            ->setOriginatingAuthority('erru_oa');

        return $entity;
    }

    protected function generateSeriousInfringements()
    {
        $sis = new ArrayCollection();

        $sis->add(
            $this->generateSeriousInfringement(734)
        );

        return $sis;
    }

    protected function generateSeriousInfringement($id)
    {
        /** @var SeriousInfringement $entity */
        $entity = m::mock(SeriousInfringement::class)->makePartial();
        $entity->setId($id);
        $entity->setVersion(($id + 2));
        $entity->setSiCategory($this->generateSiCategory(274, 'sicatdesc'));
        $entity->setSiCategoryType($this->generateSiCategoryType(274, 'sicattypedesc'));
        $entity->setInfringementDate(new \DateTime('2009-11-30'));
        $entity->setCheckDate(new \DateTime('2010-07-20'));

        $entity->setAppliedPenalties($this->generateArrayCollection('appliedPenalty'));
        $entity->setImposedErrus($this->generateArrayCollection('imposedErru'));
        $entity->setRequestedErrus($this->generateArrayCollection('requestedErru'));

        return $entity;
    }

    protected function generateArrayCollection($entity, $count = 1)
    {
        $ac = new ArrayCollection();
        $method = 'generate' . ucfirst((string) $entity);
        for ($i = 1; $i <= $count; $i++) {
            $ac->add(
                $this->$method($i)
            );
        }

        return $ac;
    }

    protected function generateAppliedPenalty($id)
    {
        $entity = new SiPenalty(
            m::mock(SeriousInfringement::class)->makePartial(),
            $this->generateSiPenaltyType(533),
            'imposed',
            new \DateTime('2013-06-31'),
            new \DateTime('2013-08-31'),
            'imposed reason'
        );
        $entity->setId($id);
        $entity->setVersion(6);

        return $entity;
    }

    protected function generateImposedErru($id = 101)
    {
        /** @var SiPenaltyErruImposed | m\MockInterface $entity */
        $entity = m::mock(SiPenaltyErruImposed::class)->makePartial();
        $entity->setId($id);
        $entity->setVersion(23);
        $entity->setSiPenaltyImposedType($this->generateSiPenaltyImposedType(42));
        $entity->setFinalDecisionDate(new \DateTime('2014-12-31'));
        $entity->setStartDate(new \DateTime('2014-06-31'));
        $entity->setEndDate(new \DateTime('2014-08-31'));
        $entity->setExecuted('executed');

        return $entity;
    }

    protected function generateRequestedErru($id = 101)
    {
        /** @var SiPenaltyErruRequested | m\MockInterface $entity */
        $entity = m::mock(SiPenaltyErruRequested::class)->makePartial();
        $entity->setId($id);
        $entity->setVersion(34);
        $entity->setSiPenaltyRequestedType($this->generateSiPenaltyRequestedType(952));
        $entity->setDuration('duration1');

        return $entity;
    }

    protected function generateSiPenaltyType($id)
    {
        $entity = new SiPenaltyType();
        $entity->setId($id);
        $entity->setVersion(6);
        $entity->setDescription($id . '-desc');

        return $entity;
    }

    protected function generateSiPenaltyImposedType($id)
    {
        $entity = new SiPenaltyImposedType();
        $entity->setId($id);
        $entity->setVersion(6);
        $entity->setDescription($id . '-desc');

        return $entity;
    }

    protected function generateSiPenaltyRequestedType($id)
    {
        $entity = new SiPenaltyRequestedType();
        $entity->setId($id);
        $entity->setVersion(6);
        $entity->setDescription($id . '-desc');

        return $entity;
    }

    protected function generateCountry($id, $isMemberState = true)
    {
        $entity = new Country();
        $entity->setId($id);
        $entity->setVersion(1);
        $entity->setIsMemberState($isMemberState);
        $entity->setCountryDesc($id . '-desc');

        return $entity;
    }

    protected function generateSiCategory($id, $desc)
    {
        $entity = new SiCategory();
        $entity->setId($id);
        $entity->setVersion(($id + 5));
        $entity->setDescription($desc);

        return $entity;
    }

    protected function generateSiCategoryType($id, $desc)
    {
        $entity = new SiCategoryType();
        $entity->setId($id);
        $entity->setVersion(($id + 5));
        $entity->setDescription($desc);

        return $entity;
    }

    protected function generateProhibition($id)
    {
        $entity = new Prohibition();
        $entity->setId($id);
        $entity->setVersion(($id + 5));
        $entity->setProhibitionDate(new \DateTime('2008-08-11'));
        $entity->setClearedDate(new \DateTime('2012-08-11'));
        $entity->setVrm('VR12 MAB');
        $entity->setIsTrailer(false);
        $entity->setImposedAt('imposed-at');
        $entity->setProhibitionType($this->generateRefDataEntity('prohibition-type1'));

        return $entity;
    }

    protected function generateTmEmployment($id)
    {
        $entity = new TmEmployment();
        $entity->setId($id);
        $entity->setVersion(($id + 5));
        $entity->setPosition('Some position');
        $entity->setEmployerName('Employer name');
        $entity->setHoursPerWeek(32);
        $entity->setContactDetails($this->generateContactDetails(54));

        return $entity;
    }

    protected function mockSetRepos($sut): void
    {
        $sut->setRepos([]);
    }
}
