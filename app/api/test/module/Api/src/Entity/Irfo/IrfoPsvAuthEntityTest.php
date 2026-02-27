<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Irfo;

use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth as Entity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType as IrfoPsvAuthTypeEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth
 * @covers Dvsa\Olcs\Api\Entity\Irfo\AbstractIrfoPsvAuth
 */
class IrfoPsvAuthEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /** @var  Entity */
    protected $entity;

    /** @var  OrganisationEntity | m\MockInterface */
    private $mockOrg;
    /** @var  IrfoPsvAuthTypeEntity | m\MockInterface */
    private $mockType;
    /** @var  RefData */
    private $status;

    public function setUp(): void
    {
        /** @var Entity entity */
        $this->entity = $this->instantiate($this->entityClass);
        $this->entity->setId('999');

        $this->mockOrg = m::mock(OrganisationEntity::class);
        $this->mockType = m::mock(IrfoPsvAuthTypeEntity::class);
        $this->status = new RefData();
    }

    public function testConstruct(): void
    {
        $entity = new Entity($this->mockOrg, $this->mockType, $this->status);

        $this->assertSame($this->mockOrg, $entity->getOrganisation());
        $this->assertSame($this->mockType, $entity->getIrfoPsvAuthType());
        $this->assertSame($this->status, $entity->getStatus());
    }

    public function testUpdate(): void
    {
        /** @var IrfoPsvAuthTypeEntity $irfoPsvAuthType */
        $irfoPsvAuthType = m::mock(IrfoPsvAuthTypeEntity::class)->makePartial();
        $irfoPsvAuthType->setSectionCode('blah');
        $validityPeriod = 2;
        $inForceDate = new \DateTime('2010-02-03');
        $serviceRouteFrom = 'Bristol';
        $serviceRouteTo = 'Leeds';

        /** @var RefData $journeyFrequency */
        $journeyFrequency = m::mock(RefData::class)->makePartial();
        $journeyFrequency->setId('psv_freq_daily');

        $copiesRequired = 3;
        $copiesRequiredTotal = 4;

        $this->entity->update(
            $irfoPsvAuthType,
            $validityPeriod,
            $inForceDate,
            $serviceRouteFrom,
            $serviceRouteTo,
            $journeyFrequency,
            $copiesRequired,
            $copiesRequiredTotal
        );

        $this->assertEquals($irfoPsvAuthType, $this->entity->getIrfoPsvAuthType());
        $this->assertEquals($validityPeriod, $this->entity->getValidityPeriod());
        $this->assertEquals($inForceDate, $this->entity->getInForceDate());
        $this->assertEquals($serviceRouteFrom, $this->entity->getServiceRouteFrom());
        $this->assertEquals($serviceRouteTo, $this->entity->getServiceRouteTo());
        $this->assertEquals($journeyFrequency, $this->entity->getJourneyFrequency());
        $this->assertEquals($copiesRequired, $this->entity->getCopiesRequired());
        $this->assertEquals($copiesRequiredTotal, $this->entity->getCopiesRequiredTotal());
        $this->assertEquals('blah/999', $this->entity->getIrfoFileNo());
    }

    public function testPopulateIrfoFeeId(): void
    {
        $this->mockOrg->shouldReceive('getId')->once()->andReturn(44);

        $entity = new Entity($this->mockOrg, $this->mockType, $this->status);
        $entity->populateIrfoFeeId();

        $this->assertEquals('IR0000044', $entity->getIrfoFeeId());
    }

    public function testIsGrantableWithApplicationFee(): void
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setStatus($status);

        $feeType = new FeeTypeEntity();

        $feeStatusPaid = new RefData();
        $feeStatusPaid->setId(FeeEntity::STATUS_PAID);

        $fee = new FeeEntity($feeType, 10, $feeStatusPaid);

        $this->assertTrue($this->entity->isGrantable($fee));
    }

    public function testIsGrantableWithApplicationFeeNotPaid(): void
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setStatus($status);

        $feeType = new FeeTypeEntity();

        $feeStatusPaid = new RefData();
        $feeStatusPaid->setId(FeeEntity::STATUS_OUTSTANDING);

        $fee = new FeeEntity($feeType, 10, $feeStatusPaid);

        $this->assertFalse($this->entity->isGrantable($fee));
    }

    public function testIsGrantableWithApplicationFeePaidInvalidState(): void
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_GRANTED);
        $this->entity->setStatus($status);

        $feeType = new FeeTypeEntity();

        $feeStatusPaid = new RefData();
        $feeStatusPaid->setId(FeeEntity::STATUS_PAID);

        $fee = new FeeEntity($feeType, 10, $feeStatusPaid);

        $this->assertFalse($this->entity->isGrantable($fee));
    }

    public function testGrant(): void
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setStatus($status);

        $feeType = new FeeTypeEntity();
        $feeStatusPaid = new RefData();
        $feeStatusPaid->setId(FeeEntity::STATUS_PAID);
        $fee = new FeeEntity($feeType, 10, $feeStatusPaid);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_GRANTED);

        $this->entity->grant($newStatus, $fee);

        $this->assertEquals($newStatus, $this->entity->getStatus());
    }

    public function testGrantFeeNotPaidThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setStatus($status);

        $feeType = new FeeTypeEntity();
        $feeStatusPaid = new RefData();
        $feeStatusPaid->setId(FeeEntity::STATUS_OUTSTANDING);
        $fee = new FeeEntity($feeType, 10, $feeStatusPaid);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_GRANTED);

        $this->entity->grant($newStatus, $fee);
    }

    public function testGrantInvalidStateThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_GRANTED);
        $this->entity->setStatus($status);

        $feeType = new FeeTypeEntity();
        $feeStatusPaid = new RefData();
        $feeStatusPaid->setId(FeeEntity::STATUS_PAID);
        $fee = new FeeEntity($feeType, 10, $feeStatusPaid);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_GRANTED);

        $this->entity->grant($newStatus, $fee);

        $this->assertEquals($newStatus, $this->entity->getStatus());
    }

    /**
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isRefusableStates')]
    public function testIsRefusable(mixed $input, mixed $expected): void
    {
        $status = new RefData();
        $status->setId($input);
        $this->entity->setStatus($status);

        $this->assertEquals($expected, $this->entity->isRefusable());
    }

    public function testIsRefusableInvalidState(): void
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_GRANTED);
        $this->entity->setStatus($status);

        $this->assertFalse($this->entity->isRefusable());
    }

    public function testRefuse(): void
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_REFUSED);

        $this->entity->refuse($newStatus);

        $this->assertEquals($newStatus, $this->entity->getStatus());
    }

    public function testRefuseThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_REFUSED);
        $this->entity->setStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_REFUSED);

        $this->entity->refuse($newStatus);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isWithdrawableStates')]
    public function testIsWithdrawable(mixed $input, mixed $expected): void
    {
        $status = new RefData();
        $status->setId($input);
        $this->entity->setStatus($status);

        $this->assertEquals($expected, $this->entity->isWithdrawable());
    }

    public function testWithdraw(): void
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_WITHDRAWN);

        $this->entity->withdraw($newStatus);

        $this->assertEquals($newStatus, $this->entity->getStatus());
    }

    public function testWithdrawThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_WITHDRAWN);
        $this->entity->setStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_WITHDRAWN);

        $this->entity->withdraw($newStatus);
    }

    public static function isWithdrawableStates(): array
    {
        return [
            [Entity::STATUS_PENDING, true],
            [Entity::STATUS_CNS, true],
            [Entity::STATUS_RENEW, true],
            [Entity::STATUS_APPROVED, true],
            [Entity::STATUS_WITHDRAWN, false],
            [Entity::STATUS_GRANTED, false],
            [Entity::STATUS_REFUSED, false]
        ];
    }

    public static function isRefusableStates(): array
    {
        return [
            [Entity::STATUS_PENDING, true],
            [Entity::STATUS_CNS, false],
            [Entity::STATUS_RENEW, true],
            [Entity::STATUS_APPROVED, false],
            [Entity::STATUS_WITHDRAWN, false],
            [Entity::STATUS_GRANTED, false],
            [Entity::STATUS_REFUSED, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isCnsableStates')]
    public function testIsCnsable(mixed $input, mixed $expected): void
    {
        $status = new RefData();
        $status->setId($input);
        $this->entity->setStatus($status);

        $this->assertEquals($expected, $this->entity->isCnsable());
    }

    public static function isCnsableStates(): array
    {
        return [
            [Entity::STATUS_PENDING, false],
            [Entity::STATUS_CNS, false],
            [Entity::STATUS_RENEW, true],
            [Entity::STATUS_APPROVED, false],
            [Entity::STATUS_WITHDRAWN, false],
            [Entity::STATUS_GRANTED, false],
            [Entity::STATUS_REFUSED, false]
        ];
    }

    public function testContinuationNotSought(): void
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_RENEW);
        $this->entity->setStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_CNS);

        $this->entity->continuationNotSought($newStatus);

        $this->assertEquals($newStatus, $this->entity->getStatus());
    }

    public function testContinuationNotSoughtThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_CNS);

        $this->entity->continuationNotSought($newStatus);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isApprovableStates')]
    public function testIsApprovable(mixed $statusId, mixed $outstandingFees, mixed $expected): void
    {
        $status = new RefData();
        $status->setId($statusId);
        $this->entity->setStatus($status);

        $this->assertEquals($expected, $this->entity->isApprovable($outstandingFees));
    }

    public static function isApprovableStates(): array
    {
        return [
            [Entity::STATUS_PENDING, [], false],
            [Entity::STATUS_CNS, [], false],
            [Entity::STATUS_RENEW, [], false],
            [Entity::STATUS_APPROVED, [], false],
            [Entity::STATUS_WITHDRAWN, [], false],
            [Entity::STATUS_GRANTED, [], true],
            [Entity::STATUS_GRANTED, ['FEE'], false],
            [Entity::STATUS_REFUSED, [], false]
        ];
    }

    public function testApprove(): void
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_GRANTED);
        $this->entity->setStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_APPROVED);

        $this->assertNull($this->entity->getRenewalDate());

        $this->entity->approve($newStatus, []);

        $this->assertEquals($newStatus, $this->entity->getStatus());
        $this->assertInstanceOf(\DateTime::class, $this->entity->getRenewalDate());
    }

    public function testApproveThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_GRANTED);
        $this->entity->setStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_APPROVED);

        $this->entity->approve($newStatus, ['FEE']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isRenewableStates')]
    public function testIsRenewable(mixed $input, mixed $expected): void
    {
        $status = new RefData();
        $status->setId($input);
        $this->entity->setStatus($status);

        $this->assertEquals($expected, $this->entity->isRenewable());
    }

    public static function isRenewableStates(): array
    {
        return [
            [Entity::STATUS_PENDING, true],
            [Entity::STATUS_CNS, false],
            [Entity::STATUS_RENEW, true],
            [Entity::STATUS_APPROVED, true],
            [Entity::STATUS_WITHDRAWN, false],
            [Entity::STATUS_GRANTED, true],
            [Entity::STATUS_REFUSED, false]
        ];
    }

    public function testRenew(): void
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_RENEW);

        $this->entity->renew($newStatus);

        $this->assertEquals($newStatus, $this->entity->getStatus());
    }

    public function testRenewThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_WITHDRAWN);
        $this->entity->setStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_RENEW);

        $this->entity->renew($newStatus);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isGeneratableDataProvider')]
    public function testIsGeneratable(mixed $statusId, mixed $outstandingFees, mixed $expected): void
    {
        $status = new RefData();
        $status->setId($statusId);
        $this->entity->setStatus($status);

        $this->assertEquals($expected, $this->entity->isGeneratable($outstandingFees));
    }

    public static function isGeneratableDataProvider(): array
    {
        return [
            [Entity::STATUS_PENDING, [], false],
            [Entity::STATUS_CNS, [], false],
            [Entity::STATUS_RENEW, [], false],
            [Entity::STATUS_APPROVED, [], true],
            [Entity::STATUS_WITHDRAWN, [], false],
            [Entity::STATUS_GRANTED, [], false],
            [Entity::STATUS_GRANTED, ['FEE'], false],
            [Entity::STATUS_REFUSED, [], false]
        ];
    }

    public function testGenerate(): void
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_APPROVED);
        $this->entity->setStatus($status);

        $this->entity->setCopiesRequired(2);
        $this->entity->setCopiesRequiredTotal(5);

        $this->entity->setCopiesIssued(20);
        $this->entity->setCopiesIssuedTotal(50);

        $this->entity->generate([]);

        $this->assertEquals(0, $this->entity->getCopiesRequired());
        $this->assertEquals(0, $this->entity->getCopiesRequiredTotal());
        $this->assertEquals(22, $this->entity->getCopiesIssued());
        $this->assertEquals(55, $this->entity->getCopiesIssuedTotal());
    }

    public function testGenerateThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_GRANTED);
        $this->entity->setStatus($status);

        $this->entity->generate(['FEE']);
    }

    public function testIsResetableState(): void
    {
        $status = new RefData();
        $this->entity->setStatus($status);

        //  check false
        $status->setId(Entity::STATUS_PENDING);

        static::assertFalse($this->entity->isResetable());

        //  check true
        $status->setId('UNIT_NOT_PENDING');

        static::assertTrue($this->entity->isResetable());
    }

    public function testReset(): void
    {
        $newStatus = new RefData();

        $statusNoPending = (new RefData())
            ->setId('NOT_PENDING_STATUS');

        $this->entity
            ->setStatus($statusNoPending)
            ->reset($newStatus);

        static::assertSame($newStatus, $this->entity->getStatus());
    }

    public function testReexpectException(): void
    {
        $this->expectException(BadRequestException::class);

        $this->entity
            ->setStatus(
                (new RefData())
                    ->setId(Entity::STATUS_PENDING)
            )
            ->reset(new RefData());

        static::assertEquals('UNIT_STATUS', $this->entity->getStatus()->getId());
    }

    public function testGetRelatedOrganisation(): void
    {
        /** @var Organisation $mockOrg */
        $mockOrg = m::mock(Organisation::class);

        $this->entity->setOrganisation($mockOrg);

        static::assertSame($mockOrg, $this->entity->getRelatedOrganisation());
    }
}
