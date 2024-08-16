<?php

namespace Dvsa\OlcsTest\Api\Entity\Irfo;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as Entity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermitType as IrfoGvPermitTypeEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * IrfoGvPermit Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class IrfoGvPermitEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function setUp(): void
    {
        /** @var Entity entity */
        $this->entity = $this->instantiate($this->entityClass);
    }

    public function testConstruct()
    {
        $organisation = m::mock(OrganisationEntity::class);
        $type = m::mock(IrfoGvPermitTypeEntity::class);
        $status = m::mock(RefData::class);

        $entity = new Entity($organisation, $type, $status);

        $this->assertSame($organisation, $entity->getOrganisation());
        $this->assertSame($type, $entity->getIrfoGvPermitType());
        $this->assertSame($status, $entity->getIrfoPermitStatus());
    }

    public function testUpdate()
    {
        $irfoGvPermitType = m::mock(IrfoGvPermitTypeEntity::class);
        $yearRequired = 2010;
        $inForceDate = new \DateTime('2010-02-03');
        $expiryDate = new \DateTime('2011-02-03');
        $noOfCopies = 11;
        $isFeeExempt = 'N';
        $exemptionDetails = 'testing';
        $irfoFeeId = 'N00001';

        $this->entity->update(
            $irfoGvPermitType,
            $yearRequired,
            $inForceDate,
            $expiryDate,
            $noOfCopies,
            $isFeeExempt,
            $exemptionDetails,
            $irfoFeeId
        );

        $this->assertEquals($irfoGvPermitType, $this->entity->getIrfoGvPermitType());
        $this->assertEquals($yearRequired, $this->entity->getYearRequired());
        $this->assertEquals($inForceDate, $this->entity->getInForceDate());
        $this->assertEquals($expiryDate, $this->entity->getExpiryDate());
        $this->assertEquals($noOfCopies, $this->entity->getNoOfCopies());
        $this->assertEquals($isFeeExempt, $this->entity->getIsFeeExempt());
        $this->assertEquals($exemptionDetails, $this->entity->getExemptionDetails());
        $this->assertEquals($irfoFeeId, $this->entity->getIrfoFeeId());
    }

    /**
     * Tests update throws exception correctly
     */
    public function testUpdateWithInvalidExpiryDate()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $irfoGvPermitType = m::mock(IrfoGvPermitTypeEntity::class);
        $yearRequired = 2010;
        $inForceDate = new \DateTime('2010-02-03');
        $expiryDate = new \DateTime('2010-01-05');
        $noOfCopies = 11;

        $this->entity->update(
            $irfoGvPermitType,
            $yearRequired,
            $inForceDate,
            $expiryDate,
            $noOfCopies
        );
    }

    public function testReset()
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_APPROVED);
        $this->entity->setIrfoPermitStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_PENDING);

        $this->entity->reset($newStatus);

        $this->assertEquals($newStatus, $this->entity->getIrfoPermitStatus());
    }

    /**
     * Tests reset throws exception correctly
     */
    public function testResetThrowsInvalidStatusException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_APPROVED);

        $this->entity->reset($status);

        return true;
    }

    public function testWithdraw()
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setIrfoPermitStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_WITHDRAWN);

        $this->entity->withdraw($newStatus);

        $this->assertEquals($newStatus, $this->entity->getIrfoPermitStatus());
    }

    /**
     * Tests withdraw throws exception correctly
     */
    public function testWithdrawThrowsInvalidStatusException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_APPROVED);

        $this->entity->withdraw($status);

        return true;
    }

    public function testRefuse()
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setIrfoPermitStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_REFUSED);

        $this->entity->refuse($newStatus);

        $this->assertEquals($newStatus, $this->entity->getIrfoPermitStatus());
    }

    /**
     * Tests refuse throws exception correctly
     */
    public function testRefuseThrowsInvalidStatusException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_APPROVED);

        $this->entity->refuse($status);

        return true;
    }

    /**
     * Tests approve throws exception correctly
     */
    public function testApproveThrowsInvalidStatusException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);

        $fees = [];

        $this->entity->approve($status, $fees);

        return true;
    }

    /**
     * Tests approve throws exception correctly
     */
    public function testApproveThrowsNotApprovableException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('isApprovable')->once()->andReturn(false);

        $status = new RefData();
        $status->setId(Entity::STATUS_APPROVED);

        $fees = [];

        $sut->approve($status, $fees);

        return true;
    }

    public function testApprove()
    {
        $sut = m::mock(Entity::class)->makePartial();
        $sut->shouldReceive('isApprovable')->once()->andReturn(true);

        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $sut->setIrfoPermitStatus($status);

        $newStatus = new RefData();
        $newStatus->setId(Entity::STATUS_APPROVED);

        $fees = [];

        $sut->approve($newStatus, $fees);

        $this->assertEquals($newStatus, $sut->getIrfoPermitStatus());

        return true;
    }

    public function testIsApprovable()
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setIrfoPermitStatus($status);

        $feeType = new FeeTypeEntity();

        $feeStatusPaid = new RefData();
        $feeStatusPaid->setId(FeeEntity::STATUS_PAID);

        $fees = [
            new FeeEntity($feeType, 10, $feeStatusPaid),
        ];

        $this->assertEquals(true, $this->entity->isApprovable($fees));
    }

    public function testIsApprovableWhenNotPending()
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_REFUSED);
        $this->entity->setIrfoPermitStatus($status);

        $fees = [];

        $this->assertEquals(false, $this->entity->isApprovable($fees));
    }

    public function testIsApprovableWhenWithoutFees()
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setIrfoPermitStatus($status);

        $fees = [];

        $this->assertEquals(false, $this->entity->isApprovable($fees));
    }

    public function testIsApprovableWhenOutstandingFees()
    {
        $status = new RefData();
        $status->setId(Entity::STATUS_PENDING);
        $this->entity->setIrfoPermitStatus($status);

        $feeType = new FeeTypeEntity();

        $feeStatusPaid = new RefData();
        $feeStatusPaid->setId(FeeEntity::STATUS_PAID);

        $feeStatusOutstanding = new RefData();
        $feeStatusOutstanding->setId(FeeEntity::STATUS_OUTSTANDING);

        $fees = [
            new FeeEntity($feeType, 10, $feeStatusPaid),
            new FeeEntity($feeType, 10, $feeStatusOutstanding),
        ];

        $this->assertEquals(false, $this->entity->isApprovable($fees));
    }

    /**
     * @dataProvider isGeneratableStates
     */
    public function testIsGeneratable($input, $expected)
    {
        $status = new RefData();
        $status->setId($input);
        $this->entity->setIrfoPermitStatus($status);

        $this->assertEquals($expected, $this->entity->isGeneratable());
    }

    public function isGeneratableStates()
    {
        return [
            [Entity::STATUS_APPROVED, true],
            [Entity::STATUS_PENDING, false],
            [Entity::STATUS_REFUSED, false],
            [Entity::STATUS_WITHDRAWN, false],
        ];
    }
}
