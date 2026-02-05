<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Cases;

use Dvsa\Olcs\Api\Entity\Venue;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Cases\Impounding as Entity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Mockery as m;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Impounding Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class ImpoundingEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testSetOtherVenueProperties(): void
    {
        $mockCase = m::mock(CasesEntity::class);
        $mockImpoundingType = m::mock(RefDataEntity::class);

        $sut = new Entity($mockCase, $mockImpoundingType);

        $sut->setVenueProperties(Entity::VENUE_OTHER, 'foo');

        $this->assertNull($sut->getVenue());
        $this->assertEquals('foo', $sut->getVenueOther());
    }

    public function testUpdatePaperwork(): void
    {
        $impoundingType = m::mock(RefDataEntity::class);
        $impoundingType->shouldReceive('getId')->once()->andReturn(Entity::TYPE_PAPERWORK);
        $impoundingLegislationTypes = m::mock(ArrayCollection::class);
        $venue = m::mock(Venue::class);
        $venueOther = 'venue other';
        $applicationReceiptDate = '2015-12-25';
        $vrm = 'vrm';
        $hearingDate = '2017-12-25T15:45:00+0100';
        $presidingTc = m::mock(RefDataEntity::class);
        $outcome = m::mock(RefDataEntity::class);
        $outcomeSentDate = '2016-12-25';
        $notes = 'notes';

        $entity = $this->instantiate(Entity::class);
        $entity->update(
            $impoundingType,
            $impoundingLegislationTypes,
            $venue,
            $venueOther,
            $applicationReceiptDate,
            $vrm,
            $hearingDate,
            $presidingTc,
            $outcome,
            $outcomeSentDate,
            $notes
        );

        $this->assertSame($impoundingType, $entity->getImpoundingType());
        $this->assertSame($impoundingLegislationTypes, $entity->getImpoundingLegislationTypes());
        $this->assertSame(null, $entity->getVenue());
        $this->assertSame(null, $entity->getVenueOther());
        $this->assertSame($applicationReceiptDate, $entity->getApplicationReceiptDate()->format('Y-m-d'));
        $this->assertSame($vrm, $entity->getVrm());
        $this->assertSame(null, $entity->getHearingDate());
        $this->assertSame($presidingTc, $entity->getPresidingTc());
        $this->assertSame($outcome, $entity->getOutcome());
        $this->assertSame($outcomeSentDate, $entity->getOutcomeSentDate()->format('Y-m-d'));
        $this->assertSame($notes, $entity->getNotes());
    }

    /**
     *
     * @param $inputVenue
     * @param $inputOther
     * @param $savedVenue
     * @param $savedOther
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('updateHearingProvider')]
    public function testUpdateHearing(mixed $inputVenue, mixed $inputOther, mixed $savedVenue, mixed $savedOther): void
    {
        $impoundingType = m::mock(RefDataEntity::class);
        $impoundingType->shouldReceive('getId')->once()->andReturn(Entity::TYPE_HEARING);
        $impoundingLegislationTypes = m::mock(ArrayCollection::class);
        $applicationReceiptDate = '2015-12-25';
        $vrm = 'vrm';
        $hearingDate = '2017-12-25T15:45:00+0100';
        $presidingTc = m::mock(RefDataEntity::class);
        $outcome = m::mock(RefDataEntity::class);
        $outcomeSentDate = '2016-12-25';
        $notes = 'notes';

        $entity = $this->instantiate(Entity::class);
        $entity->update(
            $impoundingType,
            $impoundingLegislationTypes,
            $inputVenue,
            $inputOther,
            $applicationReceiptDate,
            $vrm,
            $hearingDate,
            $presidingTc,
            $outcome,
            $outcomeSentDate,
            $notes
        );

        $this->assertSame($impoundingType, $entity->getImpoundingType());
        $this->assertSame($impoundingLegislationTypes, $entity->getImpoundingLegislationTypes());
        $this->assertSame($savedVenue, $entity->getVenue());
        $this->assertSame($savedOther, $entity->getVenueOther());
        $this->assertSame($applicationReceiptDate, $entity->getApplicationReceiptDate()->format('Y-m-d'));
        $this->assertSame($vrm, $entity->getVrm());
        $this->assertSame($hearingDate, $entity->getHearingDate()->format(\DateTime::ISO8601));
        $this->assertSame($presidingTc, $entity->getPresidingTc());
        $this->assertSame($outcome, $entity->getOutcome());
        $this->assertSame($outcomeSentDate, $entity->getOutcomeSentDate()->format('Y-m-d'));
        $this->assertSame($notes, $entity->getNotes());
    }

    public static function updateHearingProvider(): array
    {
        $mockVenue = m::mock(Venue::class);

        return [
            [$mockVenue, 'other venue', $mockVenue, null],
            [Entity::VENUE_OTHER, 'other venue', null, 'other venue'],
            [null, 'other venue', null, null],
        ];
    }
}
