<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Permits;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPath;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Generic\Question;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Mockery as m;
use RuntimeException;

/**
 * IrhpPermitApplication Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class IrhpPermitApplicationEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @var Entity
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Entity();

        parent::setUp();
    }

    public function testCreateNew(): void
    {
        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);
        $licence = m::mock(Licence::class);

        $irhpPermitApplication = Entity::createNew(
            $irhpPermitWindow,
            $licence
        );

        $this->assertSame($irhpPermitWindow, $irhpPermitApplication->getIrhpPermitWindow());
        $this->assertSame($licence, $irhpPermitApplication->getLicence());
        $this->assertNull($irhpPermitApplication->getIrhpApplication());
    }

    public function testCreateNewForIrhpApplication(): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);

        $irhpPermitApplication = Entity::createNewForIrhpApplication(
            $irhpApplication,
            $irhpPermitWindow
        );

        $this->assertSame($irhpApplication, $irhpPermitApplication->getIrhpApplication());
        $this->assertSame($irhpPermitWindow, $irhpPermitApplication->getIrhpPermitWindow());
        $this->assertNull($irhpPermitApplication->getLicence());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpEmissionsCategoriesAndNull')]
    public function testGetPermitIntensityOfUse(mixed $emissionsCategoryId): void
    {
        $intensityOfUse = 0.75;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getPermitIntensityOfUse')
            ->with($emissionsCategoryId)
            ->once()
            ->andReturn($intensityOfUse);

        $irhpPermitApplication = m::mock(Entity::class)->makePartial();
        $irhpPermitApplication->setIrhpApplication($irhpApplication);

        $this->assertEquals(
            $intensityOfUse,
            $irhpPermitApplication->getPermitIntensityOfUse($emissionsCategoryId)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpEmissionsCategoriesAndNull')]
    public function testGetPermitApplicationScore(mixed $emissionsCategoryId): void
    {
        $applicationScore = 1.25;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getPermitApplicationScore')
            ->with($emissionsCategoryId)
            ->once()
            ->andReturn($applicationScore);

        $irhpPermitApplication = m::mock(Entity::class)->makePartial();
        $irhpPermitApplication->setIrhpApplication($irhpApplication);

        $this->assertEquals(
            $applicationScore,
            $irhpPermitApplication->getPermitApplicationScore($emissionsCategoryId)
        );
    }

    public static function dpEmissionsCategoriesAndNull(): array
    {
        return [
            [RefData::EMISSIONS_CATEGORY_EURO5_REF],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF],
            [null],
        ];
    }

    public function testGetCalculatedBundleValues(): void
    {
        $serializationParameters = [
            'licence' => [
                'organisation'
            ]
        ];

        $serializedIrhpApplication = [
            'prop1' => 'value1',
            'prop2' => 'value2',
        ];

        $permitsAwarded = 10;
        $euro5PermitsAwarded = 7;
        $euro6PermitsAwarded = 3;
        $euro5PermitsWanted = 5;
        $euro6PermitsWanted = 2;
        $validPermitsCount = 14;
        $permitsRequired = 3;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('serialize')
            ->with($serializationParameters)
            ->andReturn($serializedIrhpApplication);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpApplication($irhpApplication);

        $entity->shouldReceive('countPermitsAwarded')
            ->withNoArgs()
            ->andReturn($permitsAwarded);
        $entity->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5PermitsAwarded);
        $entity->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6PermitsAwarded);
        $entity->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF, true)
            ->andReturn($euro5PermitsWanted);
        $entity->shouldReceive('countPermitsAwarded')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF, true)
            ->andReturn($euro6PermitsWanted);
        $entity->shouldReceive('countValidPermits')
            ->withNoArgs()
            ->andReturn($validPermitsCount);
        $entity->shouldReceive('countPermitsRequired')
            ->withNoArgs()
            ->andReturn($permitsRequired);

        $this->assertSame(
            [
                'permitsAwarded' => $permitsAwarded,
                'euro5PermitsAwarded' => $euro5PermitsAwarded,
                'euro6PermitsAwarded' => $euro6PermitsAwarded,
                'euro5PermitsWanted' => $euro5PermitsWanted,
                'euro6PermitsWanted' => $euro6PermitsWanted,
                'validPermits' => $validPermitsCount,
                'permitsRequired' => $permitsRequired,
                'relatedApplication' => $serializedIrhpApplication,
            ],
            $entity->getCalculatedBundleValues()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCountValidPermits')]
    public function testCountValidPermits(mixed $statusId, mixed $count): void
    {
        $irhpPermitRange = m::mock(IrhpPermitRange::class);
        $irhpPermitApplication = m::mock(Entity::class);

        $irhpCandidate = m::mock(IrhpCandidatePermit::class);
        $irhpCandidate->shouldReceive('getIrhpPermitRange')
            ->once()
            ->withNoArgs()
            ->andReturn($irhpPermitRange);
        $irhpCandidate->shouldReceive('getIrhpPermitApplication')
            ->once()
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $issueDate = new \DateTime();
        $permitNumber = '00001';
        $status = new RefData($statusId);
        $expiryDate = new \DateTime();

        $irhpPermit = IrhpPermit::createNew(
            $irhpCandidate,
            $issueDate,
            $status,
            $permitNumber,
            $expiryDate
        );

        $collection = new ArrayCollection([$irhpPermit]);

        $this->sut->setIrhpPermits($collection);
        $this->assertEquals($count, $this->sut->countValidPermits());
    }

    public static function dpCountValidPermits(): array
    {
        return [
            [IrhpPermit::STATUS_PENDING, 1],
            [IrhpPermit::STATUS_AWAITING_PRINTING, 1],
            [IrhpPermit::STATUS_PRINTING, 1],
            [IrhpPermit::STATUS_PRINTED, 1],
            [IrhpPermit::STATUS_ERROR, 1],
            [IrhpPermit::STATUS_CEASED, 0],
            [IrhpPermit::STATUS_TERMINATED, 0]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCountPermitsAwardedForAllocationModeEmissionsCategories')]
    public function testCountPermitsAwardedForAllocationModeEmissionsCategories(mixed $emissionsCategoryId): void
    {
        $permitsAwarded = 14;

        $entity = m::mock(Entity::class)->makePartial();

        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow->shouldReceive('getIrhpPermitStock->getAllocationMode')
            ->withNoArgs()
            ->andReturn(IrhpPermitStock::ALLOCATION_MODE_EMISSIONS_CATEGORIES);
        $entity->setIrhpPermitWindow($irhpPermitWindow);

        $entity->shouldReceive('getTotalEmissionsCategoryPermitsRequired')
            ->with($emissionsCategoryId)
            ->andReturn($permitsAwarded);

        $this->assertEquals(
            $permitsAwarded,
            $entity->countPermitsAwarded($emissionsCategoryId)
        );
    }

    public static function dpTestCountPermitsAwardedForAllocationModeEmissionsCategories(): array
    {
        return [
            [null],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCountPermitsAwardedForAllocationModeCandidatePermits')]
    public function testCountPermitsAwardedForAllocationModeCandidatePermits(
        mixed $emissionsCategoryId,
        mixed $wantedOnly,
        mixed $expectedWantedOnly
    ): void {
        $successfulIrhpCandidatePermits = [
            m::mock(IrhpCandidatePermit::class),
            m::mock(IrhpCandidatePermit::class),
            m::mock(IrhpCandidatePermit::class),
        ];

        $expectedPermitsAwarded = 3;

        $entity = m::mock(Entity::class)->makePartial();

        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow->shouldReceive('getIrhpPermitStock->getAllocationMode')
            ->withNoArgs()
            ->andReturn(IrhpPermitStock::ALLOCATION_MODE_CANDIDATE_PERMITS);
        $entity->setIrhpPermitWindow($irhpPermitWindow);

        $entity->shouldReceive('getSuccessfulIrhpCandidatePermits')
            ->with($emissionsCategoryId, $expectedWantedOnly)
            ->andReturn($successfulIrhpCandidatePermits);

        $this->assertEquals(
            $expectedPermitsAwarded,
            $entity->countPermitsAwarded($emissionsCategoryId, $wantedOnly)
        );
    }

    public static function dpTestCountPermitsAwardedForAllocationModeCandidatePermits(): array
    {
        return [
            [null, null, false],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, null, false],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, null, false],
            [null, false, false],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, false, false],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, false, false],
            [null, true, true],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, true, true],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, true, true],
        ];
    }

    public function testGetSuccessfulIrhpCandidatePermitsNoEmissionsCategory(): void
    {
        $irhpCandidatePermits = $this->getPermitsAwardedMocks();
        foreach ($irhpCandidatePermits as $irhpCandidatePermit) {
            $this->sut->addIrhpCandidatePermits($irhpCandidatePermit);
        }

        $successfulIrhpCandidatePermits = $this->sut->getSuccessfulIrhpCandidatePermits();

        $this->assertTrue($successfulIrhpCandidatePermits->contains($irhpCandidatePermits[0]));
        $this->assertTrue($successfulIrhpCandidatePermits->contains($irhpCandidatePermits[2]));
        $this->assertTrue($successfulIrhpCandidatePermits->contains($irhpCandidatePermits[4]));
        $this->assertEquals(3, $successfulIrhpCandidatePermits->count());
    }

    public function testGetSuccessfulIrhpCandidatePermitsNoEmissionsCategoryWantedOnly(): void
    {
        $irhpCandidatePermits = $this->getPermitsAwardedMocks();
        foreach ($irhpCandidatePermits as $irhpCandidatePermit) {
            $this->sut->addIrhpCandidatePermits($irhpCandidatePermit);
        }

        $successfulIrhpCandidatePermits = $this->sut->getSuccessfulIrhpCandidatePermits(null, true);

        $this->assertTrue($successfulIrhpCandidatePermits->contains($irhpCandidatePermits[2]));
        $this->assertEquals(1, $successfulIrhpCandidatePermits->count());
    }

    public function testGetSuccessfulIrhpCandidatePermitsEuro5EmissionsCategory(): void
    {
        $irhpCandidatePermits = $this->getPermitsAwardedMocks();
        foreach ($irhpCandidatePermits as $irhpCandidatePermit) {
            $this->sut->addIrhpCandidatePermits($irhpCandidatePermit);
        }

        $successfulIrhpCandidatePermits = $this->sut->getSuccessfulIrhpCandidatePermits(
            RefData::EMISSIONS_CATEGORY_EURO5_REF
        );

        $this->assertTrue($successfulIrhpCandidatePermits->contains($irhpCandidatePermits[0]));
        $this->assertTrue($successfulIrhpCandidatePermits->contains($irhpCandidatePermits[2]));
        $this->assertEquals(2, $successfulIrhpCandidatePermits->count());
    }

    public function testGetSuccessfulIrhpCandidatePermitsEuro5EmissionsCategoryWantedOnly(): void
    {
        $irhpCandidatePermits = $this->getPermitsAwardedMocks();
        foreach ($irhpCandidatePermits as $irhpCandidatePermit) {
            $this->sut->addIrhpCandidatePermits($irhpCandidatePermit);
        }

        $successfulIrhpCandidatePermits = $this->sut->getSuccessfulIrhpCandidatePermits(
            RefData::EMISSIONS_CATEGORY_EURO5_REF,
            true
        );

        $this->assertTrue($successfulIrhpCandidatePermits->contains($irhpCandidatePermits[2]));
        $this->assertEquals(1, $successfulIrhpCandidatePermits->count());
    }

    public function testGetSuccessfulIrhpCandidatePermitsEuro6EmissionsCategory(): void
    {
        $irhpCandidatePermits = $this->getPermitsAwardedMocks();
        foreach ($irhpCandidatePermits as $irhpCandidatePermit) {
            $this->sut->addIrhpCandidatePermits($irhpCandidatePermit);
        }

        $successfulIrhpCandidatePermits = $this->sut->getSuccessfulIrhpCandidatePermits(
            RefData::EMISSIONS_CATEGORY_EURO6_REF
        );

        $this->assertTrue($successfulIrhpCandidatePermits->contains($irhpCandidatePermits[4]));
        $this->assertEquals(1, $successfulIrhpCandidatePermits->count());
    }

    public function testGetSuccessfulIrhpCandidatePermitsEuro6EmissionsCategoryWantedOnly(): void
    {
        $irhpCandidatePermits = $this->getPermitsAwardedMocks();
        foreach ($irhpCandidatePermits as $irhpCandidatePermit) {
            $this->sut->addIrhpCandidatePermits($irhpCandidatePermit);
        }

        $successfulIrhpCandidatePermits = $this->sut->getSuccessfulIrhpCandidatePermits(
            RefData::EMISSIONS_CATEGORY_EURO6_REF,
            true
        );

        $this->assertEquals(0, $successfulIrhpCandidatePermits->count());
    }

    private function getPermitsAwardedMocks(): array
    {
        $euro5EmissionsCategory = new RefData(RefData::EMISSIONS_CATEGORY_EURO5_REF);
        $euro6EmissionsCategory = new RefData(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $candidatePermit1 = m::mock(IrhpCandidatePermit::class);
        $candidatePermit1->shouldReceive('getSuccessful')
            ->andReturn(true);
        $candidatePermit1->shouldReceive('getWanted')
            ->andReturn(false);
        $candidatePermit1->shouldReceive('getAssignedEmissionsCategory')
            ->andReturn($euro5EmissionsCategory);

        $candidatePermit2 = m::mock(IrhpCandidatePermit::class);
        $candidatePermit2->shouldReceive('getSuccessful')
            ->andReturn(false);
        $candidatePermit2->shouldReceive('getWanted')
            ->andReturn(true);
        $candidatePermit2->shouldReceive('getAssignedEmissionsCategory')
            ->andReturn($euro5EmissionsCategory);

        $candidatePermit3 = m::mock(IrhpCandidatePermit::class);
        $candidatePermit3->shouldReceive('getSuccessful')
            ->andReturn(true);
        $candidatePermit3->shouldReceive('getWanted')
            ->andReturn(true);
        $candidatePermit3->shouldReceive('getAssignedEmissionsCategory')
            ->andReturn($euro5EmissionsCategory);

        $candidatePermit4 = m::mock(IrhpCandidatePermit::class);
        $candidatePermit4->shouldReceive('getSuccessful')
            ->andReturn(false);
        $candidatePermit4->shouldReceive('getWanted')
            ->andReturn(true);
        $candidatePermit4->shouldReceive('getAssignedEmissionsCategory')
            ->andReturn($euro6EmissionsCategory);

        $candidatePermit5 = m::mock(IrhpCandidatePermit::class);
        $candidatePermit5->shouldReceive('getSuccessful')
            ->andReturn(true);
        $candidatePermit5->shouldReceive('getWanted')
            ->andReturn(false);
        $candidatePermit5->shouldReceive('getAssignedEmissionsCategory')
            ->andReturn($euro6EmissionsCategory);

        return [
            $candidatePermit1,
            $candidatePermit2,
            $candidatePermit3,
            $candidatePermit4,
            $candidatePermit5,
        ];
    }

    public function testUpdatePermitsRequired(): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('canBeUpdated')
            ->withNoArgs()
            ->andReturn(true);

        $irhpPermitApplication = Entity::createNew(
            m::mock(IrhpPermitWindow::class),
            m::mock(Licence::class),
            $irhpApplication
        );

        $irhpPermitApplication->setPermitsRequired(44);
        $irhpPermitApplication->setIrhpApplication($irhpApplication);

        $irhpPermitApplication->updatePermitsRequired(4);
        $this->assertEquals(4, $irhpPermitApplication->getPermitsRequired());
    }

    public function testUpdatePermitsRequiredCannotBeUpdated(): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('canBeUpdated')
            ->withNoArgs()
            ->andReturn(false);

        $irhpPermitApplication = Entity::createNew(
            m::mock(IrhpPermitWindow::class),
            m::mock(Licence::class),
            $irhpApplication
        );

        $irhpPermitApplication->setPermitsRequired(44);
        $irhpPermitApplication->setIrhpApplication($irhpApplication);

        $irhpPermitApplication->updatePermitsRequired(4);
        $this->assertEquals(44, $irhpPermitApplication->getPermitsRequired());
    }

    public function testHasPermitsRequired(): void
    {
        $this->assertFalse($this->sut->hasPermitsRequired());

        $this->sut->setPermitsRequired(0);
        $this->assertTrue($this->sut->hasPermitsRequired());
    }

    public function testGetIssueFeeProductReferenceBilateral(): void
    {
        $irhpPermitApplication = m::mock(Entity::class)->makePartial();
        $irhpPermitApplication->shouldReceive('getIrhpApplication->getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);

        $this->assertEquals(
            FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_PRODUCT_REF,
            $irhpPermitApplication->getIssueFeeProductReference()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetIssueFeeProductReferenceMultilateral')]
    public function testGetIssueFeeProductReferenceMultilateral(mixed $dateTimeInput, mixed $expectedFormattedDateTime): void
    {
        $tieredProductReference = 'TIERED_PRODUCT_REFERENCE';

        $stockValidFrom = m::mock(DateTime::class);
        $stockValidTo = m::mock(DateTime::class);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getValidFrom')
            ->with(true)
            ->andReturn($stockValidFrom);
        $irhpPermitStock->shouldReceive('getValidTo')
            ->with(true)
            ->andReturn($stockValidTo);

        $irhpPermitApplication = m::mock(Entity::class)->makePartial();
        $irhpPermitApplication->shouldReceive('getIrhpApplication->getIrhpPermitType->getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->andReturn($irhpPermitStock);
        $irhpPermitApplication->shouldReceive('genericGetProdRefForTier')
            ->andReturnUsing(
                function (
                    $validityStart,
                    $validityEnd,
                    $now,
                    $tieredProductReferenceArray
                ) use (
                    $tieredProductReference,
                    $stockValidFrom,
                    $stockValidTo,
                    $expectedFormattedDateTime
                ) {
                    $this->assertSame($validityStart, $stockValidFrom);
                    $this->assertSame($validityEnd, $stockValidTo);
                    $this->assertEquals(
                        $now->format('Y-m-d'),
                        $expectedFormattedDateTime
                    );
                    $this->assertEquals(
                        Entity::MULTILATERAL_ISSUE_FEE_PRODUCT_REFERENCE_MONTH_ARRAY,
                        $tieredProductReferenceArray
                    );

                    return $tieredProductReference;
                }
            );

        $this->assertEquals(
            $tieredProductReference,
            $irhpPermitApplication->getIssueFeeProductReference($dateTimeInput)
        );
    }

    public static function dpGetIssueFeeProductReferenceMultilateral(): array
    {
        $dateTime = new DateTime('2029-04-21 13:40:15');

        return [
            [null, (new DateTime())->format('Y-m-d')],
            [$dateTime, $dateTime->format('Y-m-d')]
        ];
    }

    public function testClearPermitsRequired(): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setPermitsRequired(5);
        $entity->clearPermitsRequired();

        $this->assertNull($entity->getPermitsRequired());
    }

    public function testUpdateEmissionsCategoryPermitsRequired(): void
    {
        $euro5Required = 7;
        $euro6Required = 3;

        $entity = m::mock(Entity::class)->makePartial();
        $entity->updateEmissionsCategoryPermitsRequired($euro5Required, $euro6Required);

        $this->assertEquals($euro5Required, $entity->getRequiredEuro5());
        $this->assertEquals($euro6Required, $entity->getRequiredEuro6());
    }

    public function testClearEmissionsCategoryPermitsRequired(): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->updateEmissionsCategoryPermitsRequired(7, 5);
        $entity->clearEmissionsCategoryPermitsRequired();

        $this->assertNull($entity->getRequiredEuro5());
        $this->assertNull($entity->getRequiredEuro6());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetTotalEmissionsCategoryPermitsRequired')]
    public function testGetTotalEmissionsCategoryPermitsRequired(mixed $requiredEuro5, mixed $requiredEuro6, mixed $expected): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setRequiredEuro5($requiredEuro5);
        $entity->setRequiredEuro6($requiredEuro6);

        $this->assertEquals(
            $expected,
            $entity->getTotalEmissionsCategoryPermitsRequired()
        );
    }

    public static function dpTestGetTotalEmissionsCategoryPermitsRequired(): array
    {
        return [
            [null, null, 0],
            [null, 4, 4],
            [4, null, 4],
            [4, 5, 9]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetRequiredPermitsByEmissionsCategory')]
    public function testGetRequiredPermitsByEmissionsCategory(mixed $emissionsCategoryId, mixed $expectedRequiredPermits): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setRequiredEuro5(10);
        $entity->setRequiredEuro6(15);

        $this->assertEquals(
            $expectedRequiredPermits,
            $entity->getRequiredPermitsByEmissionsCategory($emissionsCategoryId)
        );
    }

    public static function dpTestGetRequiredPermitsByEmissionsCategory(): array
    {
        return [
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, 10],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, 15],
        ];
    }

    public function testGetRequiredPermitsByEmissionsCategoryException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported emissions category for getRequiredPermitsByEmissionsCategory');

        $entity = m::mock(Entity::class)->makePartial();
        $entity->getRequiredPermitsByEmissionsCategory(RefData::EMISSIONS_CATEGORY_NA_REF);
    }

    public function testGetRangesWithCandidatePermitCounts(): void
    {
        $irhpPermitRange1Id = 43;
        $irhpPermitRange1 = m::mock(IrhpPermitRange::class);
        $irhpPermitRange1->shouldReceive('getId')
            ->andReturn($irhpPermitRange1Id);

        $irhpPermitRange2Id = 71;
        $irhpPermitRange2 = m::mock(IrhpPermitRange::class);
        $irhpPermitRange2->shouldReceive('getId')
            ->andReturn($irhpPermitRange2Id);

        $irhpPermitRange3Id = 103;
        $irhpPermitRange3 = m::mock(IrhpPermitRange::class);
        $irhpPermitRange3->shouldReceive('getId')
            ->andReturn($irhpPermitRange3Id);

        $irhpCandidatePermit1 = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit1->shouldReceive('getIrhpPermitRange')
            ->andReturn($irhpPermitRange1);

        $irhpCandidatePermit2 = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit2->shouldReceive('getIrhpPermitRange')
            ->andReturn($irhpPermitRange3);

        $irhpCandidatePermit3 = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit3->shouldReceive('getIrhpPermitRange')
            ->andReturn($irhpPermitRange2);

        $irhpCandidatePermit4 = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit4->shouldReceive('getIrhpPermitRange')
            ->andReturn($irhpPermitRange1);

        $irhpCandidatePermit5 = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit5->shouldReceive('getIrhpPermitRange')
            ->andReturn($irhpPermitRange2);

        $irhpCandidatePermit6 = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit6->shouldReceive('getIrhpPermitRange')
            ->andReturn($irhpPermitRange1);

        $irhpCandidatePermits = [
            $irhpCandidatePermit1,
            $irhpCandidatePermit2,
            $irhpCandidatePermit3,
            $irhpCandidatePermit4,
            $irhpCandidatePermit5,
            $irhpCandidatePermit6
        ];

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpCandidatePermits(new ArrayCollection($irhpCandidatePermits));

        $expectedResponse = [
            $irhpPermitRange1Id => [
                Entity::REQUESTED_PERMITS_KEY => 3,
                Entity::RANGE_ENTITY_KEY => $irhpPermitRange1
            ],
            $irhpPermitRange3Id => [
                Entity::REQUESTED_PERMITS_KEY => 1,
                Entity::RANGE_ENTITY_KEY => $irhpPermitRange3
            ],
            $irhpPermitRange2Id => [
                Entity::REQUESTED_PERMITS_KEY => 2,
                Entity::RANGE_ENTITY_KEY => $irhpPermitRange2
            ],
        ];

        $this->assertEquals(
            $expectedResponse,
            $entity->getRangesWithCandidatePermitCounts()
        );
    }

    public function testGenerateIssueDateForEcmtRemoval(): void
    {
        $issueDateAsString = '2020-05-15';

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIrhpPermitType->isEcmtRemoval')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpApplication->shouldReceive('getAnswerValueByQuestionId')
            ->with(Question::QUESTION_ID_REMOVAL_PERMIT_START_DATE)
            ->andReturn($issueDateAsString);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpApplication($irhpApplication);

        $this->assertEquals(
            $issueDateAsString,
            $entity->generateIssueDate()->format('Y-m-d')
        );
    }

    public function testGenerateIssueDateForEcmtShortTerm(): void
    {
        $issueDateAsString = '2020-05-15';

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIrhpPermitType->isEcmtRemoval')
            ->withNoArgs()
            ->andReturnFalse();
        $irhpApplication->shouldReceive('getIrhpPermitType->isEcmtShortTerm')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpApplication->shouldReceive('getAnswerValueByQuestionId')
            ->with(Question::QUESTION_ID_SHORT_TERM_EARLIEST_PERMIT_START_DATE)
            ->andReturn($issueDateAsString);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpApplication($irhpApplication);

        $this->assertEquals(
            $issueDateAsString,
            $entity->generateIssueDate()->format('Y-m-d')
        );
    }

    public function testGenerateIssueDateForOther(): void
    {
        $currentDateAsString = (new DateTime())->format('Y-m-d');

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIrhpPermitType->isEcmtRemoval')
            ->withNoArgs()
            ->andReturnFalse();

        $irhpApplication->shouldReceive('getIrhpPermitType->isEcmtShortTerm')
            ->withNoArgs()
            ->andReturnFalse();

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpApplication($irhpApplication);

        $this->assertEquals(
            $currentDateAsString,
            $entity->generateIssueDate()->format('Y-m-d')
        );
    }

    public function testGenerateExpiryDate(): void
    {
        $issueDate = m::mock(DateTime::class);
        $expiryDate = m::mock(DateTime::class);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIrhpPermitType->generateExpiryDate')
            ->with($issueDate)
            ->once()
            ->andReturn($expiryDate);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('generateIssueDate')
            ->withNoArgs()
            ->andReturn($issueDate);

        $entity->setIrhpApplication($irhpApplication);

        $this->assertSame(
            $expiryDate,
            $entity->generateExpiryDate()
        );
    }

    public function testGetAnswerValueByQuestionId(): void
    {
        $questionId = 47;
        $answerValue = 'answer value';

        $entity = m::mock(Entity::class)->makePartial();

        $activeApplicationPath = m::mock(ApplicationPath::class);
        $activeApplicationPath->shouldReceive('getAnswerValueByQuestionId')
            ->with($questionId, $entity)
            ->andReturn($answerValue);

        $entity->shouldReceive('getActiveApplicationPath')
            ->withNoArgs()
            ->andReturn($activeApplicationPath);

        $this->assertEquals(
            $answerValue,
            $entity->getAnswerValueByQuestionId($questionId)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsNotYetSubmitted')]
    public function testIsNotYetSubmitted(mixed $isNotYetSubmitted): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isNotYetSubmitted')
            ->withNoArgs()
            ->andReturn($isNotYetSubmitted);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpApplication($irhpApplication);

        $this->assertEquals(
            $isNotYetSubmitted,
            $entity->isNotYetSubmitted()
        );
    }

    public static function dpTestIsNotYetSubmitted(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testGetApplicationPathLockedOn(): void
    {
        $applicationPathLockedOn = m::mock(DateTime::class);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getApplicationPathLockedOn')
            ->withNoArgs()
            ->andReturn($applicationPathLockedOn);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpApplication($irhpApplication);

        $this->assertSame(
            $applicationPathLockedOn,
            $entity->getApplicationPathLockedOn()
        );
    }

    public function testGetActiveApplicationPath(): void
    {
        $applicationPathLockedOn = m::mock(DateTime::class);
        $applicationPath = m::mock(ApplicationPath::class);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getApplicationPathLockedOn')
            ->withNoArgs()
            ->andReturn($applicationPathLockedOn);
        $entity->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getApplicationPathGroup->getActiveApplicationPath')
            ->with($applicationPathLockedOn)
            ->andReturn($applicationPath);

        $this->assertSame(
            $applicationPath,
            $entity->getActiveApplicationPath()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetAnswerWithStandardAnswer')]
    public function testGetAnswerWithStandardAnswer(mixed $isCustom, mixed $formControlType): void
    {
        $answerValue = 'answer value';

        $applicationPathLockedOn = m::mock(DateTime::class);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getApplicationPathLockedOn')
            ->withNoArgs()
            ->andReturn($applicationPathLockedOn);

        $question = m::mock(Question::class);
        $question->shouldReceive('getFormControlType')
            ->withNoArgs()
            ->andReturn($formControlType);
        $question->shouldReceive('isCustom')
            ->withNoArgs()
            ->andReturn($isCustom);
        $question->shouldReceive('getStandardAnswer')
            ->with($entity, $applicationPathLockedOn)
            ->andReturn($answerValue);

        $applicationStep = m::mock(ApplicationStep::class);
        $applicationStep->shouldReceive('getQuestion')
            ->withNoArgs()
            ->andReturn($question);

        $this->assertEquals(
            $answerValue,
            $entity->getAnswer($applicationStep)
        );
    }

    public function getAnswerUnsupportedCustomType(): void
    {
        $this->expectExpection(RuntimeException::class);
        $this->expectExceptionMessage('Unable to retrieve answer status for form control type FORM_CONTROL_OTHER');

        $applicationPathLockedOn = m::mock(DateTime::class);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getApplicationPathLockedOn')
            ->withNoArgs()
            ->andReturn($applicationPathLockedOn);

        $question = m::mock(Question::class);
        $question->shouldReceive('getFormControlType')
            ->withNoArgs()
            ->andReturn('FORM_CONTROL_OTHER');
        $question->shouldReceive('isCustom')
            ->withNoArgs()
            ->andReturnTrue;

        $applicationStep = m::mock(ApplicationStep::class);
        $applicationStep->shouldReceive('getQuestion')
            ->withNoArgs()
            ->andReturn($question);

        $entity->getAnswer($applicationStep);
    }

    public static function dpGetAnswerWithStandardAnswer(): array
    {
        return [
            [false, Question::FORM_CONTROL_BILATERAL_CABOTAGE_ONLY],
            [false, Question::FORM_CONTROL_BILATERAL_CABOTAGE_STD_AND_CABOTAGE],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpResetCheckAnswers')]
    public function testResetCheckAnswers(mixed $canBeUpdated, mixed $expectedValue): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('canBeUpdated')
            ->withNoArgs()
            ->andReturn($canBeUpdated);

        $this->sut->setIrhpApplication($irhpApplication);
        $this->sut->setCheckedAnswers(true);
        $this->sut->resetCheckAnswers();

        $this->assertEquals(
            $expectedValue,
            $this->sut->getCheckedAnswers()
        );
    }

    public static function dpResetCheckAnswers(): array
    {
        return [
            [true, false],
            [false, true],
        ];
    }

    public function testGetCamelCaseEntityName(): void
    {
        $this->assertEquals(
            'irhpPermitApplication',
            $this->sut->getCamelCaseEntityName()
        );
    }

    public function testOnSubmitApplicationStep(): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('resetCheckAnswers')
            ->once()
            ->withNoArgs();

        $entity->onSubmitApplicationStep();
    }

    public function testGetAdditionalQaViewData(): void
    {
        $entity = m::mock(Entity::class)->makePartial();

        $countryCode = 'DE';
        $countryName = 'Germany';
        $previousStepSlug = 'previous-step-slug';

        $expected = [
            'previousStepSlug' => $previousStepSlug,
            'countryName' => $countryName,
            'countryCode' => $countryCode,
        ];

        $country = m::mock(Country::class);
        $country->shouldReceive('getCountryDesc')
            ->withNoArgs()
            ->andReturn($countryName);
        $country->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($countryCode);

        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow->shouldReceive('getIrhpPermitStock->getCountry')
            ->withNoArgs()
            ->andReturn($country);

        $entity->setIrhpPermitWindow($irhpPermitWindow);

        $applicationStep = m::mock(ApplicationStep::class);
        $applicationStep->shouldReceive('getPreviousStepSlug')
            ->withNoArgs()
            ->andReturn($previousStepSlug);

        $this->assertEquals(
            $expected,
            $entity->getAdditionalQaViewData($applicationStep)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsApplicationPathEnabled')]
    public function testIsApplicationPathEnabled(mixed $isIrhpPermitApplicationPathEnabled): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIrhpPermitType->isIrhpPermitApplicationPathEnabled')
            ->withNoArgs()
            ->andReturn($isIrhpPermitApplicationPathEnabled);

        $this->sut->setIrhpApplication($irhpApplication);

        $this->assertEquals(
            $isIrhpPermitApplicationPathEnabled,
            $this->sut->isApplicationPathEnabled()
        );
    }

    public static function dpIsApplicationPathEnabled(): array
    {
        return [
            [true],
            [false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetBilateralCabotageSelection')]
    public function testGetBilateralCabotageSelection(mixed $cabotageOnlyAnswer, mixed $standardAndCabotageAnswer, mixed $expectedAnswer): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $entity = m::mock(Entity::class)->makePartial();

        $entity->setIrhpApplication($irhpApplication);
        $entity->shouldReceive('getAnswerValueByQuestionId')
            ->with(Question::QUESTION_ID_BILATERAL_CABOTAGE_ONLY)
            ->andReturn($cabotageOnlyAnswer);
        $entity->shouldReceive('getAnswerValueByQuestionId')
            ->with(Question::QUESTION_ID_BILATERAL_STANDARD_AND_CABOTAGE)
            ->andReturn($standardAndCabotageAnswer);

        $this->assertEquals(
            $expectedAnswer,
            $entity->getBilateralCabotageSelection()
        );
    }

    public static function dpGetBilateralCabotageSelection(): array
    {
        return [
            ['cabotage_only_answer', null, 'cabotage_only_answer'],
            [null, 'standard_and_cabotage_answer', 'standard_and_cabotage_answer'],
            [null, null, Answer::BILATERAL_STANDARD_ONLY],
        ];
    }

    public function testGetBilateralCabotageSelectionNotBilateral(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('getBilateralCabotageSelection is applicable only to bilateral applications');

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->setIrhpApplication($irhpApplication);

        $this->sut->getBilateralCabotageSelection();
    }

    public function testGetBilateralPermitUsageSelection(): void
    {
        $permitUsageAnswer = 'permit_usage_answer';

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $entity = m::mock(Entity::class)->makePartial();

        $entity->setIrhpApplication($irhpApplication);
        $entity->shouldReceive('getAnswerValueByQuestionId')
            ->with(Question::QUESTION_ID_BILATERAL_PERMIT_USAGE)
            ->andReturn($permitUsageAnswer);

        $this->assertEquals(
            $permitUsageAnswer,
            $entity->getBilateralPermitUsageSelection()
        );
    }

    public function testGetBilateralPermitUsageSelectionNotBilateral(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('getBilateralPermitUsageSelection is applicable only to bilateral applications');

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->setIrhpApplication($irhpApplication);

        $this->sut->getBilateralPermitUsageSelection();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpUpdateAndGetBilateralRequired')]
    public function testUpdateAndGetBilateralRequired(mixed $standardRequired, mixed $cabotageRequired, mixed $moroccoRequired): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpApplication->shouldReceive('canBeUpdated')
            ->withNoArgs()
            ->andReturnTrue();

        $this->sut->setIrhpApplication($irhpApplication);

        $required = [
            Entity::BILATERAL_STANDARD_REQUIRED => $standardRequired,
            Entity::BILATERAL_CABOTAGE_REQUIRED => $cabotageRequired,
            Entity::BILATERAL_MOROCCO_REQUIRED => $moroccoRequired,
        ];

        $this->sut->updateBilateralRequired($required);
        $this->assertEquals($required, $this->sut->getBilateralRequired());
    }

    public static function dpUpdateAndGetBilateralRequired(): array
    {
        return [
            ['43', '57', null],
            ['22', null, null],
            [null, '31', null],
            [null, null, '10'],
        ];
    }

    public function testUpdateBilateralRequiredBadKeys(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected or missing array keys passed to updateBilateralRequired');

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpApplication->shouldReceive('canBeUpdated')
            ->withNoArgs()
            ->andReturnTrue();

        $this->sut->setIrhpApplication($irhpApplication);

        $required = [
            'foo' => 'bar',
            'test' => 'key',
        ];

        $this->sut->updateBilateralRequired($required);
    }

    public function testUpdateBilateralRequiredNotBilateral(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('updateBilateralRequired is applicable only to bilateral applications');

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->setIrhpApplication($irhpApplication);

        $this->sut->updateBilateralRequired([]);
    }

    public function testupdateBilateralRequiredCannotBeUpdated(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('updateBilateralRequired called when application in unexpected state');

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpApplication->shouldReceive('canBeUpdated')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->setIrhpApplication($irhpApplication);

        $this->sut->updateBilateralRequired([]);
    }

    public function testClearBilateralRequired(): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpApplication->shouldReceive('canBeUpdated')
            ->withNoArgs()
            ->andReturnTrue();

        $this->sut->setIrhpApplication($irhpApplication);

        $required = [
            Entity::BILATERAL_STANDARD_REQUIRED => '7',
            Entity::BILATERAL_CABOTAGE_REQUIRED => '8',
            Entity::BILATERAL_MOROCCO_REQUIRED => '9',
        ];

        $this->sut->updateBilateralRequired($required);

        $expected = [
            Entity::BILATERAL_STANDARD_REQUIRED => null,
            Entity::BILATERAL_CABOTAGE_REQUIRED => null,
            Entity::BILATERAL_MOROCCO_REQUIRED => null,
        ];

        $this->sut->clearBilateralRequired();
        $this->assertEquals($expected, $this->sut->getBilateralRequired());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetFilteredBilateralRequired')]
    public function testGetFilteredBilateralRequired(mixed $bilateralRequired, mixed $expected): void
    {
        $entity = m::mock(Entity::class)->makePartial();

        $entity->shouldReceive('getBilateralRequired')
            ->withNoArgs()
            ->andReturn($bilateralRequired);

        $this->assertEquals(
            $expected,
            $entity->getFilteredBilateralRequired()
        );
    }

    public static function dpGetFilteredBilateralRequired(): array
    {
        return [
            [
                [
                    Entity::BILATERAL_STANDARD_REQUIRED => null,
                    Entity::BILATERAL_CABOTAGE_REQUIRED => null,
                    Entity::BILATERAL_MOROCCO_REQUIRED => null,
                ],
                []
            ],
            [
                [
                    Entity::BILATERAL_STANDARD_REQUIRED => 5,
                    Entity::BILATERAL_CABOTAGE_REQUIRED => 9,
                    Entity::BILATERAL_MOROCCO_REQUIRED => null,
                ],
                [
                    Entity::BILATERAL_STANDARD_REQUIRED => 5,
                    Entity::BILATERAL_CABOTAGE_REQUIRED => 9,
                ]
            ],
            [
                [
                    Entity::BILATERAL_STANDARD_REQUIRED => null,
                    Entity::BILATERAL_CABOTAGE_REQUIRED => 3,
                    Entity::BILATERAL_MOROCCO_REQUIRED => null,
                ],
                [
                    Entity::BILATERAL_CABOTAGE_REQUIRED => 3,
                ]
            ],
            [
                [
                    Entity::BILATERAL_STANDARD_REQUIRED => 12,
                    Entity::BILATERAL_CABOTAGE_REQUIRED => null,
                    Entity::BILATERAL_MOROCCO_REQUIRED => null,
                ],
                [
                    Entity::BILATERAL_STANDARD_REQUIRED => 12,
                ]
            ],
            [
                [
                    Entity::BILATERAL_STANDARD_REQUIRED => null,
                    Entity::BILATERAL_CABOTAGE_REQUIRED => null,
                    Entity::BILATERAL_MOROCCO_REQUIRED => 14,
                ],
                [
                    Entity::BILATERAL_MOROCCO_REQUIRED => 14,
                ]
            ],
        ];
    }

    public function testGetBilateralFeeProductRefsAndQuantitiesStandardOnly(): void
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);

        $bilateralRequired = [
            Entity::BILATERAL_STANDARD_REQUIRED => 6,
            Entity::BILATERAL_CABOTAGE_REQUIRED => null,
            Entity::BILATERAL_MOROCCO_REQUIRED => null
        ];

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getBilateralRequired')
            ->withNoArgs()
            ->andReturn($bilateralRequired);
        $entity->shouldReceive('getBilateralFeeProductReferences')
            ->with($irhpPermitStock, Entity::BILATERAL_STANDARD_REQUIRED)
            ->andReturn(['PRODUCT_REF_ABC', 'PRODUCT_REF_DEF']);

        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow->shouldReceive('getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $entity->setIrhpApplication($irhpApplication);
        $entity->setIrhpPermitWindow($irhpPermitWindow);

        $expectedProductRefsAndQuantities = [
            'PRODUCT_REF_ABC' => 6,
            'PRODUCT_REF_DEF' => 6,
        ];

        $this->assertEquals(
            $expectedProductRefsAndQuantities,
            $entity->getBilateralFeeProductRefsAndQuantities()
        );
    }

    public function testGetBilateralFeeProductRefsAndQuantitiesCabotageOnly(): void
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);

        $bilateralRequired = [
            Entity::BILATERAL_STANDARD_REQUIRED => null,
            Entity::BILATERAL_CABOTAGE_REQUIRED => 8,
            Entity::BILATERAL_MOROCCO_REQUIRED => null
        ];

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getBilateralRequired')
            ->withNoArgs()
            ->andReturn($bilateralRequired);
        $entity->shouldReceive('getBilateralFeeProductReferences')
            ->with($irhpPermitStock, Entity::BILATERAL_CABOTAGE_REQUIRED)
            ->andReturn(['PRODUCT_REF_ABC', 'PRODUCT_REF_DEF']);

        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow->shouldReceive('getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $entity->setIrhpApplication($irhpApplication);
        $entity->setIrhpPermitWindow($irhpPermitWindow);

        $expectedProductRefsAndQuantities = [
            'PRODUCT_REF_ABC' => 8,
            'PRODUCT_REF_DEF' => 8,
        ];

        $this->assertEquals(
            $expectedProductRefsAndQuantities,
            $entity->getBilateralFeeProductRefsAndQuantities()
        );
    }

    public function testGetBilateralFeeProductRefsAndQuantitiesStandardAndCabotage(): void
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);

        $bilateralRequired = [
            Entity::BILATERAL_STANDARD_REQUIRED => 7,
            Entity::BILATERAL_CABOTAGE_REQUIRED => 5,
            Entity::BILATERAL_MOROCCO_REQUIRED => null
        ];

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getBilateralRequired')
            ->withNoArgs()
            ->andReturn($bilateralRequired);
        $entity->shouldReceive('getBilateralFeeProductReferences')
            ->with($irhpPermitStock, Entity::BILATERAL_STANDARD_REQUIRED)
            ->andReturn(['PRODUCT_REF_ABC', 'PRODUCT_REF_DEF']);
        $entity->shouldReceive('getBilateralFeeProductReferences')
            ->with($irhpPermitStock, Entity::BILATERAL_CABOTAGE_REQUIRED)
            ->andReturn(['PRODUCT_REF_DEF', 'PRODUCT_REF_GHI']);

        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow->shouldReceive('getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $entity->setIrhpApplication($irhpApplication);
        $entity->setIrhpPermitWindow($irhpPermitWindow);

        $expectedProductRefsAndQuantities = [
            'PRODUCT_REF_ABC' => 7,
            'PRODUCT_REF_DEF' => 12,
            'PRODUCT_REF_GHI' => 5
        ];

        $this->assertEquals(
            $expectedProductRefsAndQuantities,
            $entity->getBilateralFeeProductRefsAndQuantities()
        );
    }

    public function testGetBilateralFeeProductRefsAndQuantitiesMoroccoOnly(): void
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);

        $bilateralRequired = [
            Entity::BILATERAL_STANDARD_REQUIRED => null,
            Entity::BILATERAL_CABOTAGE_REQUIRED => null,
            Entity::BILATERAL_MOROCCO_REQUIRED => 10
        ];

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getBilateralRequired')
            ->withNoArgs()
            ->andReturn($bilateralRequired);
        $entity->shouldReceive('getBilateralFeeProductReferences')
            ->with($irhpPermitStock, Entity::BILATERAL_MOROCCO_REQUIRED)
            ->andReturn(['PRODUCT_REF_GHI', 'PRODUCT_REF_JKL']);

        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow->shouldReceive('getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $entity->setIrhpApplication($irhpApplication);
        $entity->setIrhpPermitWindow($irhpPermitWindow);

        $expectedProductRefsAndQuantities = [
            'PRODUCT_REF_GHI' => 10,
            'PRODUCT_REF_JKL' => 10,
        ];

        $this->assertEquals(
            $expectedProductRefsAndQuantities,
            $entity->getBilateralFeeProductRefsAndQuantities()
        );
    }


    public function testGetBilateralFeeProductRefsAndQuantitiesNotBilateral(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('getBilateralFeeProductRefsAndQuantities is applicable only to bilateral applications');

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->setIrhpApplication($irhpApplication);

        $this->sut->getBilateralFeeProductRefsAndQuantities();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetBilateralFeeProductReferencesNotMorocco')]
    public function testGetBilateralFeeProductReferencesNotMorocco(
        mixed $countryId,
        mixed $permitUsage,
        mixed $standardOrCabotage,
        mixed $expected
    ): void {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getBilateralPermitUsageSelection')
            ->withNoArgs()
            ->andReturn($permitUsage);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('isMorocco')
            ->withNoArgs()
            ->andReturnFalse();
        $irhpPermitStock->shouldReceive('getCountry->getId')
            ->withNoArgs()
            ->andReturn($countryId);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $entity->setIrhpApplication($irhpApplication);

        $this->assertEquals(
            $expected,
            $entity->getBilateralFeeProductReferences($irhpPermitStock, $standardOrCabotage)
        );
    }

    public static function dpGetBilateralFeeProductReferencesNotMorocco(): array
    {
        return [
            [
                Country::ID_NORWAY,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                Entity::BILATERAL_FEE_PRODUCT_REFS_TYPE_2
            ],
            [
                Country::ID_NORWAY,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                Entity::BILATERAL_FEE_PRODUCT_REFS_TYPE_2
            ],
            [
                Country::ID_NORWAY,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                Entity::BILATERAL_FEE_PRODUCT_REFS_TYPE_1
            ],
            [
                Country::ID_NORWAY,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                Entity::BILATERAL_FEE_PRODUCT_REFS_TYPE_2
            ],
            [
                'XX',
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                Entity::BILATERAL_FEE_PRODUCT_REFS_TYPE_2
            ],
            [
                'XX',
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                Entity::BILATERAL_FEE_PRODUCT_REFS_TYPE_2
            ],
            [
                'XX',
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                Entity::BILATERAL_FEE_PRODUCT_REFS_TYPE_1
            ],
            [
                'XX',
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                Entity::BILATERAL_FEE_PRODUCT_REFS_TYPE_2
            ],
            [
                Country::ID_BELARUS,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                [
                    FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU
                ]
            ],
            [
                Country::ID_GEORGIA,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                [
                    FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU
                ]
            ],
            [
                Country::ID_RUSSIA,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                [
                    FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU
                ]
            ],
            [
                Country::ID_RUSSIA,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                [
                    FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU
                ]
            ],
            [
                Country::ID_TUNISIA,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                [
                    FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU
                ]
            ],
            [
                Country::ID_TURKEY,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                [
                    FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU
                ]
            ],
            [
                Country::ID_UKRAINE,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                [
                    FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU
                ]
            ],
            [
                Country::ID_KAZAKHSTAN,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                []
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetBilateralFeeProductReferencesNotMoroccoNotFound')]
    public function testGetBilateralFeeProductReferencesNotMoroccoNotFound(
        mixed $countryId,
        mixed $permitUsage,
        mixed $standardOrCabotage,
        mixed $expectedMessage
    ): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expectedMessage);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getBilateralPermitUsageSelection')
            ->withNoArgs()
            ->andReturn($permitUsage);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('isMorocco')
            ->withNoArgs()
            ->andReturnFalse();
        $irhpPermitStock->shouldReceive('getCountry->getId')
            ->withNoArgs()
            ->andReturn($countryId);

        $entity->setIrhpApplication($irhpApplication);

        $entity->getBilateralFeeProductReferences($irhpPermitStock, $standardOrCabotage);
    }

    public static function dpGetBilateralFeeProductReferencesNotMoroccoNotFound(): array
    {
        return [
            [
                Country::ID_BELARUS,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                'No bilateral fee configuration found: BY/journey_multiple/standard'
            ],
            [
                Country::ID_BELARUS,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: BY/journey_single/cabotage'
            ],
            [
                Country::ID_BELARUS,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: BY/journey_multiple/cabotage'
            ],
            [
                Country::ID_GEORGIA,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                'No bilateral fee configuration found: GE/journey_multiple/standard'
            ],
            [
                Country::ID_GEORGIA,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: GE/journey_single/cabotage'
            ],
            [
                Country::ID_GEORGIA,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: GE/journey_multiple/cabotage'
            ],
            [
                Country::ID_RUSSIA,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                'No bilateral fee configuration found: RU/journey_multiple/standard'
            ],
            [
                Country::ID_RUSSIA,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: RU/journey_single/cabotage'
            ],
            [
                Country::ID_RUSSIA,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: RU/journey_multiple/cabotage'
            ],
            [
                Country::ID_TUNISIA,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                'No bilateral fee configuration found: TN/journey_multiple/standard'
            ],
            [
                Country::ID_TUNISIA,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: TN/journey_single/cabotage'
            ],
            [
                Country::ID_TUNISIA,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: TN/journey_multiple/cabotage'
            ],
            [
                Country::ID_TURKEY,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                'No bilateral fee configuration found: TR/journey_multiple/standard'
            ],
            [
                Country::ID_TURKEY,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: TR/journey_single/cabotage'
            ],
            [
                Country::ID_TURKEY,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: TR/journey_multiple/cabotage'
            ],
            [
                Country::ID_UKRAINE,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                'No bilateral fee configuration found: UA/journey_multiple/standard'
            ],
            [
                Country::ID_UKRAINE,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: UA/journey_single/cabotage'
            ],
            [
                Country::ID_UKRAINE,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: UA/journey_multiple/cabotage'
            ],
            [
                Country::ID_KAZAKHSTAN,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_STANDARD_REQUIRED,
                'No bilateral fee configuration found: KZ/journey_multiple/standard'
            ],
            [
                Country::ID_KAZAKHSTAN,
                RefData::JOURNEY_SINGLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: KZ/journey_single/cabotage'
            ],
            [
                Country::ID_KAZAKHSTAN,
                RefData::JOURNEY_MULTIPLE,
                Entity::BILATERAL_CABOTAGE_REQUIRED,
                'No bilateral fee configuration found: KZ/journey_multiple/cabotage'
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetBilateralFeeProductReferencesMorocco')]
    public function testGetBilateralFeeProductReferencesMorocco(mixed $permitCategoryId, mixed $expected): void
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('isMorocco')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpPermitStock->shouldReceive('getPermitCategory->getId')
            ->withNoArgs()
            ->andReturn($permitCategoryId);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $this->sut->setIrhpApplication($irhpApplication);

        $this->assertEquals(
            $expected,
            $this->sut->getBilateralFeeProductReferences($irhpPermitStock, Entity::BILATERAL_MOROCCO_REQUIRED)
        );
    }

    public static function dpGetBilateralFeeProductReferencesMorocco(): array
    {
        return [
            [
                RefData::PERMIT_CAT_STANDARD_MULTIPLE_15,
                [FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_MULTI_MOROCCO_PRODUCT_REF]
            ],
            [
                RefData::PERMIT_CAT_STANDARD_SINGLE,
                [FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU]
            ],
            [
                RefData::PERMIT_CAT_EMPTY_ENTRY,
                [FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU]
            ],
            [
                RefData::PERMIT_CAT_HORS_CONTINGENT,
                [FeeType::FEE_TYPE_IRHP_ISSUE_BILATERAL_SINGLE_PRODUCT_REF_NON_EU]
            ],
        ];
    }

    public function testGetBilateralFeeProductReferencesMoroccoNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No bilateral morocco fee configuration found: permit_category_other');

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('isMorocco')
            ->withNoArgs()
            ->andReturnTrue();
        $irhpPermitStock->shouldReceive('getPermitCategory->getId')
            ->withNoArgs()
            ->andReturn('permit_category_other');

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $this->sut->setIrhpApplication($irhpApplication);

        $this->sut->getBilateralFeeProductReferences($irhpPermitStock, Entity::BILATERAL_MOROCCO_REQUIRED);
    }

    public function testGetBilateralFeeProductReferencesNotBilateral(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('getBilateralFeeProductReferences is applicable only to bilateral applications');

        $irhpPermitStock = m::mock(IrhpPermitStock::class);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->setIrhpApplication($irhpApplication);

        $this->sut->getBilateralFeeProductReferences($irhpPermitStock, Entity::BILATERAL_STANDARD_REQUIRED);
    }

    public function testGetBilateralFeePerPermit(): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $this->sut->setIrhpApplication($irhpApplication);

        $applicationFeeType = m::mock(FeeType::class);
        $applicationFeeType->shouldReceive('getFixedValue')
            ->withNoArgs()
            ->andReturn(5);

        $issueFeeType = m::mock(FeeType::class);
        $issueFeeType->shouldReceive('getFixedValue')
            ->withNoArgs()
            ->andReturn(13);

        $feeTypes = [$applicationFeeType, $issueFeeType];

        $this->assertEquals(
            18,
            $this->sut->getBilateralFeePerPermit($feeTypes)
        );
    }

    public function testGetBilateralFeePerPermitNotBilateral(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('getBilateralFeePerPermit is applicable only to bilateral applications');

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->setIrhpApplication($irhpApplication);

        $feeTypes = [m::mock(FeeType::class)];

        $this->sut->getBilateralFeePerPermit($feeTypes);
    }

    public function testGetOutstandingFees(): void
    {
        $fee1 = m::mock(Fee::class);
        $fee1->shouldReceive('isOutstanding')
            ->andReturnTrue();

        $fee2 = m::mock(Fee::class);
        $fee2->shouldReceive('isOutstanding')
            ->andReturnFalse();

        $fee3 = m::mock(Fee::class);
        $fee3->shouldReceive('isOutstanding')
            ->andReturnTrue();

        $fees = [$fee1, $fee2, $fee3];

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getFees')
            ->withNoArgs()
            ->andReturn($fees);

        $outstandingFees = $entity->getOutstandingFees();

        $this->assertCount(2, $outstandingFees);
        $this->assertContains($fee1, $outstandingFees);
        $this->assertContains($fee3, $outstandingFees);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsAssociatedWithBilateralCabotageOnlyApplicationPathGroup')]
    public function testIsAssociatedWithBilateralCabotageOnlyApplicationPathGroup(mixed $isBilateralCabotageOnly, mixed $expected): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getApplicationPathGroup->isBilateralCabotageOnly')
            ->withNoArgs()
            ->andReturn($isBilateralCabotageOnly);

        $this->assertEquals(
            $expected,
            $entity->isAssociatedWithBilateralCabotageOnlyApplicationPathGroup()
        );
    }

    public static function dpIsAssociatedWithBilateralCabotageOnlyApplicationPathGroup(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    public function testupdateCheckAnswers(): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue()
            ->shouldReceive('canBeUpdated')
            ->withNoArgs()
            ->andReturnTrue();

        $this->sut->setIrhpApplication($irhpApplication);

        $this->assertSame(0, $this->sut->getCheckedAnswers());
        $this->sut->updateCheckAnswers();
        $this->assertTrue($this->sut->getCheckedAnswers());
    }

    public function testupdateCheckAnswersNotBilateral(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_CHECK_ANSWERS);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->setIrhpApplication($irhpApplication);

        $this->sut->updateCheckAnswers();
    }

    public function testupdateCheckAnswersNotUpdatableBilateral(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Entity::ERR_CANT_CHECK_ANSWERS);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue()
            ->shouldReceive('canBeUpdated')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->setIrhpApplication($irhpApplication);

        $this->sut->updateCheckAnswers();
    }

    public function testCountPermitsRequiredBilateral(): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpApplication($irhpApplication);

        $filteredBilateralRequired = [
            Entity::BILATERAL_STANDARD_REQUIRED => 6,
            Entity::BILATERAL_CABOTAGE_REQUIRED => 11,
        ];

        $entity->shouldReceive('getFilteredBilateralRequired')
            ->withNoArgs()
            ->andReturn($filteredBilateralRequired);

        $this->assertEquals(
            17,
            $entity->countPermitsRequired()
        );
    }

    public function testCountPermitsRequiredNotBilateral(): void
    {
        $permitsRequired = 13;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->setIrhpApplication($irhpApplication);
        $this->sut->setPermitsRequired($permitsRequired);

        $this->assertEquals(
            $permitsRequired,
            $this->sut->countPermitsRequired()
        );
    }
}
