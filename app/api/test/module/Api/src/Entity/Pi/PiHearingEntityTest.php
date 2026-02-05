<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Pi;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Pi\PiHearing as Entity;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * PiHearing Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class PiHearingEntityTest extends EntityTester
{
    /**
     * Holds the entity
     *
     * @var Entity
     */
    protected $entity;

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function setUp(): void
    {
        /** @var \Dvsa\Olcs\Api\Entity\Pi\PiHearing entity */
        $this->entity = $this->instantiate($this->entityClass);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderHearingBeforeAgreedDateValidate')]
    public function testCreateValidationHearingBeforeAgreedDate(mixed $expectException, mixed $hearingDate, mixed $piAgreedDate): void
    {
        $piEntity = m::mock(PiEntity::class);
        $piEntity->shouldReceive('isClosed')->andReturn(false);
        $piEntity->shouldReceive('getAgreedDate')->with(true)->atLeast(1)->andReturn($piAgreedDate);
        $presidingTc = m::mock(PresidingTcEntity::class);
        $presidedByRole = m::mock(RefData::class);

        try {
            $entity = new Entity(
                $piEntity,
                $presidingTc,
                $presidedByRole,
                $hearingDate,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null
            );

            $this->assertInstanceOf(Entity::class, $entity);

            if ($expectException === true) {
                $this->fail('ValidationException SHOULD have been thrown');
            }
        } catch (ValidationException $e) {
            if ($expectException === false) {
                $this->fail('ValidationException should NOT have been thrown');
            }

            $this->assertEquals(
                [Entity::MSG_HEARING_DATE_BEFORE_PI_DATE => $piAgreedDate->format('Y-m-d')],
                $e->getMessages()
            );
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderHearingBeforeAgreedDateValidate')]
    public function testUpdateValidationDecisionBeforeHearing(mixed $expectException, mixed $hearingDate, mixed $piAgreedDate): void
    {
        $sut = $this->instantiate($this->entityClass);
        $piEntity = m::mock(PiEntity::class);
        $piEntity->shouldReceive('isClosed')->andReturn(false);
        $piEntity->shouldReceive('getAgreedDate')->with(true)->atLeast(1)->andReturn($piAgreedDate);
        $sut->setPi($piEntity);
        $presidingTc = m::mock(PresidingTcEntity::class);
        $presidedByRole = m::mock(RefData::class);

        try {
            $sut->update(
                $presidingTc,
                $presidedByRole,
                $hearingDate,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null
            );

            if ($expectException === true) {
                $this->fail('ValidationException SHOULD have been thrown');
            }

            // Assert the entity was successfully updated when no exception expected
            $this->assertInstanceOf(Entity::class, $sut);
        } catch (ValidationException $e) {
            if ($expectException === false) {
                $this->fail('ValidationException should NOT have been thrown');
            }

            $this->assertEquals(
                [Entity::MSG_HEARING_DATE_BEFORE_PI_DATE => $piAgreedDate->format('Y-m-d')],
                $e->getMessages()
            );
        }
    }

    /**
     * test create
     */
    public function testCreateNotAdjournedOrCancelled(): void
    {
        $piEntity = m::mock(PiEntity::class);
        $piEntity->shouldReceive('isClosed')->andReturn(false);
        $piEntity->shouldReceive('getAgreedDate')->andReturn(null);
        $presidingTc = m::mock(PresidingTcEntity::class);
        $presidedByRole = m::mock(RefData::class);
        $hearingDate = new \DateTime();
        $venue = null;
        $venueOther = 'other venue';
        $witnesses = 2;
        $drivers = 1;
        $isCancelled = 'N';
        $cancelledReason = 'cancelled reason';
        $isAdjourned = 'N';
        $adjournedReason = 'adjourned reason';
        $details = 'details';
        $adjournedDate = '2015-12-25 12:00:00';
        $cancelledDate = '2015-12-25';

        $hearing = new Entity(
            $piEntity,
            $presidingTc,
            $presidedByRole,
            $hearingDate,
            $venue,
            $venueOther,
            $witnesses,
            $drivers,
            $isCancelled,
            $cancelledDate,
            $cancelledReason,
            $isAdjourned,
            $adjournedDate,
            $adjournedReason,
            $details
        );

        $this->assertEquals($piEntity, $hearing->getPi());
        $this->assertEquals($presidingTc, $hearing->getPresidingTc());
        $this->assertEquals($presidedByRole, $hearing->getPresidedByRole());
        $this->assertEquals($hearingDate, $hearing->getHearingDate());
        $this->assertEquals($venue, $hearing->getVenue());
        $this->assertEquals($venueOther, $hearing->getVenueOther());
        $this->assertEquals($witnesses, $hearing->getWitnesses());
        $this->assertEquals($isCancelled, $hearing->getIsCancelled());
        $this->assertEquals(null, $hearing->getCancelledDate());
        $this->assertEquals(null, $hearing->getCancelledReason());
        $this->assertEquals($isAdjourned, $hearing->getIsAdjourned());
        $this->assertEquals(null, $hearing->getAdjournedDate());
        $this->assertEquals(null, $hearing->getAdjournedReason());
        $this->assertEquals($details, $hearing->getDetails());
    }

    /**
     * test update with different adjourned dates to also test date processing
     *
     * @param string|null $adjournedDate
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('adjournedDateProvider')]
    public function testUpdateWithAdjournedOrCancelled(mixed $adjournedDate): void
    {
        $piEntity = m::mock(PiEntity::class);
        $piEntity->shouldReceive('isClosed')->andReturn(false);
        $piEntity->shouldReceive('getAgreedDate')->andReturn(null);
        $this->entity->setPi($piEntity);
        $presidingTc = m::mock(PresidingTcEntity::class);
        $presidedByRole = m::mock(RefData::class);
        $hearingDate = new \DateTime();
        $venue = null;
        $venueOther = 'other venue';
        $witnesses = 2;
        $drivers = 1;
        $isCancelled = 'Y';
        $cancelledReason = 'cancelled reason';
        $isAdjourned = 'Y';
        $adjournedReason = 'adjourned reason';
        $details = 'details';

        if ($adjournedDate == null) {
            $entityAdjournedDate = null;
        } else {
            $entityAdjournedDate = new \DateTime($adjournedDate);
        }

        $cancelledDate = '2015-12-25';
        $entityCancelledDate = \DateTime::createFromFormat('Y-m-d', $cancelledDate)->setTime(0, 0, 0);

        $this->entity->update(
            $presidingTc,
            $presidedByRole,
            $hearingDate,
            $venue,
            $venueOther,
            $witnesses,
            $drivers,
            $isCancelled,
            $cancelledDate,
            $cancelledReason,
            $isAdjourned,
            $adjournedDate,
            $adjournedReason,
            $details
        );

        $this->assertEquals($presidingTc, $this->entity->getPresidingTc());
        $this->assertEquals($presidedByRole, $this->entity->getPresidedByRole());
        $this->assertEquals($hearingDate, $this->entity->getHearingDate());
        $this->assertEquals($venue, $this->entity->getVenue());
        $this->assertEquals($venueOther, $this->entity->getVenueOther());
        $this->assertEquals($witnesses, $this->entity->getWitnesses());
        $this->assertEquals($drivers, $this->entity->getDrivers());
        $this->assertEquals($isCancelled, $this->entity->getIsCancelled());
        $this->assertEquals($entityCancelledDate, $this->entity->getCancelledDate());
        $this->assertEquals($cancelledReason, $this->entity->getCancelledReason());
        $this->assertEquals($isAdjourned, $this->entity->getIsAdjourned());
        $this->assertEquals($entityAdjournedDate, $this->entity->getAdjournedDate());
        $this->assertEquals($adjournedReason, $this->entity->getAdjournedReason());
        $this->assertEquals($details, $this->entity->getDetails());
    }

    public function testCreateExceptionWhenPiClosed(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $piEntity = m::mock(PiEntity::class);
        $piEntity->shouldReceive('isClosed')->andReturn(true);
        $presidingTc = m::mock(PresidingTcEntity::class);
        $presidedByRole = m::mock(RefData::class);
        $hearingDate = m::mock(\DateTime::class);

        $hearing = new Entity(
            $piEntity,
            $presidingTc,
            $presidedByRole,
            $hearingDate,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );
    }

    public function testUpdateExceptionWhenPiClosed(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $piEntity = m::mock(PiEntity::class);
        $piEntity->shouldReceive('isClosed')->andReturn(true);
        $this->entity->setPi($piEntity);
        $presidingTc = m::mock(PresidingTcEntity::class);
        $presidedByRole = m::mock(RefData::class);
        $hearingDate = m::mock(\DateTime::class);

        $this->entity->update(
            $presidingTc,
            $presidedByRole,
            $hearingDate,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );
    }

    /**
     * @return array
     */
    public static function adjournedDateProvider(): array
    {
        return [
            [null],
            ['2015-12-25T12:00:00+01:00']
        ];
    }

    public static function dataProviderHearingBeforeAgreedDateValidate(): array
    {
        return [
            // $expectException, $hearingDate, $piAgreedDate
            [true, new \DateTime('2012-10-09'), new \DateTime('2017-10-10')],
            [true, new \DateTime('2017-10-09 23:45'), new \DateTime('2017-10-10')],
            [false, new \DateTime('2017-10-10 00:01'), new \DateTime('2017-10-10')],
            [false, new \DateTime('2017-10-10 23:00 '), new \DateTime('2017-10-10')],
            [false, new \DateTime('2017-10-10 00:00 +01:00 '), new \DateTime('2017-10-10')],
            [false, new \DateTime('2017-10-10 01:00 +01:00 '), new \DateTime('2017-10-10')],
            [false, new \DateTime('2017-10-10 23:45 +01:00 '), new \DateTime('2017-10-10')],
            [false, new \DateTime('2017-10-11'), new \DateTime('2017-10-10')],
            [false, new \DateTime('2027-10-10'), new \DateTime('2017-10-10')],
        ];
    }
}
