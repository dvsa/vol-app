<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Tm;

use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Mockery as m;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as Entity;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * TransportManager Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class TransportManagerEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    protected function getRefData($id)
    {
        $refData = new RefData();
        $refData->setId($id);

        return $refData;
    }

    public function testUpdatePerson()
    {
        $entity = new Entity();

        $entity->updateTransportManager('tmtype', 'tmstatus', 1, 2);

        $this->assertEquals('tmtype', $entity->getTmType());
        $this->assertEquals('tmstatus', $entity->getTmStatus());
        $this->assertEquals(1, $entity->getWorkCd());
        $this->assertEquals(2, $entity->getHomeCd());
    }

    public function testGetAssociatedOrganisationsRemoveDuplicates()
    {
        $entity = new Entity();

        $org1 = new Organisation();
        $org1->setId(101);
        $org2 = new Organisation();
        $org2->setId(102);
        $org3 = new Organisation();
        $org3->setId(103);
        $org4 = new Organisation();
        $org4->setId(104);

        $lic1 = new Licence($org1, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic2 = new Licence($org2, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic3 = new Licence($org3, $this->getRefData(Licence::LICENCE_STATUS_VALID));

        $app1 = new Application($lic1, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app2 = new Application($lic2, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app3 = new Application($lic3, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);

        $tma1 = new TransportManagerApplication();
        $tma1->setApplication($app1);
        $tma2 = new TransportManagerApplication();
        $tma2->setApplication($app2);
        $tma3 = new TransportManagerApplication();
        $tma3->setApplication($app3);

        $entity->addTmApplications(new \Doctrine\Common\Collections\ArrayCollection([$tma1, $tma2, $tma3]));

        $tml1 = new TransportManagerLicence($lic1, $entity);
        $tml2 = new TransportManagerLicence($lic2, $entity);
        $tml3 = new TransportManagerLicence($lic3, $entity);

        $entity->addTmLicences(new \Doctrine\Common\Collections\ArrayCollection([$tml1, $tml2, $tml3]));

        $associatedOrgs = $entity->getAssociatedOrganisations();

        $this->assertSame([101 => $org1, 102 => $org2, 103 => $org3], $associatedOrgs);
    }

    public function testGetAssociatedLicenceStatuses()
    {
        $entity = new Entity();

        $org1 = new Organisation();
        $org1->setId(101);
        $org2 = new Organisation();
        $org2->setId(102);
        $org3 = new Organisation();
        $org3->setId(103);
        $org4 = new Organisation();
        $org4->setId(104);

        $lic1 = new Licence($org1, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic2 = new Licence($org2, $this->getRefData(Licence::LICENCE_STATUS_CURTAILED));
        $lic3 = new Licence($org3, $this->getRefData(Licence::LICENCE_STATUS_SUSPENDED));
        $lic4 = new Licence($org4, $this->getRefData(Licence::LICENCE_STATUS_NOT_TAKEN_UP));

        $tml1 = new TransportManagerLicence($lic1, $entity);
        $tml2 = new TransportManagerLicence($lic2, $entity);
        $tml3 = new TransportManagerLicence($lic3, $entity);
        $tml4 = new TransportManagerLicence($lic4, $entity);

        $entity->addTmLicences(new \Doctrine\Common\Collections\ArrayCollection([$tml1, $tml2, $tml3, $tml4]));

        $associatedOrgs = $entity->getAssociatedOrganisations();

        $this->assertSame([101 => $org1, 102 => $org2, 103 => $org3], $associatedOrgs);
    }

    public function testGetAssociatedApplicationStatuses()
    {
        $entity = new Entity();

        $org1 = new Organisation();
        $org1->setId(101);
        $org2 = new Organisation();
        $org2->setId(102);
        $org3 = new Organisation();
        $org3->setId(103);
        $org4 = new Organisation();
        $org4->setId(104);

        $lic1 = new Licence($org1, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic2 = new Licence($org2, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic3 = new Licence($org3, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic4 = new Licence($org4, $this->getRefData(Licence::LICENCE_STATUS_VALID));

        $app1 = new Application($lic1, $this->getRefData(Application::APPLICATION_STATUS_GRANTED), 0);
        $app2 = new Application($lic2, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app3 = new Application($lic3, $this->getRefData(Application::APPLICATION_STATUS_REFUSED), 0);
        $app4 = new Application($lic4, $this->getRefData(Application::APPLICATION_STATUS_VALID), 0);

        $tma1 = new TransportManagerApplication();
        $tma1->setApplication($app1);
        $tma2 = new TransportManagerApplication();
        $tma2->setApplication($app2);
        $tma3 = new TransportManagerApplication();
        $tma3->setApplication($app3);
        $tma4 = new TransportManagerApplication();
        $tma4->setApplication($app4);

        $entity->addTmApplications(new \Doctrine\Common\Collections\ArrayCollection([$tma1, $tma2, $tma3, $tma4]));

        $associatedOrgs = $entity->getAssociatedOrganisations();

        $this->assertSame([101 => $org1, 102 => $org2], $associatedOrgs);
    }

    public function testGetAssociatedApplicationVariations()
    {
        $entity = new Entity();

        $org1 = new Organisation();
        $org1->setId(101);
        $org2 = new Organisation();
        $org2->setId(102);
        $org3 = new Organisation();
        $org3->setId(103);
        $org4 = new Organisation();
        $org4->setId(104);

        $lic1 = new Licence($org1, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic2 = new Licence($org2, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic3 = new Licence($org3, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic4 = new Licence($org4, $this->getRefData(Licence::LICENCE_STATUS_VALID));

        $app1 = new Application($lic1, $this->getRefData(Application::APPLICATION_STATUS_GRANTED), 0);
        $app2 = new Application($lic2, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 1);
        $app3 = new Application($lic3, $this->getRefData(Application::APPLICATION_STATUS_REFUSED), 0);
        $app4 = new Application($lic4, $this->getRefData(Application::APPLICATION_STATUS_VALID), 1);

        $tma1 = new TransportManagerApplication();
        $tma1->setApplication($app1);
        $tma2 = new TransportManagerApplication();
        $tma2->setApplication($app2);
        $tma3 = new TransportManagerApplication();
        $tma3->setApplication($app3);
        $tma4 = new TransportManagerApplication();
        $tma4->setApplication($app4);

        $entity->addTmApplications(new \Doctrine\Common\Collections\ArrayCollection([$tma1, $tma2, $tma3, $tma4]));

        $associatedOrgs = $entity->getAssociatedOrganisations();

        $this->assertSame([101 => $org1], $associatedOrgs);
    }

    public function testGetTotAuthVehicles()
    {
        $entity = new Entity();

        $org1 = new Organisation();
        $org1->setId(101);
        $org2 = new Organisation();
        $org2->setId(102);
        $org3 = new Organisation();
        $org3->setId(103);
        $org4 = new Organisation();
        $org4->setId(104);

        $lic1 = new Licence($org1, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic1->updateTotAuthHgvVehicles(1);
        $lic2 = new Licence($org2, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic2->updateTotAuthHgvVehicles(2);
        $lic3 = new Licence($org3, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic3->updateTotAuthHgvVehicles(3);

        $app1 = new Application($lic1, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->updateTotAuthHgvVehicles(1);
        $app2 = new Application($lic2, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app2->updateTotAuthHgvVehicles(2);
        $app3 = new Application($lic3, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app3->updateTotAuthHgvVehicles(3);

        $tma1 = new TransportManagerApplication();
        $tma1->setApplication($app1);
        $tma2 = new TransportManagerApplication();
        $tma2->setApplication($app2);
        $tma3 = new TransportManagerApplication();
        $tma3->setApplication($app3);

        $entity->addTmApplications(new \Doctrine\Common\Collections\ArrayCollection([$tma1, $tma2, $tma3]));

        $tml1 = new TransportManagerLicence($lic1, $entity);
        $tml2 = new TransportManagerLicence($lic2, $entity);
        $tml3 = new TransportManagerLicence($lic3, $entity);

        $entity->addTmLicences(new \Doctrine\Common\Collections\ArrayCollection([$tml1, $tml2, $tml3]));

        $total = $entity->getTotAuthVehicles();

        $this->assertSame(12, $total);
    }

    /**
     * @dataProvider dpTestHasValidSiGbQualification
     */
    public function testHasValidSiGbQualification($expected, array $qualificationTypes)
    {
        $entity = new Entity();

        foreach ($qualificationTypes as $qualificationType) {
            $qualification = new TmQualification();
            $qualification->setQualificationType($this->getRefData($qualificationType));
            $entity->addQualifications($qualification);
        }

        $this->assertSame($expected, $entity->hasValidSiGbQualification());
    }

    public function dpTestHasValidSiGbQualification()
    {
        return [
            [false, []],
            [false, [TmQualification::QUALIFICATION_TYPE_NIAR, TmQualification::QUALIFICATION_TYPE_NICPCSI]],
            [false, [TmQualification::QUALIFICATION_TYPE_NIAR, TmQualification::QUALIFICATION_TYPE_NIEXSI]],
            [true, [TmQualification::QUALIFICATION_TYPE_NIAR, TmQualification::QUALIFICATION_TYPE_AR]],
            [true, [TmQualification::QUALIFICATION_TYPE_NIAR, TmQualification::QUALIFICATION_TYPE_CPCSI]],
            [true, [TmQualification::QUALIFICATION_TYPE_NIAR, TmQualification::QUALIFICATION_TYPE_EXSI]],
        ];
    }

    /**
     * @dataProvider dpTestHasValidSiNiQualification
     */
    public function testHasValidSiNiQualification($expected, array $qualificationTypes)
    {
        $entity = new Entity();

        foreach ($qualificationTypes as $qualificationType) {
            $qualification = new TmQualification();
            $qualification->setQualificationType($this->getRefData($qualificationType));
            $entity->addQualifications($qualification);
        }

        $this->assertSame($expected, $entity->hasValidSiNiQualification());
    }

    public function dpTestHasValidSiNiQualification()
    {
        return [
            [false, []],
            [false, [TmQualification::QUALIFICATION_TYPE_AR, TmQualification::QUALIFICATION_TYPE_CPCSI]],
            [false, [TmQualification::QUALIFICATION_TYPE_AR, TmQualification::QUALIFICATION_TYPE_EXSI]],
            [true, [TmQualification::QUALIFICATION_TYPE_AR, TmQualification::QUALIFICATION_TYPE_NIAR]],
            [true, [TmQualification::QUALIFICATION_TYPE_AR, TmQualification::QUALIFICATION_TYPE_NICPCSI]],
            [true, [TmQualification::QUALIFICATION_TYPE_AR, TmQualification::QUALIFICATION_TYPE_NIEXSI]],
        ];
    }

    /**
     * @dataProvider dpGetLgvAcquiredRightsQualificationReturnsNull
     */
    public function testGetLgvAcquiredRightsQualificationReturnsNull(array $qualificationTypes)
    {
        $entity = new Entity();

        foreach ($qualificationTypes as $qualificationType) {
            $qualification = new TmQualification();
            $qualification->setQualificationType($this->getRefData($qualificationType));
            $entity->addQualifications($qualification);
        }

        $this->assertNull($entity->getLgvAcquiredRightsQualification());
    }

    public function dpGetLgvAcquiredRightsQualificationReturnsNull()
    {
        return [
            [[]],
            [[TmQualification::QUALIFICATION_TYPE_NIAR]],
        ];
    }

    /**
     * @dataProvider dpGetLgvAcquiredRightsQualificationReturnsTmQualification
     */
    public function testGetLgvAcquiredRightsQualificationReturnsTmQualification($expected, array $qualificationTypes)
    {
        $entity = new Entity();

        foreach ($qualificationTypes as $qualificationType) {
            $qualification = new TmQualification();
            $qualification->setQualificationType($this->getRefData($qualificationType));
            $entity->addQualifications($qualification);
        }

        $lgvAcquiredRightsQualification = $entity->getLgvAcquiredRightsQualification();

        $this->assertInstanceOf(TmQualification::class, $lgvAcquiredRightsQualification);
        $this->assertEquals($expected, $lgvAcquiredRightsQualification->getQualificationType()->getId());
    }

    public function dpGetLgvAcquiredRightsQualificationReturnsTmQualification()
    {
        return [
            [
                TmQualification::QUALIFICATION_TYPE_LGVAR,
                [TmQualification::QUALIFICATION_TYPE_NIAR, TmQualification::QUALIFICATION_TYPE_LGVAR]
            ],
            [
                TmQualification::QUALIFICATION_TYPE_NILGVAR,
                [TmQualification::QUALIFICATION_TYPE_NIAR, TmQualification::QUALIFICATION_TYPE_NILGVAR]
            ],
        ];
    }

    /**
     * @dataProvider dpHasLgvAcquiredRightsQualification
     */
    public function testHasLgvAcquiredRightsQualification($expected, array $qualificationTypes)
    {
        $entity = new Entity();

        foreach ($qualificationTypes as $qualificationType) {
            $qualification = new TmQualification();
            $qualification->setQualificationType($this->getRefData($qualificationType));
            $entity->addQualifications($qualification);
        }

        $this->assertSame($expected, $entity->hasLgvAcquiredRightsQualification());
    }

    public function dpHasLgvAcquiredRightsQualification()
    {
        return [
            [false, []],
            [false, [TmQualification::QUALIFICATION_TYPE_NIAR]],
            [true, [TmQualification::QUALIFICATION_TYPE_NIAR, TmQualification::QUALIFICATION_TYPE_LGVAR]],
            [true, [TmQualification::QUALIFICATION_TYPE_NIAR, TmQualification::QUALIFICATION_TYPE_NILGVAR]],
        ];
    }

    /**
     * @dataProvider dpNiFlag
     */
    public function testIsSiQualificationRequiredNotSi($niFlag)
    {
        $entity = new Entity();

        $org1 = new Organisation();
        $org1->setId(101);
        $org2 = new Organisation();
        $org2->setId(102);

        $lic1 = new Licence($org1, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic1->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $lic2 = new Licence($org2, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic2->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

        $app1 = new Application($lic1, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setNiFlag('Y')->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $app2 = new Application($lic2, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setNiFlag('N')->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

        $tma1 = new TransportManagerApplication();
        $tma1->setApplication($app1);
        $tma2 = new TransportManagerApplication();
        $tma2->setApplication($app2);
        $entity->addTmApplications(new \Doctrine\Common\Collections\ArrayCollection([$tma1, $tma2]));

        $tml1 = new TransportManagerLicence($lic1, $entity);
        $tml2 = new TransportManagerLicence($lic2, $entity);
        $entity->addTmLicences(new \Doctrine\Common\Collections\ArrayCollection([$tml1, $tml2]));

        $this->assertSame(false, $entity->isSiQualificationRequired($niFlag));
    }

    /**
     * @dataProvider dpNiFlag
     */
    public function testIsSiQualificationRequiredAppSi($niFlag)
    {
        $entity = new Entity();

        $org1 = new Organisation();
        $org1->setId(101);
        $org2 = new Organisation();
        $org2->setId(102);

        $lic1 = new Licence($org1, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic1->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL));
        $lic2 = new Licence($org2, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic2->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

        $app1 = new Application($lic1, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setNiFlag('N')->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $app2 = new Application($lic2, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setNiFlag('N')->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

        $tma1 = new TransportManagerApplication();
        $tma1->setApplication($app1);
        $tma2 = new TransportManagerApplication();
        $tma2->setApplication($app2);
        $entity->addTmApplications(new \Doctrine\Common\Collections\ArrayCollection([$tma1, $tma2]));

        $tml1 = new TransportManagerLicence($lic1, $entity);
        $tml2 = new TransportManagerLicence($lic2, $entity);
        $entity->addTmLicences(new \Doctrine\Common\Collections\ArrayCollection([$tml1, $tml2]));

        $this->assertSame($niFlag === 'N', $entity->isSiQualificationRequired($niFlag));
    }

    /**
     * @dataProvider dpNiFlag
     */
    public function testIsSiQualificationRequiredLic($niFlag)
    {
        $entity = new Entity();

        $org1 = new Organisation();
        $org1->setId(101);
        $org2 = new Organisation();
        $org2->setId(102);

        $lic1 = new Licence($org1, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic1->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $lic2 = new Licence($org2, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic2->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

        $app1 = new Application($lic1, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setNiFlag('N')->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $app2 = new Application($lic2, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setNiFlag('N')->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL));

        $tma1 = new TransportManagerApplication();
        $tma1->setApplication($app1);
        $tma2 = new TransportManagerApplication();
        $tma2->setApplication($app2);
        $entity->addTmApplications(new \Doctrine\Common\Collections\ArrayCollection([$tma1, $tma2]));

        $tml1 = new TransportManagerLicence($lic1, $entity);
        $tml2 = new TransportManagerLicence($lic2, $entity);
        $entity->addTmLicences(new \Doctrine\Common\Collections\ArrayCollection([$tml1, $tml2]));

        $this->assertSame($niFlag === 'N', $entity->isSiQualificationRequired($niFlag));
    }

    /**
     * @dataProvider dpNiFlag
     */
    public function testIsSiQualificationRequiredGbSi($niFlag)
    {
        $entity = new Entity();

        $org1 = new Organisation();
        $org1->setId(101);
        $org2 = new Organisation();
        $org2->setId(102);

        $lic1 = new Licence($org1, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic1->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $lic2 = new Licence($org2, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic2->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

        $app1 = new Application($lic1, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setNiFlag('Y')->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $app2 = new Application($lic2, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setNiFlag('N')->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL));

        $tma1 = new TransportManagerApplication();
        $tma1->setApplication($app1);
        $tma2 = new TransportManagerApplication();
        $tma2->setApplication($app2);
        $entity->addTmApplications(new \Doctrine\Common\Collections\ArrayCollection([$tma1, $tma2]));

        $tml1 = new TransportManagerLicence($lic1, $entity);
        $tml2 = new TransportManagerLicence($lic2, $entity);
        $entity->addTmLicences(new \Doctrine\Common\Collections\ArrayCollection([$tml1, $tml2]));

        $this->assertSame($niFlag === 'N', $entity->isSiQualificationRequired($niFlag));
    }

    public function dpNiFlag()
    {
        return [
            ['N'],
            ['Y'],
        ];
    }

    public function testIsDetachedCases()
    {
        $entity = new Entity();
        $entity->setCases([1,2,3]);

        $this->assertFalse($entity->isDetached());
    }

    public function testIsDetachedLicences()
    {
        $org1 = new Organisation();
        $lic1 = new Licence($org1, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $tm1 = new TransportManager();

        $tml1 = new TransportManagerLicence($lic1, $tm1);

        $entity = new Entity();
        $entity->setCases([]);
        $entity->setTmLicences([$tml1]);

        $this->assertFalse($entity->isDetached());
    }

    public function testGetCalculatedBundleValues()
    {
        $entity = new Entity();

        $this->assertSame(
            [
                'hasValidSiGbQualification' => false,
                'requireSiGbQualification' => false,
                'hasValidSiNiQualification' => false,
                'requireSiNiQualification' => false,
                'associatedOrganisationCount' => 0,
                'associatedTotalAuthVehicles' => 0,
                'isDetached' => true,
                'requireSiGbQualificationOnVariation' => false,
                'requireSiNiQualificationOnVariation' => false,
            ],
            $entity->getCalculatedBundleValues()
        );
    }

    public function testGetContextValue()
    {
        $entity = new Entity();
        $entity->setId(111);

        $this->assertEquals(111, $entity->getContextValue());
    }

    /**
     * @dataProvider dpNiFlag
     */
    public function testIsSiQualificationRequiredOnVariation($niFlag)
    {
        $entity = new Entity();

        $org1 = new Organisation();
        $org1->setId(101);
        $org2 = new Organisation();
        $org2->setId(102);

        $lic1 = new Licence($org1, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic1->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $lic1->setId(1);
        $lic2 = new Licence($org2, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic2->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $lic2->setId(2);
        $lic3 = new Licence($org2, $this->getRefData(Licence::LICENCE_STATUS_VALID));
        $lic3->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $lic3->setId(3);

        $app1 = new Application($lic1, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app1->setNiFlag($niFlag)->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $app1->setIsVariation(true);
        $app2 = new Application($lic2, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app2->setNiFlag($niFlag)->setLicenceType($this->getRefData(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL));
        $app2->setIsVariation(true);
        $app3 = new Application($lic3, $this->getRefData(Application::APPLICATION_STATUS_UNDER_CONSIDERATION), 0);
        $app3->setNiFlag($niFlag);
        $app3->setIsVariation(true);

        $lic1->setApplications(new \Doctrine\Common\Collections\ArrayCollection([$app1]));
        $lic2->setApplications(new \Doctrine\Common\Collections\ArrayCollection([$app2]));
        $lic3->setApplications(new \Doctrine\Common\Collections\ArrayCollection([$app3]));

        $tml1 = new TransportManagerLicence($lic1, $entity);
        $tml2 = new TransportManagerLicence($lic2, $entity);
        $tml3 = new TransportManagerLicence($lic3, $entity);
        $entity->addTmLicences(new \Doctrine\Common\Collections\ArrayCollection([$tml3, $tml1, $tml2]));

        $this->assertSame($niFlag === 'N', $entity->isSiQualificationRequiredOnVariation($niFlag));
    }

    /**
     * @dataProvider dpHasReputeCheckDataProvider
     */
    public function testHasReputeCheckAddress(
        ?string $forename,
        ?string $familyName,
        ?\DateTime $birthDate,
        ?string $birthPlace,
        bool $result
    ): void {
        $entity = new Entity();

        $person = new Person();
        $person->setForename($forename);
        $person->setFamilyName($familyName);
        $person->setBirthDate($birthDate);
        $person->setBirthPlace($birthPlace);

        $contactDetails = m::mock(ContactDetails::class);
        $contactDetails->shouldReceive('getPerson')->andReturn($person);

        $entity->setHomeCd($contactDetails);

        $this->assertEquals($result, $entity->hasReputeCheckAddress());
    }

    public function testHasQualificationsTrue(): void
    {
        $entity = new Entity();

        $qualification = m::mock(TmQualification::class);
        $qualificationCollection = new ArrayCollection([$qualification]);

        $entity->setQualifications($qualificationCollection);
        $this->assertTrue($entity->hasQualifications());
    }

    public function testHasQualificationsFalse(): void
    {
        $entity = new Entity();
        $entity->setQualifications(new ArrayCollection());
        $this->assertFalse($entity->hasQualifications());
    }

    /**
     * Data provider for testHasReputeCheckAddress
     */
    public function dpHasReputeCheckDataProvider(): array
    {
        $forename = 'forename';
        $familyName = 'family name';
        $birthDate = new \DateTime('2015-12-25');
        $birthPlace = 'birth place';

        return [
            [null, $familyName, $birthDate, $birthPlace, false],
            [$forename, null, $birthDate, $birthPlace, false],
            [$forename, $familyName, null, $birthPlace, false],
            [$forename, $familyName, $birthDate, null, false],
            [$forename, $familyName, $birthDate, $birthPlace, true]
        ];
    }

    public function testGetMostRecentQualification(): void
    {
        $qual1 = new TmQualification();
        $qual1->setIssuedDate(new \DateTime('2015-12-25 00:00:00'));
        $qual1->setId(1);

        $qual2 = new TmQualification();
        $qual2->setIssuedDate(new \DateTime('2015-12-24 00:00:00'));
        $qual2->setId(2);

        //qual 3 has joint latest date, so is selected based on the later id
        $qual3 = new TmQualification();
        $qual3->setIssuedDate(new \DateTime('2015-12-25 00:00:00'));
        $qual3->setId(3);

        $qual4 = new TmQualification();
        $qual4->setIssuedDate(new \DateTime('2015-12-23 00:00:00'));
        $qual4->setId(4);

        $qualifications = new ArrayCollection([$qual1, $qual2, $qual3, $qual4]);

        $entity = new Entity();
        $entity->setQualifications($qualifications);

        $this->assertEquals(new ArrayCollection([$qual3]), $entity->getMostRecentQualification());
    }
}
