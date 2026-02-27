<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Organisation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Disqualification;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as Entity;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;
use Doctrine\ORM\EntityNotFoundException;

/**
 * Organisation Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class OrganisationEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testHasInforceLicences(): void
    {
        /** @var Entity $organisation */
        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->shouldReceive('getLicences->matching')
            ->with(m::type(Criteria::class))
            ->andReturnUsing(
                function (Criteria $criteria) {

                    /** @var \Doctrine\Common\Collections\Expr\Comparison $expr */
                    $expr = $criteria->getWhereExpression();

                    $this->assertEquals('inForceDate', $expr->getField());
                    $this->assertEquals('<>', $expr->getOperator());
                    $this->assertEquals(null, $expr->getValue()->getValue());

                    $collection = m::mock();
                    $collection->shouldReceive('toArray')
                        ->andReturn(['foo']);

                    return $collection;
                }
            );

        $this->assertTrue($organisation->hasInforceLicences());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('organisationDataProvider')]
    public function testUpdateOrganisation(mixed $isIrfo, mixed $lastName, mixed $expectedName, mixed $allowEmail): void
    {
        $organisation = new Entity();

        $organisation->updateOrganisation(
            'name',
            '12345678',
            'fname',
            $lastName,
            $isIrfo,
            'nob',
            ['cpid'],
            $allowEmail
        );

        $this->assertEquals($organisation->getCpid(), ['cpid']);
        $this->assertEquals($organisation->getName(), $expectedName);
        $this->assertEquals($organisation->getCompanyOrLlpNo(), '12345678');
        $this->assertEquals($organisation->getIsIrfo(), $isIrfo);
        $this->assertEquals($organisation->getNatureOfBusiness(), 'nob');
        $this->assertEquals($organisation->getAllowEmail(), $allowEmail);
    }

    public static function organisationDataProvider(): array
    {
        return [
            ['Y', 'lname', 'fname lname', 'Y'],
            ['N', '', 'name','N']
        ];
    }

    public function testGetAdminOrganisationUsers(): void
    {
        $mockOrgUser1 = m::mock(OrganisationUser::class)
            ->shouldReceive('getUser')
            ->once()
            ->andReturn(
                m::mock(UserEntity::class)
                    ->shouldReceive('getAccountDisabled')->andReturn('N')->once()
                    ->getMock()
            )
            ->getMock();

        $mockOrgUser2 = m::mock(OrganisationUser::class)
            ->shouldReceive('getUser')
            ->once()
            ->andReturn(
                m::mock(UserEntity::class)
                    ->shouldReceive('getAccountDisabled')->andReturn('Y')->once()
                    ->getMock()
            )
            ->getMock();

        //  not user entity
        $mockOrgUser3 = m::mock(OrganisationUser::class)
            ->shouldReceive('getUser')->once()->andReturn(m::mock())
            ->getMock();

        $collection = new ArrayCollection([$mockOrgUser1, $mockOrgUser2, $mockOrgUser3]);

        /** @var Entity | m\MockInterface $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('getOrganisationUsers->matching')
            ->with(m::type(Criteria::class))
            ->andReturn($collection);

        $expected = new ArrayCollection();
        $expected->add($mockOrgUser1);

        $this->assertEquals($expected, $sut->getAdminOrganisationUsers());
    }

    public function testGetAdminOrganisationUsersWithException(): void
    {
        $mockOrgUser1 = m::mock(OrganisationUser::class)
            ->shouldReceive('getUser')
            ->once()
            ->andReturn(
                m::mock(UserEntity::class)
                    ->shouldReceive('getAccountDisabled')->andThrow(EntityNotFoundException::class)->once()
                    ->getMock()
            )
            ->getMock();

        $mockOrgUser2 = m::mock(OrganisationUser::class)
            ->shouldReceive('getUser')
            ->once()
            ->andReturn(
                m::mock(UserEntity::class)
                    ->shouldReceive('getAccountDisabled')->andReturn('Y')->once()
                    ->getMock()
            )
            ->getMock();

        //  not user entity
        $mockOrgUser3 = m::mock(OrganisationUser::class)
            ->shouldReceive('getUser')->once()->andReturn(m::mock())
            ->getMock();

        $collection = new ArrayCollection([$mockOrgUser1, $mockOrgUser2, $mockOrgUser3]);

        /** @var Entity | m\MockInterface $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('getOrganisationUsers->matching')
            ->with(m::type(Criteria::class))
            ->andReturn($collection);

        $this->assertEquals(new ArrayCollection(), $sut->getAdminOrganisationUsers());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHasAdminEmailAddresses')]
    public function testHasAdminEmailAddresses(array $emailsMap, mixed $expect): void
    {
        /** @var Entity | m\MockInterface $sut */
        $sut = m::mock(Entity::class)->makePartial();

        $orgUsers = [];
        foreach ($emailsMap as $email) {
            /** @var OrganisationUser | m\MockInterface $mockOrgUser */
            $mockOrgUser = m::mock(OrganisationUser::class)->makePartial();
            $mockOrgUser->setIsAdministrator(true);
            $mockOrgUser->shouldReceive('getUser->getContactDetails->getEmailAddress')->andReturn($email);

            $orgUsers[] = $mockOrgUser;
        }

        $sut->shouldReceive('getAdminOrganisationUsers')->andReturn($orgUsers);

        static::assertEquals($expect, $sut->hasAdminEmailAddresses());
    }

    public static function dpTestHasAdminEmailAddresses(): array
    {
        return [
            [
                'emailsMap' => [
                    null,
                    '',
                    'unit_broken_emailaddress',
                ],
                'expect' => false,
            ],
            [
                'emailsMap' => [
                    null,
                    'aaa.bbb@domain.com',
                ],
                'expect' => true,
            ],
        ];
    }

    public static function dpOrgTypes(): array
    {
        return [
            [Entity::ORG_TYPE_SOLE_TRADER],
            [Entity::ORG_TYPE_IRFO],
            [Entity::ORG_TYPE_LLP],
            [Entity::ORG_TYPE_OTHER],
            [Entity::ORG_TYPE_PARTNERSHIP],
            [Entity::ORG_TYPE_REGISTERED_COMPANY],
        ];
    }

    /**
     * @param string $typeId
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpOrgTypes')]
    public function testIsSoleTrader(mixed $typeId): void
    {
        $type = new \Dvsa\Olcs\Api\Entity\System\RefData();
        $type->setId($typeId);
        $organisation = new Entity();
        $organisation->setType($type);

        $this->assertSame($typeId === Entity::ORG_TYPE_SOLE_TRADER, $organisation->isSoleTrader());
    }

    /**
     * @param string $typeId
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpOrgTypes')]
    public function testIsPartnership(mixed $typeId): void
    {
        $type = new \Dvsa\Olcs\Api\Entity\System\RefData();
        $type->setId($typeId);
        $organisation = new Entity();
        $organisation->setType($type);

        $this->assertSame($typeId === Entity::ORG_TYPE_PARTNERSHIP, $organisation->isPartnership());
    }

    public function testIsUnlicensed(): void
    {
        /** @var Entity $organisation */
        $organisation = $this->instantiate($this->entityClass);

        $this->assertFalse($organisation->isUnlicensed());

        $organisation->setIsUnlicensed(true);

        $this->assertTrue($organisation->isUnlicensed());
    }

    /**
     * Tests fetching a licence from the organisation using the licNo
     */
    public function testGetLicenceByLicNo(): void
    {
        $licNo = 'PD8538936';

        /** @var Entity | m\MockInterface $organisation */
        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->shouldReceive('getLicences->matching')
            ->with(m::type(Criteria::class))
            ->andReturnUsing(
                function (Criteria $criteria) {
                    $licNo = 'PD8538936';

                    /** @var \Doctrine\Common\Collections\Expr\Comparison $expr */
                    $expr = $criteria->getWhereExpression();

                    $this->assertEquals('licNo', $expr->getField());
                    $this->assertEquals('=', $expr->getOperator());
                    $this->assertEquals($licNo, $expr->getValue()->getValue());

                    $collection = m::mock();
                    $collection->shouldReceive('toArray')
                        ->andReturn(['licence']);

                    return $collection;
                }
            );

        $this->assertEquals(
            ['licence'],
            $organisation->getLicenceByLicNo($licNo)->toArray()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpActiveLicences')]
    public function testGetActiveLicences(mixed $goodsOrPsv, mixed $status, mixed $expectedAny, mixed $expectedGoods, mixed $expectedPsv): void
    {
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setStatus(new RefData($status));
        $licence->setGoodsOrPsv(new RefData($goodsOrPsv));

        $collection = new ArrayCollection([$licence]);

        /** @var Entity | m\MockInterface $organisation */
        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->setLicences($collection);

        $this->assertEquals(
            $expectedAny,
            $organisation->getActiveLicences()->contains($licence)
        );

        $this->assertEquals(
            $expectedGoods,
            $organisation->getActiveLicences(LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE)->contains($licence)
        );

        $this->assertEquals(
            $expectedPsv,
            $organisation->getActiveLicences(LicenceEntity::LICENCE_CATEGORY_PSV)->contains($licence)
        );
    }

    public static function dpActiveLicences(): array
    {
        return [
            [null, LicenceEntity::LICENCE_STATUS_VALID, true, false, false],
            [null, LicenceEntity::LICENCE_STATUS_SUSPENDED, true, false, false],
            [null, LicenceEntity::LICENCE_STATUS_CURTAILED, true, false, false],
            [null, LicenceEntity::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION, true, false, false],
            [null, LicenceEntity::LICENCE_STATUS_NOT_SUBMITTED, false, false, false],
            [null, LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION, false, false, false],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_VALID, true, true, false],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_SUSPENDED, true, true, false],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_CURTAILED, true, true, false],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION, true, true, false],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_NOT_SUBMITTED, false, false, false],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION, false, false, false],
            [LicenceEntity::LICENCE_CATEGORY_PSV, LicenceEntity::LICENCE_STATUS_VALID, true, false, true],
            [LicenceEntity::LICENCE_CATEGORY_PSV, LicenceEntity::LICENCE_STATUS_SUSPENDED, true, false, true],
            [LicenceEntity::LICENCE_CATEGORY_PSV, LicenceEntity::LICENCE_STATUS_CURTAILED, true, false, true],
            [LicenceEntity::LICENCE_CATEGORY_PSV, LicenceEntity::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION, true, false, true],
            [LicenceEntity::LICENCE_CATEGORY_PSV, LicenceEntity::LICENCE_STATUS_NOT_SUBMITTED, false, false, false],
            [LicenceEntity::LICENCE_CATEGORY_PSV, LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION, false, false, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpActiveLicences')]
    public function testHasActiveLicences(mixed $goodsOrPsv, mixed $status, mixed $expectedAny, mixed $expectedGoods, mixed $expectedPsv): void
    {
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setStatus(new RefData($status));
        $licence->setGoodsOrPsv(new RefData($goodsOrPsv));

        $collection = new ArrayCollection([$licence]);

        /** @var Entity | m\MockInterface $organisation */
        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->setLicences($collection);

        $this->assertEquals(
            $expectedAny,
            $organisation->hasActiveLicences()
        );

        $this->assertEquals(
            $expectedGoods,
            $organisation->hasActiveLicences(LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE)
        );

        $this->assertEquals(
            $expectedPsv,
            $organisation->hasActiveLicences(LicenceEntity::LICENCE_CATEGORY_PSV)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpEligibleIrhpLicences')]
    public function testGetEligibleIrhpLicences(mixed $goodsOrPsv, mixed $status, mixed $licenceType, mixed $expected): void
    {
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setStatus(new RefData($status));
        $licence->setGoodsOrPsv(new RefData($goodsOrPsv));
        $licence->setLicenceType(new RefData($licenceType));

        $collection = new ArrayCollection([$licence]);

        /** @var Entity | m\MockInterface $organisation */
        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->setLicences($collection);

        $this->assertEquals(
            $expected,
            $organisation->getEligibleIrhpLicences()->contains($licence)
        );
    }

    public static function dpEligibleIrhpLicences(): array
    {
        return [
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_VALID, LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL, true],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_VALID, LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL, true],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_VALID, LicenceEntity::LICENCE_TYPE_RESTRICTED, true],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_VALID, LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED, false],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_SUSPENDED, LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL, true],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_CURTAILED, LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL, true],
            [LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE, LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION, LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL, false],
            [LicenceEntity::LICENCE_CATEGORY_PSV, LicenceEntity::LICENCE_STATUS_VALID, LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL, false],
            [null, LicenceEntity::LICENCE_STATUS_VALID, LicenceEntity::LICENCE_TYPE_STANDARD_INTERNATIONAL, false],
        ];
    }

    public function testGetEligibleIrhpLicencesForStock(): void
    {
        $permitAppId = 333;
        $activePermitApp = m::mock(IrhpApplication::class);
        $activePermitApp->shouldReceive('getId')->once()->withNoArgs()->andReturn($permitAppId);
        $stock = m::mock(IrhpPermitStock::class);

        $id1 = 111;
        $licNo1 = 'OB1234567';
        $trafficArea1 = 'traffic area 1';
        $isRestricted1 = true;
        $typeDesc1 = 'type description 1';

        $licence1 = m::mock(LicenceEntity::class);
        $licence1->shouldReceive('getId')->once()->withNoArgs()->andReturn($id1);
        $licence1->shouldReceive('getActiveIrhpApplication')->with($stock)->andReturn($activePermitApp);
        $licence1->shouldReceive('getLicNo')->once()->withNoArgs()->andReturn($licNo1);
        $licence1->shouldReceive('getTrafficArea->getName')->once()->withNoArgs()->andReturn($trafficArea1);
        $licence1->shouldReceive('isRestricted')->once()->withNoArgs()->andReturn($isRestricted1);
        $licence1->shouldReceive('getLicenceType->getDescription')->once()->withNoArgs()->andReturn($typeDesc1);

        $id2 = 222;
        $licNo2 = 'OB7654321';
        $trafficArea2 = 'traffic area 2';
        $isRestricted2 = false;
        $typeDesc2 = 'type description 2';

        $licence2 = m::mock(LicenceEntity::class);
        $licence2->shouldReceive('getId')->once()->withNoArgs()->andReturn($id2);
        $licence2->shouldReceive('getActiveIrhpApplication')->with($stock)->andReturnNull();
        $licence2->shouldReceive('getLicNo')->once()->withNoArgs()->andReturn($licNo2);
        $licence2->shouldReceive('getTrafficArea->getName')->once()->withNoArgs()->andReturn($trafficArea2);
        $licence2->shouldReceive('isRestricted')->once()->withNoArgs()->andReturn($isRestricted2);
        $licence2->shouldReceive('getLicenceType->getDescription')->once()->withNoArgs()->andReturn($typeDesc2);

        $licenceCollection = new ArrayCollection([$licence1, $licence2]);

        $expectedData = [
            $id1 => [
                'id' => $id1,
                'licNo' => $licNo1,
                'trafficArea' => $trafficArea1,
                'isRestricted' => $isRestricted1,
                'licenceTypeDesc' => $typeDesc1,
                'canMakeApplication' => false,
                'activeApplicationId' => $permitAppId,
            ],
            $id2 => [
                'id' => $id2,
                'licNo' => $licNo2,
                'trafficArea' => $trafficArea2,
                'isRestricted' => $isRestricted2,
                'licenceTypeDesc' => $typeDesc2,
                'canMakeApplication' => true,
                'activeApplicationId' => null,
            ]
        ];

        /** @var Entity | m\MockInterface $organisation */
        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->shouldReceive('getEligibleIrhpLicences')
            ->withNoArgs()
            ->once()
            ->andReturn($licenceCollection);

        self::assertEquals($expectedData, $organisation->getEligibleIrhpLicencesForStock($stock));
    }

    public function testGetDisqualificationNull(): void
    {
        /* @var $organisation Entity */
        $organisation = $this->instantiate($this->entityClass);
        $organisation->setDisqualifications(new ArrayCollection());

        $this->assertSame(null, $organisation->getDisqualification());
    }

    public function testGetDisqualification(): void
    {
        $disqualification = new Disqualification(m::mock(Entity::class));

        /* @var $organisation Entity */
        $organisation = $this->instantiate($this->entityClass);
        $organisation->setDisqualifications(new ArrayCollection([$disqualification]));

        $this->assertSame($disqualification, $organisation->getDisqualification());
    }

    public function testGetDisqualificationStatusNone(): void
    {
        /* @var $organisation Entity */
        $organisation = $this->instantiate($this->entityClass);
        $organisation->setDisqualifications(new ArrayCollection());

        $this->assertSame(Disqualification::STATUS_NONE, $organisation->getDisqualificationStatus());
    }

    public function testGetDisqualificationStatusActive(): void
    {
        $disqualification = new Disqualification(m::mock(Entity::class));
        $disqualification->setIsDisqualified('Y');
        $disqualification->setStartDate('2015-01-01');

        /* @var $organisation Entity */
        $organisation = $this->instantiate($this->entityClass);
        $organisation->setDisqualifications(new ArrayCollection([$disqualification]));

        $this->assertSame(Disqualification::STATUS_ACTIVE, $organisation->getDisqualificationStatus());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetLinkedLicences')]
    public function testGetLinkedLicences(mixed $status, mixed $expected): void
    {
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setStatus(new RefData($status));

        $licences = new ArrayCollection();
        $licences->add($licence);

        /** @var Entity | m\MockInterface $organisation */
        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->setLicences($licences);

        $this->assertEquals($expected, $organisation->getLinkedLicences()->contains($licence));
    }

    public static function dpGetLinkedLicences(): array
    {
        return [
            [LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION, false],
            [LicenceEntity::LICENCE_STATUS_NOT_SUBMITTED, false],
            [LicenceEntity::LICENCE_STATUS_SUSPENDED, true],
            [LicenceEntity::LICENCE_STATUS_VALID, true],
            [LicenceEntity::LICENCE_STATUS_CURTAILED, true],
            [LicenceEntity::LICENCE_STATUS_GRANTED, false],
            [LicenceEntity::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION, true],
            [LicenceEntity::LICENCE_STATUS_SURRENDERED, true],
            [LicenceEntity::LICENCE_STATUS_WITHDRAWN, false],
            [LicenceEntity::LICENCE_STATUS_REFUSED, false],
            [LicenceEntity::LICENCE_STATUS_REVOKED, true],
            [LicenceEntity::LICENCE_STATUS_NOT_TAKEN_UP, true],
            [LicenceEntity::LICENCE_STATUS_TERMINATED, true],
            [LicenceEntity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, true],
            [LicenceEntity::LICENCE_STATUS_UNLICENSED, true],
            [LicenceEntity::LICENCE_STATUS_CANCELLED, true],
        ];
    }

    public function testGetContextValue(): void
    {
        $entity = new Entity();
        $entity->setId(111);

        $this->assertEquals(111, $entity->getContextValue());
    }

    /**
     * Tests we're retrieving admin email addresses correctly
     */
    public function testGetAdminEmailAddresses(): void
    {
        $entity = new Entity();

        $email1 = 'foo@bar.com';
        $email2 = 'bar@foo.com';

        $expectedEmails = [
            0 => $email1,
            1 => $email2
        ];

        $disabledOrgUser = $this->getDisabledOrgUser();
        $deletedOrgUser = $this->getDeletedOrgUser();
        $nonAdminOrgUser = $this->getNonAdminOrgUser();
        $orgUser1 = $this->getAdminOrgUserWithEmail($email1);
        $orgUser2 = $this->getAdminOrgUserWithEmail($email2);

        $entity->setOrganisationUsers(new ArrayCollection([$disabledOrgUser, $deletedOrgUser, $nonAdminOrgUser, $orgUser1, $orgUser2]));

        $this->assertEquals($expectedEmails, $entity->getAdminEmailAddresses());
    }

    /**
     * two operator admins are found, so the final one can be deleted
     */
    public function testCanDeleteOperatorAdminTrue(): void
    {
        $entity = new Entity();

        $disabledOrgUser = $this->getDisabledOrgUser();
        $deletedOrgUser = $this->getDeletedOrgUser();
        $nonAdminOrgUser = $this->getNonAdminOrgUser();
        $orgUser1 = $this->getOperatorAdminOrgUser();
        $orgUser2 = $this->getAdminOrgUserNotOperatorAdminRole();
        $orgUser3 = $this->getOperatorAdminOrgUser();
        $orgUser4 = $this->getAdminOrgUserRoleNotChecked();

        $entity->setOrganisationUsers(new ArrayCollection([$disabledOrgUser, $deletedOrgUser, $nonAdminOrgUser, $orgUser1, $orgUser2, $orgUser3, $orgUser4]));

        $this->assertTrue($entity->canDeleteOperatorAdmin());
    }

    /**
     * only one operator admin is found, so the final operator admin can't be deleted
     */
    public function testCanDeleteOperatorAdminFalse(): void
    {
        $entity = new Entity();

        $disabledOrgUser = $this->getDisabledOrgUser();
        $deletedOrgUser = $this->getDeletedOrgUser();
        $nonAdminOrgUser = $this->getNonAdminOrgUser();
        $orgUser1 = $this->getAdminOrgUserNotOperatorAdminRole();
        $orgUser2 = $this->getOperatorAdminOrgUser();

        $entity->setOrganisationUsers(new ArrayCollection([$disabledOrgUser, $deletedOrgUser, $nonAdminOrgUser, $orgUser1, $orgUser2]));

        $this->assertFalse($entity->canDeleteOperatorAdmin());
    }

    /**
     * disabled user ignored
     * first user not op admin, 2nd user is op admin, 3rd user doesn't need to be checked
     */
    public function testHasOperatorAdminTrue(): void
    {
        $entity = new Entity();

        $disabledOrgUser = $this->getDisabledOrgUser();
        $deletedOrgUser = $this->getDeletedOrgUser();
        $nonAdminOrgUser = $this->getNonAdminOrgUser();
        $orgUser1 = $this->getOperatorAdminOrgUser();
        $orgUser2 = $this->getAdminOrgUserRoleNotChecked();

        $entity->setOrganisationUsers(new ArrayCollection([$disabledOrgUser, $deletedOrgUser, $nonAdminOrgUser, $orgUser1, $orgUser2]));

        $this->assertTrue($entity->hasOperatorAdmin());
    }

    /**
     * no operator admins are found
     */
    public function testHasOperatorAdminFalse(): void
    {
        $entity = new Entity();

        $disabledOrgUser = $this->getDisabledOrgUser();
        $deletedOrgUser = $this->getDeletedOrgUser();
        $nonAdminOrgUser = $this->getNonAdminOrgUser();
        $orgUser1 = $this->getAdminOrgUserNotOperatorAdminRole();
        $orgUser2 = $this->getAdminOrgUserNotOperatorAdminRole();

        $entity->setOrganisationUsers(new ArrayCollection([$disabledOrgUser, $deletedOrgUser, $nonAdminOrgUser, $orgUser1, $orgUser2]));

        $this->assertFalse($entity->hasOperatorAdmin());
    }

    /**
     * Test get allowed operator location from applications
     *
     * @param int $niFlag
     * @param string $allowedOperatorLocation
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('allowedOperatorLocationProviderApplications')]
    public function testGetAllowedOperatorLocationFromApplications(mixed $niFlag, mixed $allowedOperatorLocation): void
    {
        $mockOutstandingApplications = new ArrayCollection();
        $mockOutstandingApplications->add(
            m::mock()
            ->shouldReceive('getNiFlag')
            ->andReturn($niFlag)
            ->twice()
            ->getMock()
        );
        /** @var Entity | m\MockInterface $organisation */
        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->shouldReceive('getOutstandingApplications')
            ->with(true)
            ->andReturn($mockOutstandingApplications)
            ->once()
            ->getMock();

        $this->assertEquals($allowedOperatorLocation, $organisation->getAllowedOperatorLocation());
    }

    /**
     * Allowed operator location provider (applications)
     */
    public static function allowedOperatorLocationProviderApplications(): array
    {
        return [
            ['N', 'GB'],
            ['Y', 'NI'],
        ];
    }

    /**
     * Test get allowed operator location from licences
     *
     * @param string $trafficArea
     * @param string $allowedOperatorLocation
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('allowedOperatorLocationProviderLicences')]
    public function testGetAllowedOperatorLocationFromLicences(mixed $trafficArea, mixed $allowedOperatorLocation, mixed $licenceStatus): void
    {
        $mockTrafficArea = m::mock()
            ->shouldReceive('getId')
            ->andReturn($trafficArea)
            ->getMock();

        $mockStatus = m::mock()
            ->shouldReceive('getId')
            ->andReturn($licenceStatus)
            ->getMock();

        $mockOutstandingApplications = new ArrayCollection();
        $mockLicences = new ArrayCollection();
        $mockLicences->add(
            m::mock()
            ->shouldReceive('getTrafficArea')
            ->andReturn($mockTrafficArea)
            ->shouldReceive('getStatus')
            ->andReturn($mockStatus)
            ->getMock()
        );

        /** @var Entity | m\MockInterface  $organisation */
        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->shouldReceive('getOutstandingApplications')
            ->with(true)
            ->andReturn($mockOutstandingApplications)
            ->once()
            ->shouldReceive('getLicences')
            ->andReturn($mockLicences)
            ->once()
            ->getMock();

        $this->assertEquals($allowedOperatorLocation, $organisation->getAllowedOperatorLocation());
    }

    /**
     * Allowed operator location provider (licences)
     */
    public static function allowedOperatorLocationProviderLicences(): array
    {
        return [
            ['N', 'NI', LicenceEntity::LICENCE_STATUS_GRANTED],
            ['B', 'GB', LicenceEntity::LICENCE_STATUS_GRANTED],
            ['B', null, LicenceEntity::LICENCE_STATUS_WITHDRAWN],
        ];
    }

    /**
     * Test get allowed operator location with no licences and no outstanding applications
     */
    public function testGetAllowedOperatorLocationDefault(): void
    {
        $mockOutstandingApplications = new ArrayCollection();
        $mockLicences = new ArrayCollection();

        /** @var Entity | m\MockInterface  $organisation */
        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->shouldReceive('getOutstandingApplications')
            ->with(true)
            ->andReturn($mockOutstandingApplications)
            ->once()
            ->shouldReceive('getLicences')
            ->andReturn($mockLicences)
            ->once()
            ->getMock();

        $this->assertNull($organisation->getAllowedOperatorLocation());
    }

    /**
     * When there is one valid licence and no new apps, we can exit early, this is not MLH
     */
    public function testIsMlhOneValidAndNoNewLicences(): void
    {
        $organisation = new Entity();
        $mockLicence1 = m::mock(LicenceEntity::class);
        $mockLicence1->expects('getStatus')->withNoArgs()->times(2)->andReturn(LicenceEntity::LICENCE_STATUS_VALID);
        $mockLicence1->expects('getGoodsOrPsv')->withNoArgs()->andReturn(LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE);
        $mockLicence2 = m::mock(LicenceEntity::class);
        $mockLicence2->expects('getStatus')->withNoArgs()->times(2)->andReturn(LicenceEntity::LICENCE_STATUS_CURTAILED);
        $mockLicence2->expects('getGoodsOrPsv')->withNoArgs()->andReturn(LicenceEntity::LICENCE_CATEGORY_PSV);

        $organisation->setLicences(new ArrayCollection([$mockLicence1, $mockLicence2]));

        $this->assertFalse($organisation->isMlh());
    }

    /**
     * When there are no valid licences and one new app, we can exit early, this is not MLH
     */
    public function testIsMlhNoValidAndOneNewLicence(): void
    {
        $organisation = new Entity();
        $mockLicence1 = m::mock(LicenceEntity::class);
        $mockLicence1->expects('getStatus')->withNoArgs()->times(2)->andReturn(LicenceEntity::LICENCE_STATUS_GRANTED);
        $mockLicence2 = m::mock(LicenceEntity::class);
        $mockLicence2->expects('getStatus')->withNoArgs()->times(2)->andReturn(LicenceEntity::LICENCE_STATUS_CURTAILED);
        $mockLicence2->expects('getGoodsOrPsv')->withNoArgs()->andReturn(LicenceEntity::LICENCE_CATEGORY_PSV);

        $organisation->setLicences(new ArrayCollection([$mockLicence1, $mockLicence2]));

        $this->assertFalse($organisation->isMlh());
    }

    /**
     * There are no new apps and no existing valid licences, this is not MLH
     */
    public function testIsMlhNoValidNoNewLicences(): void
    {
        $organisation = new Entity();
        $organisation->setLicences(new ArrayCollection());
        $this->assertFalse($organisation->isMlh());
    }

    /**
     * When there are two valid licences, we can exit early, this is MLH without needing additional checks
     */
    public function testIsMlhTwoValidLicences(): void
    {
        $organisation = new Entity();
        $mockValidLicence1 = m::mock(LicenceEntity::class);
        $mockValidLicence1->expects('getStatus')->withNoArgs()->andReturn(LicenceEntity::LICENCE_STATUS_VALID);
        $mockValidLicence1->expects('getGoodsOrPsv')
            ->withNoArgs()
            ->andReturn(LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE);
        $mockValidLicence2 = m::mock(LicenceEntity::class);
        $mockValidLicence2->expects('getStatus')->withNoArgs()->andReturn(LicenceEntity::LICENCE_STATUS_CURTAILED);
        $mockValidLicence2->expects('getGoodsOrPsv')
            ->withNoArgs()
            ->andReturn(LicenceEntity::LICENCE_CATEGORY_GOODS_VEHICLE);

        $organisation->setLicences(new ArrayCollection([$mockValidLicence1, $mockValidLicence2]));

        $this->assertTrue($organisation->isMlh());
    }

    /**
     * When there are no valid licences and three new apps, the last two are goods, this is MLH
     */
    public function testIsMlhNoValidOneNewInvalidLicenceTwoNewValidLicences(): void
    {
        $organisation = new Entity();

        $psvApp = m::mock(ApplicationEntity::class);
        $psvApp->expects('isGoods')->withNoArgs()->andReturn(false);
        $goodsApp1 = m::mock(ApplicationEntity::class);
        $goodsApp1->expects('isGoods')->withNoArgs()->andReturn(true);
        $goodsApp2 = m::mock(ApplicationEntity::class);
        $goodsApp2->expects('isGoods')->withNoArgs()->andReturn(true);

        $mockLicence1 = m::mock(LicenceEntity::class);
        $mockLicence1->expects('getStatus')->withNoArgs()->times(2)->andReturn(LicenceEntity::LICENCE_STATUS_GRANTED);
        $mockLicence1->expects('getApplications')->withNoArgs()->andReturn(new ArrayCollection([$psvApp]));

        $mockLicence2 = m::mock(LicenceEntity::class);
        $mockLicence2->expects('getStatus')->withNoArgs()->times(2)->andReturn(LicenceEntity::LICENCE_STATUS_GRANTED);
        $mockLicence2->expects('getApplications')->withNoArgs()->andReturn(new ArrayCollection());

        $mockLicence3 = m::mock(LicenceEntity::class);
        $mockLicence3->expects('getStatus')->withNoArgs()->times(2)->andReturn(LicenceEntity::LICENCE_STATUS_GRANTED);
        $mockLicence3->expects('getApplications')->withNoArgs()->andReturn(new ArrayCollection([$goodsApp1]));

        $mockLicence4 = m::mock(LicenceEntity::class);
        $mockLicence4->expects('getStatus')
            ->withNoArgs()
            ->times(2)
            ->andReturn(LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION);
        $mockLicence4->expects('getApplications')->withNoArgs()->andReturn(new ArrayCollection([$goodsApp2]));

        $organisation->setLicences(new ArrayCollection([$mockLicence1, $mockLicence2, $mockLicence3, $mockLicence4]));

        $this->assertTrue($organisation->isMlh());
    }

    public function testHasUnlicencedLicences(): void
    {
        $mockLic1 = m::mock(LicenceEntity::class)
            ->shouldReceive('getLicNo')
            ->andReturn('FOO')
            ->getMock();

        $mockLic2 = m::mock(LicenceEntity::class)
            ->shouldReceive('getLicNo')
            ->andReturn('UA123')
            ->getMock();

        $licences = new ArrayCollection();
        $licences->add($mockLic1);
        $licences->add($mockLic2);

        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->shouldReceive('getLicences')
            ->andReturn($licences)
            ->once()
            ->getMock();

        $this->assertTrue($organisation->hasUnlicencedLicences());
    }

    /**
     * Test whether the organisation is eligible for permits.
     * Use 3 mock licences
     * The first one will not meet the criteria and be skipped
     * The second one will meet the criteria
     * The third licence won't need to be checked at all
     */
    public function testIsEligibleForPermitsWhenTrue(): void
    {
        $mockLicence1 = m::mock(LicenceEntity::class);
        $mockLicence1->shouldReceive('isEligibleForPermits')->once()->andReturn(false);
        $mockLicence2 = m::mock(LicenceEntity::class);
        $mockLicence2->shouldReceive('isEligibleForPermits')->once()->andReturn(true);
        $mockLicence3 = m::mock(LicenceEntity::class);
        $mockLicence3->shouldReceive('isEligibleForPermits')->never();

        $licences = new ArrayCollection([$mockLicence1, $mockLicence2, $mockLicence3]);

        $entity = $this->instantiate(Entity::class);
        $entity->setLicences($licences);
        $this->assertEquals(true, $entity->isEligibleForPermits());
    }

    /**
     * Test whether the organisation is eligible for permits.
     * Use 3 mock licences, all 3 won't meet the criteria hence we return false
     */
    public function testIsEligibleForPermitsWhenFalse(): void
    {
        $mockLicence1 = m::mock(LicenceEntity::class);
        $mockLicence1->shouldReceive('isEligibleForPermits')->once()->andReturn(false);
        $mockLicence2 = m::mock(LicenceEntity::class);
        $mockLicence2->shouldReceive('isEligibleForPermits')->once()->andReturn(false);
        $mockLicence3 = m::mock(LicenceEntity::class);
        $mockLicence3->shouldReceive('isEligibleForPermits')->once()->andReturn(false);

        $licences = new ArrayCollection([$mockLicence1, $mockLicence2, $mockLicence3]);

        $entity = $this->instantiate(Entity::class);
        $entity->setLicences($licences);
        $this->assertEquals(false, $entity->isEligibleForPermits());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHasSubmittedLicenceApplication')]
    public function testHasSubmittedLicenceApplication(mixed $status, mixed $expected): void
    {
        if (!is_null($status)) {
            $licence = m::mock(LicenceEntity::class)->makePartial();
            $licence->setStatus(new RefData($status));
            $collection = new ArrayCollection([$licence]);
        } else {
            $collection = new ArrayCollection([]);
        }

        /** @var Entity | m\MockInterface $organisation */
        $organisation = m::mock(Entity::class)->makePartial();
        $organisation->setLicences($collection);

        $this->assertEquals(
            $expected,
            $organisation->hasSubmittedLicenceApplication()
        );
    }

    public static function dpHasSubmittedLicenceApplication(): array
    {
        return [
            [null, false],
            [LicenceEntity::LICENCE_STATUS_UNDER_CONSIDERATION, true],
            [LicenceEntity::LICENCE_STATUS_NOT_SUBMITTED, false],
            [LicenceEntity::LICENCE_STATUS_SUSPENDED, true],
            [LicenceEntity::LICENCE_STATUS_VALID, true],
            [LicenceEntity::LICENCE_STATUS_CURTAILED, true],
            [LicenceEntity::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION, true],
            [LicenceEntity::LICENCE_STATUS_SURRENDERED, true],
            [LicenceEntity::LICENCE_STATUS_WITHDRAWN, true],
            [LicenceEntity::LICENCE_STATUS_REFUSED, true],
            [LicenceEntity::LICENCE_STATUS_REVOKED, true],
            [LicenceEntity::LICENCE_STATUS_NOT_TAKEN_UP, true],
            [LicenceEntity::LICENCE_STATUS_TERMINATED, true],
            [LicenceEntity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, true],
            [LicenceEntity::LICENCE_STATUS_UNLICENSED, true],
            [LicenceEntity::LICENCE_STATUS_CANCELLED, false],
        ];
    }

    private function getDisabledOrgUser(): m\LegacyMockInterface
    {
        $disabledUser = m::mock(UserEntity::class);
        $disabledUser->expects('getAccountDisabled')->withNoArgs()->andReturn('Y');
        $disabledUser->expects('isOperatorAdministrator')->never();

        $disabledOrgUser = m::mock(OrganisationUser::class)->makePartial();
        $disabledOrgUser->setIsAdministrator('Y');
        $disabledOrgUser->expects('getUser')->withNoArgs()->andReturn($disabledUser);

        return $disabledOrgUser;
    }

    private function getDeletedOrgUser(): m\LegacyMockInterface
    {
        $deletedOrgUser = m::mock(OrganisationUser::class)->makePartial();
        $deletedOrgUser->setIsAdministrator('Y');
        $deletedOrgUser->expects('getUser')->withNoArgs()->andThrow(EntityNotFoundException::class);

        return $deletedOrgUser;
    }

    private function getNonAdminOrgUser(): m\LegacyMockInterface
    {
        $nonAdminOrgUser = m::mock(OrganisationUser::class)->makePartial();
        $nonAdminOrgUser->setIsAdministrator('N');
        $nonAdminOrgUser->expects('getUser')->never();

        return $nonAdminOrgUser;
    }

    private function getAdminOrgUserRoleNotChecked(): m\LegacyMockInterface
    {
        $user = m::mock(UserEntity::class);
        $user->expects('getAccountDisabled')->withNoArgs()->andReturn('N');
        $user->expects('isOperatorAdministrator')->never();

        $adminOrgUserRoleNotChecked = m::mock(OrganisationUser::class)->makePartial();
        $adminOrgUserRoleNotChecked->setIsAdministrator('Y');
        $adminOrgUserRoleNotChecked->expects('getUser')->withNoArgs()->andReturn($user);

        return $adminOrgUserRoleNotChecked;
    }

    private function getOperatorAdminOrgUser(): m\LegacyMockInterface
    {
        $user = m::mock(UserEntity::class);
        $user->expects('getAccountDisabled')->withNoArgs()->andReturn('N');
        $user->expects('isOperatorAdministrator')->withNoArgs()->andReturnTrue();

        $operatorAdminOrgUser = m::mock(OrganisationUser::class)->makePartial();
        $operatorAdminOrgUser->setIsAdministrator('Y');
        $operatorAdminOrgUser->expects('getUser')->twice()->withNoArgs()->andReturn($user);

        return $operatorAdminOrgUser;
    }

    private function getAdminOrgUserNotOperatorAdminRole(): m\LegacyMockInterface
    {
        $user = m::mock(UserEntity::class);
        $user->expects('getAccountDisabled')->withNoArgs()->andReturn('N');
        $user->expects('isOperatorAdministrator')->withNoArgs()->andReturnFalse();

        $operatorAdminOrgUser = m::mock(OrganisationUser::class)->makePartial();
        $operatorAdminOrgUser->setIsAdministrator('Y');
        $operatorAdminOrgUser->expects('getUser')->twice()->withNoArgs()->andReturn($user);

        return $operatorAdminOrgUser;
    }

    private function getAdminOrgUserWithEmail(string $email): m\LegacyMockInterface
    {
        $user = m::mock(UserEntity::class);
        $user->expects('getAccountDisabled')->withNoArgs()->andReturn('N');
        $user->expects('getContactDetails->getEmailAddress')->withNoArgs()->andReturn($email);

        $operatorAdminOrgUser = m::mock(OrganisationUser::class)->makePartial();
        $operatorAdminOrgUser->setIsAdministrator('Y');
        $operatorAdminOrgUser->expects('getUser')->twice()->withNoArgs()->andReturn($user);

        return $operatorAdminOrgUser;
    }
}
