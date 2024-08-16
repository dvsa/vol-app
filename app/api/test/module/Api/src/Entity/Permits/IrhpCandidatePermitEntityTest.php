<?php

namespace Dvsa\OlcsTest\Api\Entity\Permits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as IrhpPermitRangeEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * IrhpCandidatePermit Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class IrhpCandidatePermitEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreateNew()
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);
        $requestedEmissionsCategory = new RefData(RefData::EMISSIONS_CATEGORY_EURO6_REF);
        $intensityOfUse = 1;
        $applicationScore = 2;

        $candidatePermit = Entity::createNew(
            $irhpPermitApplication,
            $requestedEmissionsCategory,
            $intensityOfUse,
            $applicationScore
        );

        $this->assertInstanceOf(Entity::class, $candidatePermit);
        $this->assertSame($irhpPermitApplication, $candidatePermit->getIrhpPermitApplication());
        $this->assertSame($requestedEmissionsCategory, $candidatePermit->getRequestedEmissionsCategory());
        $this->assertEquals($intensityOfUse, $candidatePermit->getIntensityOfUse());
        $this->assertEquals($applicationScore, $candidatePermit->getApplicationScore());
        $this->assertEquals(0, $candidatePermit->getSuccessful());
        $this->assertEquals(1, $candidatePermit->getWanted());
    }

    public function testCreateForApgg()
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);

        $emissionsCategory = m::mock(RefData::class);

        $irhpPermitRange = m::mock(IrhpPermitRangeEntity::class);
        $irhpPermitRange->shouldReceive('getEmissionsCategory')
            ->andReturn($emissionsCategory);

        $candidatePermit = Entity::createForApgg($irhpPermitApplication, $irhpPermitRange);

        $this->assertInstanceOf(Entity::class, $candidatePermit);
        $this->assertSame($irhpPermitApplication, $candidatePermit->getIrhpPermitApplication());
        $this->assertSame($irhpPermitRange, $candidatePermit->getIrhpPermitRange());
        $this->assertSame($emissionsCategory, $candidatePermit->getAssignedEmissionsCategory());
        $this->assertEquals(1, $candidatePermit->getSuccessful());
        $this->assertEquals(1, $candidatePermit->getWanted());
    }

    public function testPrepareForScoring()
    {
        $requestedEmissionsCategory = new RefData(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $candidatePermit = Entity::createNew(m::mock(IrhpPermitApplicationEntity::class), $requestedEmissionsCategory);
        $candidatePermit->setSuccessful(1);
        $candidatePermit->setAssignedEmissionsCategory(RefData::EMISSIONS_CATEGORY_EURO6_REF);
        $candidatePermit->setIrhpPermitRange(m::mock(IrhpPermitRangeEntity::class));

        $candidatePermit->prepareForScoring();
        $this->assertEquals(0, $candidatePermit->getSuccessful());
        $this->assertNull($candidatePermit->getIrhpPermitRange());
        $this->assertNull($candidatePermit->getAssignedEmissionsCategory());
    }

    public function testHasRandomizedScore()
    {
        $requestedEmissionsCategory = new RefData(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $candidatePermit = Entity::createNew(m::mock(IrhpPermitApplicationEntity::class), $requestedEmissionsCategory);

        $this->assertFalse($candidatePermit->hasRandomizedScore());
        $candidatePermit->setRandomizedScore(0.123);
        $this->assertTrue($candidatePermit->hasRandomizedScore());
    }

    public function testApplyRandomizedScore()
    {
        $deviationData = [
            'licenceData' => [
                'PD2737280' => [12, 15, 7]
            ],
            'meanDeviation' => 1.5
        ];

        $requestedEmissionsCategory = new RefData(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $candidatePermit = Entity::createNew(
            m::mock(IrhpPermitApplicationEntity::class),
            $requestedEmissionsCategory
        );

        $candidatePermit->applyRandomizedScore($deviationData, 'PD2737280');
        $this->assertNotNull($candidatePermit->getRandomFactor());
        $this->assertNotNull($candidatePermit->getRandomizedScore());
    }

    public function testApplyRange()
    {
        $requestedEmissionsCategory = new RefData(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $candidatePermit = Entity::createNew(
            m::mock(IrhpPermitApplicationEntity::class),
            $requestedEmissionsCategory
        );
        $candidatePermit->setAssignedEmissionsCategory(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $range = m::mock(IrhpPermitRangeEntity::class);
        $range->shouldReceive('getEmissionsCategory')
            ->andReturn(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $candidatePermit->applyRange($range);
        $this->assertSame($range, $candidatePermit->getIrhpPermitRange());
    }

    public function testApplyRangeExceptionOnEmissionsCategoryMismatch()
    {
        $requestedEmissionsCategory = new RefData(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(
            'A candidate permit can only be assigned to a range with a matching emissions category'
        );

        $candidatePermit = Entity::createNew(
            m::mock(IrhpPermitApplicationEntity::class),
            $requestedEmissionsCategory
        );
        $candidatePermit->setAssignedEmissionsCategory(RefData::EMISSIONS_CATEGORY_EURO5_REF);

        $range = m::mock(IrhpPermitRangeEntity::class);
        $range->shouldReceive('getEmissionsCategory')
            ->andReturn(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $candidatePermit->applyRange($range);
    }

    public function testMarkAsSuccessful()
    {
        $candidatePermit = m::mock(Entity::class)->makePartial();
        $candidatePermit->setSuccessful(0);

        $assignedEmissionsCategory = m::mock(RefData::class);
        $candidatePermit->markAsSuccessful($assignedEmissionsCategory);

        $this->assertEquals(1, $candidatePermit->getSuccessful());
        $this->assertSame($assignedEmissionsCategory, $candidatePermit->getAssignedEmissionsCategory());
    }

    public function testMarkAsSuccessfulExceptionOnAlreadySuccessful()
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(
            'This candidate permit has already been marked as successful'
        );

        $candidatePermit = m::mock(Entity::class)->makePartial();
        $candidatePermit->setSuccessful(1);

        $assignedEmissionsCategory = m::mock(RefData::class);
        $candidatePermit->markAsSuccessful($assignedEmissionsCategory);
    }

    /**
     * @dataProvider canDeleteProvider
     */
    public function testCanDelete($isUc, $expected)
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);

        $irhpPermitApplication
            ->shouldReceive('getIrhpApplication->isUnderConsideration')
            ->andReturn($isUc);

        $emissionsCategory = m::mock(RefData::class);

        $entity = Entity::createNew(
            $irhpPermitApplication,
            $emissionsCategory
        );
        $this->assertEquals($expected, $entity->canDelete());
    }

    public function canDeleteProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    public function testUpdateIrhpPermitRange()
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);

        $irhpPermitApplication
            ->shouldReceive('getIrhpApplication->isUnderConsideration')
            ->andReturn(true);

        $emissionsCategory = m::mock(RefData::class);

        $newRangeEmissionsCategory = m::mock(RefData::class);

        $newRange = m::mock(IrhpPermitRange::class);
        $newRange
            ->shouldReceive('getEmissionsCategory')
            ->andReturn($newRangeEmissionsCategory);

        $entity = Entity::createNew(
            $irhpPermitApplication,
            $emissionsCategory
        );

        $entity->updateIrhpPermitRange($newRange);
        $this->assertSame($newRange, $entity->getIrhpPermitRange());
        $this->assertSame($newRangeEmissionsCategory, $entity->getAssignedEmissionsCategory());
    }

    public function testUpdateIrhpPermitRangeWrongStatus()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);

        $irhpPermitApplication
            ->shouldReceive('getIrhpApplication->isUnderConsideration')
            ->andReturn(false);

        $emissionsCategory = m::mock(RefData::class);
        $newRange = m::mock(IrhpPermitRange::class);

        $entity = Entity::createNew(
            $irhpPermitApplication,
            $emissionsCategory
        );

        $entity->updateIrhpPermitRange($newRange);
    }

    /**
     * @dataProvider isApplicationUnderConsiderationProvider
     */
    public function testIsApplicationUnderConsideration($uc, $expected)
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);

        $irhpPermitApplication
            ->shouldReceive('getIrhpApplication->isUnderConsideration')
            ->andReturn($uc);

        $emissionsCategory = m::mock(RefData::class);

        $entity = Entity::createNew(
            $irhpPermitApplication,
            $emissionsCategory
        );

        $entity->isApplicationUnderConsideration();
        $this->assertSame($expected, $entity->isApplicationUnderConsideration());
    }

    public function isApplicationUnderConsiderationProvider()
    {
        return [
            [false, false],
            [true, true]
        ];
    }

    public function testReviveFromUnsuccessful()
    {
        $candidatePermit = m::mock(Entity::class)->makePartial();
        $candidatePermit->setSuccessful(1);
        $candidatePermit->setAssignedEmissionsCategory(m::mock(RefData::class));
        $candidatePermit->setIrhpPermitRange(m::mock(IrhpPermitRange::class));
        $candidatePermit->setRandomizedScore(1.5);
        $candidatePermit->setRandomFactor(0.25);

        $candidatePermit->reviveFromUnsuccessful();

        $this->assertEquals(0, $candidatePermit->getSuccessful());
        $this->assertNull($candidatePermit->getAssignedEmissionsCategory());
        $this->assertNull($candidatePermit->getIrhpPermitRange());
        $this->assertNull($candidatePermit->getRandomizedScore());
        $this->assertNull($candidatePermit->getRandomFactor());
    }

    public function testUpdateWanted()
    {
        $candidatePermit = m::mock(Entity::class)->makePartial();
        $candidatePermit->setWanted(1);

        $candidatePermit->updateWanted(1);
        $this->assertEquals(1, $candidatePermit->getWanted());

        $candidatePermit->updateWanted(0);
        $this->assertEquals(0, $candidatePermit->getWanted());
    }
}
