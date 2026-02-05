<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Permits;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Service\Permits\Allocate\EmissionsStandardCriteria;
use Dvsa\Olcs\Api\Service\Permits\Allocate\RangeMatchingCriteriaInterface;
use Exception;
use Mockery as m;
use RuntimeException;

/**
 * IrhpPermitStock Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class IrhpPermitStockEntityTest extends EntityTester
{
    use ProcessDateTrait;

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreateUpdate(): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();
        $validFrom = '2019-01-01';
        $expectedFrom = $this->processDate($validFrom, 'Y-m-d');
        $validTo = '2019-02-01';
        $expectedTo = $this->processDate($validTo, 'Y-m-d');
        $initialStock = 1400;

        $updateValidFrom = '2019-02-01';
        $updateExpectedFrom = $this->processDate($updateValidFrom, 'Y-m-d');
        $updateValidTo = '2019-02-02';
        $updateExpectedTo = $this->processDate($updateValidTo, 'Y-m-d');
        $updateInitialStock = 1401;
        $status = m::mock(RefData::class);

        $irhpPermitType->shouldReceive('getId')
            ->andReturn(3);

        $entity = Entity::create(
            $irhpPermitType,
            null,
            null,
            $initialStock,
            $status,
            null,
            null,
            null,
            $validFrom,
            $validTo
        );

        $this->assertSame($irhpPermitType, $entity->getIrhpPermitType());
        $this->assertEquals($expectedFrom, $entity->getValidFrom());
        $this->assertEquals($expectedTo, $entity->getValidTo());
        $this->assertEquals($initialStock, $entity->getInitialStock());
        $this->assertSame($status, $entity->getStatus());

        $entity->update($irhpPermitType, null, null, $updateInitialStock, null, $updateValidFrom, $updateValidTo);

        $this->assertEquals($updateExpectedFrom, $entity->getValidFrom());
        $this->assertEquals($updateExpectedTo, $entity->getValidTo());
        $this->assertEquals($updateInitialStock, $entity->getInitialStock());
    }

    public function testCreateUpdateAppPath(): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();
        $validFrom = '2019-01-01';
        $expectedFrom = $this->processDate($validFrom, 'Y-m-d');
        $validTo = '2019-02-01';
        $expectedTo = $this->processDate($validTo, 'Y-m-d');
        $initialStock = 1400;
        $applicationPathGroup = m::mock(ApplicationPathGroup::class);
        $businessProcess = m::mock(RefData::class);
        $periodNameKey = 'initial.period.name.key';

        $updateValidFrom = '2019-02-01';
        $updateExpectedFrom = $this->processDate($updateValidFrom, 'Y-m-d');
        $updateValidTo = '2019-02-02';
        $updateExpectedTo = $this->processDate($updateValidTo, 'Y-m-d');
        $updateInitialStock = 1401;
        $status = m::mock(RefData::class);
        $updatePeriodNameKey = 'updated.period.name.key';

        $irhpPermitType->shouldReceive('getId')
            ->andReturn(3);

        $entity = Entity::create(
            $irhpPermitType,
            null,
            null,
            $initialStock,
            $status,
            $applicationPathGroup,
            $businessProcess,
            $periodNameKey,
            $validFrom,
            $validTo
        );

        $this->assertSame($irhpPermitType, $entity->getIrhpPermitType());
        $this->assertEquals($expectedFrom, $entity->getValidFrom());
        $this->assertEquals($expectedTo, $entity->getValidTo());
        $this->assertEquals($initialStock, $entity->getInitialStock());
        $this->assertSame($status, $entity->getStatus());

        $entity->update($irhpPermitType, null, null, $updateInitialStock, $updatePeriodNameKey, $updateValidFrom, $updateValidTo);

        $this->assertEquals($updateExpectedFrom, $entity->getValidFrom());
        $this->assertEquals($updateExpectedTo, $entity->getValidTo());
        $this->assertEquals($updateInitialStock, $entity->getInitialStock());
        $this->assertEquals($updatePeriodNameKey, $entity->getPeriodNameKey());
    }

    public function testCreateUpdateAppPathBilateral(): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();
        $country = m::mock(Country::class);
        $country->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(Country::ID_BELARUS);
        $permitCategory = null;
        $validFrom = '2019-01-01';
        $expectedFrom = $this->processDate($validFrom, 'Y-m-d');
        $validTo = '2019-02-01';
        $expectedTo = $this->processDate($validTo, 'Y-m-d');
        $initialStock = 1400;
        $applicationPathGroup = m::mock(ApplicationPathGroup::class);
        $businessProcess = m::mock(RefData::class);
        $periodNameKey = 'initial.period.name.key';

        $updateCountry = m::mock(Country::class);
        $updateCountry->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(Country::ID_NORWAY);
        $updatePermitCategory = null;
        $updateValidFrom = '2019-02-01';
        $updateExpectedFrom = $this->processDate($updateValidFrom, 'Y-m-d');
        $updateValidTo = '2019-02-02';
        $updateExpectedTo = $this->processDate($updateValidTo, 'Y-m-d');
        $updateInitialStock = 1401;
        $status = m::mock(RefData::class);
        $updatePeriodNameKey = 'updated.period.name.key';

        $irhpPermitType->shouldReceive('getId')
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);

        $entity = Entity::create(
            $irhpPermitType,
            $country,
            $permitCategory,
            $initialStock,
            $status,
            $applicationPathGroup,
            $businessProcess,
            $periodNameKey,
            $validFrom,
            $validTo
        );

        $this->assertSame($irhpPermitType, $entity->getIrhpPermitType());
        $this->assertSame($country, $entity->getCountry());
        $this->assertSame($permitCategory, $entity->getPermitCategory());
        $this->assertEquals($expectedFrom, $entity->getValidFrom());
        $this->assertEquals($expectedTo, $entity->getValidTo());
        $this->assertEquals($initialStock, $entity->getInitialStock());
        $this->assertEquals($status, $entity->getStatus());

        $entity->update(
            $irhpPermitType,
            $updateCountry,
            $updatePermitCategory,
            $updateInitialStock,
            $updatePeriodNameKey,
            $updateValidFrom,
            $updateValidTo
        );

        $this->assertSame($updateCountry, $entity->getCountry());
        $this->assertSame($updatePermitCategory, $entity->getPermitCategory());
        $this->assertEquals($updateExpectedFrom, $entity->getValidFrom());
        $this->assertEquals($updateExpectedTo, $entity->getValidTo());
        $this->assertEquals($updateInitialStock, $entity->getInitialStock());
        $this->assertEquals($updatePeriodNameKey, $entity->getPeriodNameKey());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpBilateralMoroccoException')]
    public function testCreateBilateralMoroccoException(mixed $countryId, mixed $permitCategory, mixed $expectedMessage): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);

        $country = m::mock(Country::class);
        $country->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($countryId);

        $exceptionRaised = false;

        try {
            Entity::create(
                $irhpPermitType,
                $country,
                $permitCategory,
                1400,
                m::mock(RefData::class),
                m::mock(ApplicationPathGroup::class),
                m::mock(RefData::class),
                'initial.period.name.key',
                '2019-01-01',
                '2019-02-02'
            );
        } catch (Exception $e) {
            $exceptionRaised = true;

            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertEquals(
                [$expectedMessage],
                $e->getMessages()
            );
        }

        $this->assertTrue($exceptionRaised);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpBilateralMoroccoException')]
    public function testUpdateBilateralMoroccoException(mixed $countryId, mixed $permitCategory, mixed $expectedMessage): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);

        $oldCountry = m::mock(Country::class);
        $oldCountry->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(Country::ID_BELARUS);

        $country = m::mock(Country::class);
        $country->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($countryId);

        $entity = Entity::create(
            $irhpPermitType,
            $oldCountry,
            null,
            1400,
            m::mock(RefData::class),
            m::mock(ApplicationPathGroup::class),
            m::mock(RefData::class),
            'initial.period.name.key',
            '2019-01-01',
            '2019-02-02'
        );

        $exceptionRaised = false;

        try {
            $entity->update(
                $irhpPermitType,
                $country,
                $permitCategory,
                1401,
                'initial.period.name.key',
                '2019-01-01',
                '2019-02-02'
            );
        } catch (Exception $e) {
            $exceptionRaised = true;

            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertEquals(
                [$expectedMessage],
                $e->getMessages()
            );
        }

        $this->assertTrue($exceptionRaised);
    }

    public static function dpBilateralMoroccoException(): array
    {
        return [
            [
                Country::ID_MOROCCO,
                null,
                'Permit category must be specified for Bilateral Morocco stocks'
            ],
            [
                Country::ID_BELARUS,
                m::mock(RefData::class),
                'Permit category is only applicable for Bilateral Morocco stocks'
            ],
        ];
    }


    public function testGetOpenWindowTrue(): void
    {
        $window1 = m::mock(IrhpPermitWindow::class);
        $window1->shouldReceive('isActive')->once()->withNoArgs()->andReturnFalse();

        $window2 = m::mock(IrhpPermitWindow::class);
        $window2->shouldReceive('isActive')->once()->withNoArgs()->andReturnTrue();

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpPermitWindows(new ArrayCollection([$window1, $window2]));

        $this->assertSame(
            $window2,
            $entity->getOpenWindow()
        );
    }

    public function testGetOpenWindowNull(): void
    {
        $window1 = m::mock(IrhpPermitWindow::class);
        $window1->shouldReceive('isActive')->once()->withNoArgs()->andReturnFalse();

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpPermitWindows(new ArrayCollection([$window1]));

        $this->assertNull(
            $entity->getOpenWindow()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHasOpenWindow')]
    public function testHasOpenWindow(mixed $openWindow, mixed $expected): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getOpenWindow')
           ->withNoArgs()
           ->andReturn($openWindow);

        $this->assertEquals(
            $expected,
            $entity->hasOpenWindow()
        );
    }

    public static function dpHasOpenWindow(): array
    {
        return [
            [null, false],
            [m::mock(IrhpPermitWindow::class), true],
        ];
    }

    public function testGetStatusDescription(): void
    {
        $statusDescription = 'status description';

        $status = m::mock(RefData::class);
        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();
        $status->shouldReceive('getDescription')
            ->andReturn($statusDescription);

        $stock = Entity::create(
            $irhpPermitType,
            null,
            null,
            1400,
            $status,
            null,
            null,
            null,
            '2019-01-01',
            '2019-02-01'
        );

        $irhpPermitType->shouldReceive('getId')
            ->andReturn(3);

        $this->assertEquals(
            $statusDescription,
            $stock->getStatusDescription()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('canDeleteProvider')]
    public function testCanDelete(mixed $data, mixed $expected): void
    {
        $status = m::mock(RefData::class);
        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();

        $stock = Entity::create(
            $irhpPermitType,
            null,
            null,
            1400,
            $status,
            null,
            null,
            null,
            '2019-01-01',
            '2019-02-01'
        );

        $irhpPermitType->shouldReceive('getId')
            ->andReturn(3);

        $stock->setIrhpPermitRanges($data['irhpPermitRanges']);
        $stock->setIrhpPermitWindows($data['irhpPermitWindows']);

        $this->assertEquals($expected, $stock->canDelete());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emissionsRangeProvider')]
    public function testHasEuro5Range(mixed $data, mixed $expected): void
    {
        $status = m::mock(RefData::class);
        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();

        $stock = Entity::create(
            $irhpPermitType,
            null,
            null,
            1400,
            $status,
            null,
            null,
            null,
            '2019-01-01',
            '2019-02-01'
        );

        $stock->setIrhpPermitRanges($data);

        $this->assertEquals($expected['euro5'], $stock->hasEuro5Range());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emissionsRangeProvider')]
    public function testHasEuro6Range(mixed $data, mixed $expected): void
    {
        $status = m::mock(RefData::class);
        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();

        $stock = Entity::create(
            $irhpPermitType,
            null,
            null,
            1400,
            $status,
            null,
            null,
            null,
            '2019-01-01',
            '2019-02-01'
        );

        $stock->setIrhpPermitRanges($data);

        $this->assertEquals($expected['euro6'], $stock->hasEuro6Range());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function emissionsRangeProvider(): array
    {
        $euro5Range = m::mock(IrhpPermitRange::class)->makePartial();
        $euro5Range->setEmissionsCategory(new RefData(RefData::EMISSIONS_CATEGORY_EURO5_REF));

        $euro6Range = m::mock(IrhpPermitRange::class)->makePartial();
        $euro6Range->setEmissionsCategory(new RefData(RefData::EMISSIONS_CATEGORY_EURO6_REF));

        $naRange = m::mock(IrhpPermitRange::class)->makePartial();
        $naRange->setEmissionsCategory(new RefData(RefData::EMISSIONS_CATEGORY_NA_REF));

        return [
            'both' => [
                [$euro5Range, $euro6Range],
                ['euro5' => true, 'euro6' => true],
            ],
            'euro5' => [
                [$euro5Range],
                ['euro5' => true, 'euro6' => false],
            ],
            'euro6' => [
                [$euro6Range],
                ['euro5' => false, 'euro6' => true],
            ],
            'na' => [
                [$naRange],
                ['euro5' => false, 'euro6' => false],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHasCabotageOrStandardRange')]
    public function testHasCabotageOrStandardRange(mixed $data, mixed $expected): void
    {
        $status = m::mock(RefData::class);
        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();

        $stock = Entity::create(
            $irhpPermitType,
            null,
            null,
            1400,
            $status,
            null,
            null
        );

        $stock->setIrhpPermitRanges($data);

        $this->assertEquals($expected['cabotage'], $stock->hasCabotageRange());
        $this->assertEquals($expected['standard'], $stock->hasStandardRange());
    }

    public static function dpHasCabotageOrStandardRange(): array
    {
        $cabotageRange = m::mock(IrhpPermitRange::class);
        $cabotageRange->shouldReceive('isCabotage')->withNoArgs()->andReturnTrue();
        $cabotageRange->shouldReceive('isStandard')->withNoArgs()->andReturnFalse();

        $standardRange = m::mock(IrhpPermitRange::class);
        $standardRange->shouldReceive('isCabotage')->withNoArgs()->andReturnFalse();
        $standardRange->shouldReceive('isStandard')->withNoArgs()->andReturnTrue();

        $naRange = m::mock(IrhpPermitRange::class);
        $naRange->shouldReceive('isCabotage')->withNoArgs()->andReturnFalse();
        $naRange->shouldReceive('isStandard')->withNoArgs()->andReturnFalse();

        return [
            'both' => [
                [$cabotageRange, $standardRange],
                ['cabotage' => true, 'standard' => true],
            ],
            'cabotage' => [
                [$cabotageRange],
                ['cabotage' => true, 'standard' => false],
            ],
            'standard' => [
                [$standardRange],
                ['cabotage' => false, 'standard' => true],
            ],
            'na' => [
                [$naRange],
                ['cabotage' => false, 'standard' => false],
            ],
        ];
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function canDeleteProvider(): array
    {
        return [
            'valid delete' => [
                [
                    'irhpPermitRanges' => [],
                    'irhpPermitWindows' => [],
                ],
                true,
            ],
            'existing range' => [
                [
                    'irhpPermitRanges' => [m::mock(IrhpPermitRange::class)],
                    'irhpPermitWindows' => [],
                ],
                false,
            ],
            'existing window' => [
                [
                    'irhpPermitRanges' => [],
                    'irhpPermitWindows' => [m::mock(IrhpPermitWindow::class)],
                ],
                false,
            ],
            'existing window and range' => [
                [
                    'irhpPermitRanges' => [m::mock(IrhpPermitRange::class)],
                    'irhpPermitWindows' => [m::mock(IrhpPermitWindow::class)],
                ],
                false,
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('statusAllowsQueueRunScoringProvider')]
    public function testStatusAllowsQueueRunScoring(mixed $statusId, mixed $expectedResult): void
    {
        $stock = $this->createEntityWithStatusId($statusId);

        $this->assertEquals(
            $expectedResult,
            $stock->statusAllowsQueueRunScoring()
        );
    }

    public static function statusAllowsQueueRunScoringProvider(): array
    {
        return [
            [Entity::STATUS_SCORING_NEVER_RUN, true],
            [Entity::STATUS_SCORING_PENDING, false],
            [Entity::STATUS_SCORING_IN_PROGRESS, false],
            [Entity::STATUS_SCORING_SUCCESSFUL, true],
            [Entity::STATUS_SCORING_PREREQUISITE_FAIL, true],
            [Entity::STATUS_SCORING_UNEXPECTED_FAIL, true],
            [Entity::STATUS_ACCEPT_PENDING, false],
            [Entity::STATUS_ACCEPT_IN_PROGRESS, false],
            [Entity::STATUS_ACCEPT_SUCCESSFUL, true],
            [Entity::STATUS_ACCEPT_PREREQUISITE_FAIL, true],
            [Entity::STATUS_ACCEPT_UNEXPECTED_FAIL, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('statusAllowsRunScoringProvider')]
    public function testStatusAllowsRunScoring(mixed $statusId, mixed $expectedResult): void
    {
        $stock = $this->createEntityWithStatusId($statusId);

        $this->assertEquals(
            $expectedResult,
            $stock->statusAllowsRunScoring()
        );
    }

    public static function statusAllowsRunScoringProvider(): array
    {
        return [
            [Entity::STATUS_SCORING_NEVER_RUN, false],
            [Entity::STATUS_SCORING_PENDING, true],
            [Entity::STATUS_SCORING_IN_PROGRESS, false],
            [Entity::STATUS_SCORING_SUCCESSFUL, false],
            [Entity::STATUS_SCORING_PREREQUISITE_FAIL, false],
            [Entity::STATUS_SCORING_UNEXPECTED_FAIL, false],
            [Entity::STATUS_ACCEPT_PENDING, false],
            [Entity::STATUS_ACCEPT_IN_PROGRESS, false],
            [Entity::STATUS_ACCEPT_SUCCESSFUL, false],
            [Entity::STATUS_ACCEPT_PREREQUISITE_FAIL, false],
            [Entity::STATUS_ACCEPT_UNEXPECTED_FAIL, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('statusAllowsQueueAcceptScoringAndPostScoringReportProvider')]
    public function testStatusAllowsQueueAcceptScoringAndPostScoringReport(mixed $statusId, mixed $expectedResult): void
    {
        $stock = $this->createEntityWithStatusId($statusId);

        $this->assertEquals(
            $expectedResult,
            $stock->statusAllowsQueueAcceptScoringAndPostScoringReport()
        );
    }

    public static function statusAllowsQueueAcceptScoringAndPostScoringReportProvider(): array
    {
        return [
            [Entity::STATUS_SCORING_NEVER_RUN, false],
            [Entity::STATUS_SCORING_PENDING, false],
            [Entity::STATUS_SCORING_IN_PROGRESS, false],
            [Entity::STATUS_SCORING_SUCCESSFUL, true],
            [Entity::STATUS_SCORING_PREREQUISITE_FAIL, false],
            [Entity::STATUS_SCORING_UNEXPECTED_FAIL, false],
            [Entity::STATUS_ACCEPT_PENDING, false],
            [Entity::STATUS_ACCEPT_IN_PROGRESS, false],
            [Entity::STATUS_ACCEPT_SUCCESSFUL, true],
            [Entity::STATUS_ACCEPT_PREREQUISITE_FAIL, true],
            [Entity::STATUS_ACCEPT_UNEXPECTED_FAIL, true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('statusAllowsAcceptScoringProvider')]
    public function testStatusAllowsAcceptScoring(mixed $statusId, mixed $expectedResult): void
    {
        $stock = $this->createEntityWithStatusId($statusId);

        $this->assertEquals(
            $expectedResult,
            $stock->statusAllowsAcceptScoring()
        );
    }

    public static function statusAllowsAcceptScoringProvider(): array
    {
        return [
            [Entity::STATUS_SCORING_NEVER_RUN, false],
            [Entity::STATUS_SCORING_PENDING, false],
            [Entity::STATUS_SCORING_IN_PROGRESS, false],
            [Entity::STATUS_SCORING_SUCCESSFUL, false],
            [Entity::STATUS_SCORING_PREREQUISITE_FAIL, false],
            [Entity::STATUS_SCORING_UNEXPECTED_FAIL, false],
            [Entity::STATUS_ACCEPT_PENDING, true],
            [Entity::STATUS_ACCEPT_IN_PROGRESS, false],
            [Entity::STATUS_ACCEPT_SUCCESSFUL, false],
            [Entity::STATUS_ACCEPT_PREREQUISITE_FAIL, false],
            [Entity::STATUS_ACCEPT_UNEXPECTED_FAIL, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToScoringPendingProvider')]
    public function testProceedToScoringPending(mixed $statusId): void
    {
        $stock = $this->createEntityWithStatusId($statusId);
        $newStatus = m::mock(RefData::class);

        $stock->proceedToScoringPending($newStatus);
        $this->assertSame($newStatus, $stock->getStatus());
    }

    public static function proceedToScoringPendingProvider(): array
    {
        return [
            [Entity::STATUS_SCORING_NEVER_RUN],
            [Entity::STATUS_SCORING_SUCCESSFUL],
            [Entity::STATUS_SCORING_PREREQUISITE_FAIL],
            [Entity::STATUS_SCORING_UNEXPECTED_FAIL],
            [Entity::STATUS_ACCEPT_PREREQUISITE_FAIL],
            [Entity::STATUS_ACCEPT_SUCCESSFUL],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToScoringPendingExceptionProvider')]
    public function testProceedToScoringPendingException(mixed $statusId, mixed $statusDescription, mixed $expectedExceptionMessages): void
    {
        $stock = $this->createEntityWithStatusIdAndDescription($statusId, $statusDescription);
        $exceptionThrown = false;

        try {
            $stock->proceedToScoringPending(m::mock(RefData::class));
        } catch (ForbiddenException $e) {
            $exceptionThrown = true;
            $this->assertEquals($expectedExceptionMessages, $e->getMessages());
        }

        $this->assertTrue($exceptionThrown);
    }

    public static function proceedToScoringPendingExceptionProvider(): array
    {
        return [
            [
                Entity::STATUS_SCORING_PENDING,
                'Scoring pending',
                ['This stock is not in the correct status to proceed to scoring pending (Scoring pending)']
            ],
            [
                Entity::STATUS_SCORING_IN_PROGRESS,
                'Scoring in progress',
                ['This stock is not in the correct status to proceed to scoring pending (Scoring in progress)']
            ],
            [
                Entity::STATUS_ACCEPT_PENDING,
                'Accept pending',
                ['This stock is not in the correct status to proceed to scoring pending (Accept pending)']
            ],
            [
                Entity::STATUS_ACCEPT_IN_PROGRESS,
                'Accept in progress',
                ['This stock is not in the correct status to proceed to scoring pending (Accept in progress)']
            ],
            [
                Entity::STATUS_ACCEPT_UNEXPECTED_FAIL,
                'Accept unexpected fail',
                ['This stock is not in the correct status to proceed to scoring pending (Accept unexpected fail)']
            ],
        ];
    }

    public function testProceedToScoringPrerequisiteFail(): void
    {
        $stock = $this->createEntityWithStatusId(Entity::STATUS_SCORING_PENDING);
        $newStatus = m::mock(RefData::class);

        $stock->proceedToScoringPrerequisiteFail($newStatus);
        $this->assertSame($newStatus, $stock->getStatus());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToScoringPrerequisiteFailExceptionProvider')]
    public function testProceedToScoringPrerequisiteFailException(mixed $statusId, mixed $statusDescription, mixed $expectedExceptionMessages): void
    {
        $stock = $this->createEntityWithStatusIdAndDescription($statusId, $statusDescription);
        $exceptionThrown = false;

        try {
            $stock->proceedToScoringPrerequisiteFail(m::mock(RefData::class));
        } catch (ForbiddenException $e) {
            $exceptionThrown = true;
            $this->assertEquals($expectedExceptionMessages, $e->getMessages());
        }

        $this->assertTrue($exceptionThrown);
    }

    public static function proceedToScoringPrerequisiteFailExceptionProvider(): array
    {
        return [
            [
                Entity::STATUS_SCORING_NEVER_RUN,
                'Scoring never run',
                ['This stock is not in the correct status to proceed to scoring prerequisite fail (Scoring never run)']
            ],
            [
                Entity::STATUS_SCORING_IN_PROGRESS,
                'Scoring in progress',
                ['This stock is not in the correct status to proceed to scoring prerequisite fail (Scoring in progress)']
            ],
            [
                Entity::STATUS_SCORING_SUCCESSFUL,
                'Scoring successful',
                ['This stock is not in the correct status to proceed to scoring prerequisite fail (Scoring successful)']
            ],
            [
                Entity::STATUS_SCORING_PREREQUISITE_FAIL,
                'Scoring prerequisite fail',
                ['This stock is not in the correct status to proceed to scoring prerequisite fail (Scoring prerequisite fail)']
            ],
            [
                Entity::STATUS_SCORING_UNEXPECTED_FAIL,
                'Scoring unexpected fail',
                ['This stock is not in the correct status to proceed to scoring prerequisite fail (Scoring unexpected fail)']
            ],
            [
                Entity::STATUS_ACCEPT_PENDING,
                'Accept pending',
                ['This stock is not in the correct status to proceed to scoring prerequisite fail (Accept pending)']
            ],
            [
                Entity::STATUS_ACCEPT_IN_PROGRESS,
                'Accept in progress',
                ['This stock is not in the correct status to proceed to scoring prerequisite fail (Accept in progress)']
            ],
            [
                Entity::STATUS_ACCEPT_SUCCESSFUL,
                'Accept successful',
                ['This stock is not in the correct status to proceed to scoring prerequisite fail (Accept successful)']
            ],
            [
                Entity::STATUS_ACCEPT_PREREQUISITE_FAIL,
                'Accept prerequisite fail',
                ['This stock is not in the correct status to proceed to scoring prerequisite fail (Accept prerequisite fail)']
            ],
            [
                Entity::STATUS_ACCEPT_UNEXPECTED_FAIL,
                'Accept unexpected fail',
                ['This stock is not in the correct status to proceed to scoring prerequisite fail (Accept unexpected fail)']
            ],
        ];
    }

    public function testProceedToScoringInProgress(): void
    {
        $stock = $this->createEntityWithStatusId(Entity::STATUS_SCORING_PENDING);
        $newStatus = m::mock(RefData::class);

        $stock->proceedToScoringInProgress($newStatus);
        $this->assertSame($newStatus, $stock->getStatus());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToScoringInProgressExceptionProvider')]
    public function testProceedToScoringInProgressException(mixed $statusId, mixed $statusDescription, mixed $expectedExceptionMessages): void
    {
        $stock = $this->createEntityWithStatusIdAndDescription($statusId, $statusDescription);
        $exceptionThrown = false;

        try {
            $stock->proceedToScoringInProgress(m::mock(RefData::class));
        } catch (ForbiddenException $e) {
            $exceptionThrown = true;
            $this->assertEquals($expectedExceptionMessages, $e->getMessages());
        }

        $this->assertTrue($exceptionThrown);
    }

    public static function proceedToScoringInProgressExceptionProvider(): array
    {
        return [
            [
                Entity::STATUS_SCORING_NEVER_RUN,
                'Scoring never run',
                ['This stock is not in the correct status to proceed to scoring in progress (Scoring never run)']
            ],
            [
                Entity::STATUS_SCORING_IN_PROGRESS,
                'Scoring in progress',
                ['This stock is not in the correct status to proceed to scoring in progress (Scoring in progress)']
            ],
            [
                Entity::STATUS_SCORING_SUCCESSFUL,
                'Scoring successful',
                ['This stock is not in the correct status to proceed to scoring in progress (Scoring successful)']
            ],
            [
                Entity::STATUS_SCORING_PREREQUISITE_FAIL,
                'Scoring prerequisite fail',
                ['This stock is not in the correct status to proceed to scoring in progress (Scoring prerequisite fail)']
            ],
            [
                Entity::STATUS_SCORING_UNEXPECTED_FAIL,
                'Scoring unexpected fail',
                ['This stock is not in the correct status to proceed to scoring in progress (Scoring unexpected fail)']
            ],
            [
                Entity::STATUS_ACCEPT_PENDING,
                'Accept pending',
                ['This stock is not in the correct status to proceed to scoring in progress (Accept pending)']
            ],
            [
                Entity::STATUS_ACCEPT_IN_PROGRESS,
                'Accept in progress',
                ['This stock is not in the correct status to proceed to scoring in progress (Accept in progress)']
            ],
            [
                Entity::STATUS_ACCEPT_SUCCESSFUL,
                'Accept successful',
                ['This stock is not in the correct status to proceed to scoring in progress (Accept successful)']
            ],
            [
                Entity::STATUS_ACCEPT_PREREQUISITE_FAIL,
                'Accept prerequisite fail',
                ['This stock is not in the correct status to proceed to scoring in progress (Accept prerequisite fail)']
            ],
            [
                Entity::STATUS_ACCEPT_UNEXPECTED_FAIL,
                'Accept unexpected fail',
                ['This stock is not in the correct status to proceed to scoring in progress (Accept unexpected fail)']
            ],
        ];
    }

    public function testProceedToScoringSuccessful(): void
    {
        $stock = $this->createEntityWithStatusId(Entity::STATUS_SCORING_IN_PROGRESS);
        $newStatus = m::mock(RefData::class);

        $stock->proceedToScoringSuccessful($newStatus);
        $this->assertSame($newStatus, $stock->getStatus());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToScoringSuccessfulExceptionProvider')]
    public function testProceedToScoringSuccessfulException(mixed $statusId, mixed $statusDescription, mixed $expectedExceptionMessages): void
    {
        $stock = $this->createEntityWithStatusIdAndDescription($statusId, $statusDescription);
        $exceptionThrown = false;

        try {
            $stock->proceedToScoringSuccessful(m::mock(RefData::class));
        } catch (ForbiddenException $e) {
            $exceptionThrown = true;
            $this->assertEquals($expectedExceptionMessages, $e->getMessages());
        }

        $this->assertTrue($exceptionThrown);
    }

    public static function proceedToScoringSuccessfulExceptionProvider(): array
    {
        return [
            [
                Entity::STATUS_SCORING_NEVER_RUN,
                'Scoring never run',
                ['This stock is not in the correct status to proceed to scoring successful (Scoring never run)']
            ],
            [
                Entity::STATUS_SCORING_PENDING,
                'Scoring pending',
                ['This stock is not in the correct status to proceed to scoring successful (Scoring pending)']
            ],
            [
                Entity::STATUS_SCORING_SUCCESSFUL,
                'Scoring successful',
                ['This stock is not in the correct status to proceed to scoring successful (Scoring successful)']
            ],
            [
                Entity::STATUS_SCORING_PREREQUISITE_FAIL,
                'Scoring prerequisite fail',
                ['This stock is not in the correct status to proceed to scoring successful (Scoring prerequisite fail)']
            ],
            [
                Entity::STATUS_SCORING_UNEXPECTED_FAIL,
                'Scoring unexpected fail',
                ['This stock is not in the correct status to proceed to scoring successful (Scoring unexpected fail)']
            ],
            [
                Entity::STATUS_ACCEPT_PENDING,
                'Accept pending',
                ['This stock is not in the correct status to proceed to scoring successful (Accept pending)']
            ],
            [
                Entity::STATUS_ACCEPT_IN_PROGRESS,
                'Accept in progress',
                ['This stock is not in the correct status to proceed to scoring successful (Accept in progress)']
            ],
            [
                Entity::STATUS_ACCEPT_SUCCESSFUL,
                'Accept successful',
                ['This stock is not in the correct status to proceed to scoring successful (Accept successful)']
            ],
            [
                Entity::STATUS_ACCEPT_PREREQUISITE_FAIL,
                'Accept prerequisite fail',
                ['This stock is not in the correct status to proceed to scoring successful (Accept prerequisite fail)']
            ],
            [
                Entity::STATUS_ACCEPT_UNEXPECTED_FAIL,
                'Accept unexpected fail',
                ['This stock is not in the correct status to proceed to scoring successful (Accept unexpected fail)']
            ],
        ];
    }

    public function testProceedToScoringUnexpectedFail(): void
    {
        $stock = $this->createEntityWithStatusId(Entity::STATUS_SCORING_IN_PROGRESS);
        $newStatus = m::mock(RefData::class);

        $stock->proceedToScoringUnexpectedFail($newStatus);
        $this->assertSame($newStatus, $stock->getStatus());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToScoringUnexpectedFailExceptionProvider')]
    public function testProceedToScoringUnexpectedFailException(mixed $statusId, mixed $statusDescription, mixed $expectedExceptionMessages): void
    {
        $stock = $this->createEntityWithStatusIdAndDescription($statusId, $statusDescription);
        $exceptionThrown = false;

        try {
            $stock->proceedToScoringUnexpectedFail(m::mock(RefData::class));
        } catch (ForbiddenException $e) {
            $exceptionThrown = true;
            $this->assertEquals($expectedExceptionMessages, $e->getMessages());
        }

        $this->assertTrue($exceptionThrown);
    }

    public static function proceedToScoringUnexpectedFailExceptionProvider(): array
    {
        return [
            [
                Entity::STATUS_SCORING_NEVER_RUN,
                'Scoring never run',
                ['This stock is not in the correct status to proceed to scoring unexpected fail (Scoring never run)']
            ],
            [
                Entity::STATUS_SCORING_PENDING,
                'Scoring pending',
                ['This stock is not in the correct status to proceed to scoring unexpected fail (Scoring pending)']
            ],
            [
                Entity::STATUS_SCORING_SUCCESSFUL,
                'Scoring successful',
                ['This stock is not in the correct status to proceed to scoring unexpected fail (Scoring successful)']
            ],
            [
                Entity::STATUS_SCORING_PREREQUISITE_FAIL,
                'Scoring prerequisite fail',
                ['This stock is not in the correct status to proceed to scoring unexpected fail (Scoring prerequisite fail)']
            ],
            [
                Entity::STATUS_SCORING_UNEXPECTED_FAIL,
                'Scoring unexpected fail',
                ['This stock is not in the correct status to proceed to scoring unexpected fail (Scoring unexpected fail)']
            ],
            [
                Entity::STATUS_ACCEPT_PENDING,
                'Accept pending',
                ['This stock is not in the correct status to proceed to scoring unexpected fail (Accept pending)']
            ],
            [
                Entity::STATUS_ACCEPT_IN_PROGRESS,
                'Accept in progress',
                ['This stock is not in the correct status to proceed to scoring unexpected fail (Accept in progress)']
            ],
            [
                Entity::STATUS_ACCEPT_SUCCESSFUL,
                'Accept successful',
                ['This stock is not in the correct status to proceed to scoring unexpected fail (Accept successful)']
            ],
            [
                Entity::STATUS_ACCEPT_PREREQUISITE_FAIL,
                'Accept prerequisite fail',
                ['This stock is not in the correct status to proceed to scoring unexpected fail (Accept prerequisite fail)']
            ],
            [
                Entity::STATUS_ACCEPT_UNEXPECTED_FAIL,
                'Accept unexpected fail',
                ['This stock is not in the correct status to proceed to scoring unexpected fail (Accept unexpected fail)']
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToAcceptPendingProvider')]
    public function testProceedToAcceptPending(mixed $statusId): void
    {
        $stock = $this->createEntityWithStatusId($statusId);
        $newStatus = m::mock(RefData::class);

        $stock->proceedToAcceptPending($newStatus);
        $this->assertSame($newStatus, $stock->getStatus());
    }

    public static function proceedToAcceptPendingProvider(): array
    {
        return [
            [Entity::STATUS_SCORING_SUCCESSFUL],
            [Entity::STATUS_ACCEPT_PREREQUISITE_FAIL],
            [Entity::STATUS_ACCEPT_UNEXPECTED_FAIL],
            [Entity::STATUS_ACCEPT_SUCCESSFUL],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToAcceptPendingExceptionProvider')]
    public function testProceedToAcceptPendingException(mixed $statusId, mixed $statusDescription, mixed $expectedExceptionMessages): void
    {
        $stock = $this->createEntityWithStatusIdAndDescription($statusId, $statusDescription);
        $exceptionThrown = false;

        try {
            $stock->proceedToAcceptPending(m::mock(RefData::class));
        } catch (ForbiddenException $e) {
            $exceptionThrown = true;
            $this->assertEquals($expectedExceptionMessages, $e->getMessages());
        }

        $this->assertTrue($exceptionThrown);
    }

    public static function proceedToAcceptPendingExceptionProvider(): array
    {
        return [
            [
                Entity::STATUS_SCORING_NEVER_RUN,
                'Scoring never run',
                ['This stock is not in the correct status to proceed to accept pending (Scoring never run)']
            ],
            [
                Entity::STATUS_SCORING_PENDING,
                'Scoring pending',
                ['This stock is not in the correct status to proceed to accept pending (Scoring pending)']
            ],
            [
                Entity::STATUS_SCORING_IN_PROGRESS,
                'Scoring in progress',
                ['This stock is not in the correct status to proceed to accept pending (Scoring in progress)']
            ],
            [
                Entity::STATUS_SCORING_PREREQUISITE_FAIL,
                'Scoring prerequisite fail',
                ['This stock is not in the correct status to proceed to accept pending (Scoring prerequisite fail)']
            ],
            [
                Entity::STATUS_SCORING_UNEXPECTED_FAIL,
                'Scoring unexpected fail',
                ['This stock is not in the correct status to proceed to accept pending (Scoring unexpected fail)']
            ],
            [
                Entity::STATUS_ACCEPT_PENDING,
                'Accept pending',
                ['This stock is not in the correct status to proceed to accept pending (Accept pending)']
            ],
            [
                Entity::STATUS_ACCEPT_IN_PROGRESS,
                'Accept in progress',
                ['This stock is not in the correct status to proceed to accept pending (Accept in progress)']
            ],
        ];
    }

    public function testProceedToAcceptPrerequisiteFail(): void
    {
        $stock = $this->createEntityWithStatusId(Entity::STATUS_ACCEPT_PENDING);
        $newStatus = m::mock(RefData::class);

        $stock->proceedToAcceptPrerequisiteFail($newStatus);
        $this->assertSame($newStatus, $stock->getStatus());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToAcceptPrerequisiteFailExceptionProvider')]
    public function testProceedToAcceptPrerequisiteFailException(mixed $statusId, mixed $statusDescription, mixed $expectedExceptionMessages): void
    {
        $stock = $this->createEntityWithStatusIdAndDescription($statusId, $statusDescription);
        $exceptionThrown = false;

        try {
            $stock->proceedToAcceptPrerequisiteFail(m::mock(RefData::class));
        } catch (ForbiddenException $e) {
            $exceptionThrown = true;
            $this->assertEquals($expectedExceptionMessages, $e->getMessages());
        }

        $this->assertTrue($exceptionThrown);
    }

    public static function proceedToAcceptPrerequisiteFailExceptionProvider(): array
    {
        return [
            [
                Entity::STATUS_SCORING_NEVER_RUN,
                'Scoring never run',
                ['This stock is not in the correct status to proceed to accept prerequisite fail (Scoring never run)']
            ],
            [
                Entity::STATUS_SCORING_PENDING,
                'Scoring pending',
                ['This stock is not in the correct status to proceed to accept prerequisite fail (Scoring pending)']
            ],
            [
                Entity::STATUS_SCORING_IN_PROGRESS,
                'Scoring in progress',
                ['This stock is not in the correct status to proceed to accept prerequisite fail (Scoring in progress)']
            ],
            [
                Entity::STATUS_SCORING_SUCCESSFUL,
                'Scoring successful',
                ['This stock is not in the correct status to proceed to accept prerequisite fail (Scoring successful)']
            ],
            [
                Entity::STATUS_SCORING_PREREQUISITE_FAIL,
                'Scoring prerequisite fail',
                ['This stock is not in the correct status to proceed to accept prerequisite fail (Scoring prerequisite fail)']
            ],
            [
                Entity::STATUS_SCORING_UNEXPECTED_FAIL,
                'Scoring unexpected fail',
                ['This stock is not in the correct status to proceed to accept prerequisite fail (Scoring unexpected fail)']
            ],
            [
                Entity::STATUS_ACCEPT_IN_PROGRESS,
                'Accept in progress',
                ['This stock is not in the correct status to proceed to accept prerequisite fail (Accept in progress)']
            ],
            [
                Entity::STATUS_ACCEPT_SUCCESSFUL,
                'Accept successful',
                ['This stock is not in the correct status to proceed to accept prerequisite fail (Accept successful)']
            ],
            [
                Entity::STATUS_ACCEPT_PREREQUISITE_FAIL,
                'Accept prerequisite fail',
                ['This stock is not in the correct status to proceed to accept prerequisite fail (Accept prerequisite fail)']
            ],
            [
                Entity::STATUS_ACCEPT_UNEXPECTED_FAIL,
                'Accept unexpected fail',
                ['This stock is not in the correct status to proceed to accept prerequisite fail (Accept unexpected fail)']
            ],
        ];
    }

    public function testProceedToAcceptInProgress(): void
    {
        $stock = $this->createEntityWithStatusId(Entity::STATUS_ACCEPT_PENDING);
        $newStatus = m::mock(RefData::class);

        $stock->proceedToAcceptInProgress($newStatus);
        $this->assertSame($newStatus, $stock->getStatus());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToAcceptInProgressExceptionProvider')]
    public function testProceedToAcceptInProgressException(mixed $statusId, mixed $statusDescription, mixed $expectedExceptionMessages): void
    {
        $stock = $this->createEntityWithStatusIdAndDescription($statusId, $statusDescription);
        $exceptionThrown = false;

        try {
            $stock->proceedToAcceptInProgress(m::mock(RefData::class));
        } catch (ForbiddenException $e) {
            $exceptionThrown = true;
            $this->assertEquals($expectedExceptionMessages, $e->getMessages());
        }

        $this->assertTrue($exceptionThrown);
    }

    public static function proceedToAcceptInProgressExceptionProvider(): array
    {
        return [
            [
                Entity::STATUS_SCORING_PENDING,
                'Scoring pending',
                ['This stock is not in the correct status to proceed to accept in progress (Scoring pending)']
            ],
            [
                Entity::STATUS_SCORING_NEVER_RUN,
                'Scoring never run',
                ['This stock is not in the correct status to proceed to accept in progress (Scoring never run)']
            ],
            [
                Entity::STATUS_SCORING_IN_PROGRESS,
                'Scoring in progress',
                ['This stock is not in the correct status to proceed to accept in progress (Scoring in progress)']
            ],
            [
                Entity::STATUS_SCORING_SUCCESSFUL,
                'Scoring successful',
                ['This stock is not in the correct status to proceed to accept in progress (Scoring successful)']
            ],
            [
                Entity::STATUS_SCORING_PREREQUISITE_FAIL,
                'Scoring prerequisite fail',
                ['This stock is not in the correct status to proceed to accept in progress (Scoring prerequisite fail)']
            ],
            [
                Entity::STATUS_SCORING_UNEXPECTED_FAIL,
                'Scoring unexpected fail',
                ['This stock is not in the correct status to proceed to accept in progress (Scoring unexpected fail)']
            ],
            [
                Entity::STATUS_ACCEPT_IN_PROGRESS,
                'Accept in progress',
                ['This stock is not in the correct status to proceed to accept in progress (Accept in progress)']
            ],
            [
                Entity::STATUS_ACCEPT_SUCCESSFUL,
                'Accept successful',
                ['This stock is not in the correct status to proceed to accept in progress (Accept successful)']
            ],
            [
                Entity::STATUS_ACCEPT_PREREQUISITE_FAIL,
                'Accept prerequisite fail',
                ['This stock is not in the correct status to proceed to accept in progress (Accept prerequisite fail)']
            ],
            [
                Entity::STATUS_ACCEPT_UNEXPECTED_FAIL,
                'Accept unexpected fail',
                ['This stock is not in the correct status to proceed to accept in progress (Accept unexpected fail)']
            ],
        ];
    }

    public function testProceedToAcceptSuccessful(): void
    {
        $stock = $this->createEntityWithStatusId(Entity::STATUS_ACCEPT_IN_PROGRESS);
        $newStatus = m::mock(RefData::class);

        $stock->proceedToAcceptSuccessful($newStatus);
        $this->assertSame($newStatus, $stock->getStatus());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToAcceptSuccessfulExceptionProvider')]
    public function testProceedToAcceptSuccessfulException(mixed $statusId, mixed $statusDescription, mixed $expectedExceptionMessages): void
    {
        $stock = $this->createEntityWithStatusIdAndDescription($statusId, $statusDescription);
        $exceptionThrown = false;

        try {
            $stock->proceedToAcceptSuccessful(m::mock(RefData::class));
        } catch (ForbiddenException $e) {
            $exceptionThrown = true;
            $this->assertEquals($expectedExceptionMessages, $e->getMessages());
        }

        $this->assertTrue($exceptionThrown);
    }

    public static function proceedToAcceptSuccessfulExceptionProvider(): array
    {
        return [
            [
                Entity::STATUS_SCORING_NEVER_RUN,
                'Scoring never run',
                ['This stock is not in the correct status to proceed to accept successful (Scoring never run)']
            ],
            [
                Entity::STATUS_SCORING_PENDING,
                'Scoring pending',
                ['This stock is not in the correct status to proceed to accept successful (Scoring pending)']
            ],
            [
                Entity::STATUS_SCORING_IN_PROGRESS,
                'Scoring in progress',
                ['This stock is not in the correct status to proceed to accept successful (Scoring in progress)']
            ],
            [
                Entity::STATUS_SCORING_SUCCESSFUL,
                'Scoring successful',
                ['This stock is not in the correct status to proceed to accept successful (Scoring successful)']
            ],
            [
                Entity::STATUS_SCORING_PREREQUISITE_FAIL,
                'Scoring prerequisite fail',
                ['This stock is not in the correct status to proceed to accept successful (Scoring prerequisite fail)']
            ],
            [
                Entity::STATUS_SCORING_UNEXPECTED_FAIL,
                'Scoring unexpected fail',
                ['This stock is not in the correct status to proceed to accept successful (Scoring unexpected fail)']
            ],
            [
                Entity::STATUS_ACCEPT_PENDING,
                'Accept pending',
                ['This stock is not in the correct status to proceed to accept successful (Accept pending)']
            ],
            [
                Entity::STATUS_ACCEPT_SUCCESSFUL,
                'Accept successful',
                ['This stock is not in the correct status to proceed to accept successful (Accept successful)']
            ],
            [
                Entity::STATUS_ACCEPT_PREREQUISITE_FAIL,
                'Accept prerequisite fail',
                ['This stock is not in the correct status to proceed to accept successful (Accept prerequisite fail)']
            ],
            [
                Entity::STATUS_ACCEPT_UNEXPECTED_FAIL,
                'Accept unexpected fail',
                ['This stock is not in the correct status to proceed to accept successful (Accept unexpected fail)']
            ],
        ];
    }

    public function testProceedToAcceptUnexpectedFail(): void
    {
        $stock = $this->createEntityWithStatusId(Entity::STATUS_ACCEPT_IN_PROGRESS);
        $newStatus = m::mock(RefData::class);

        $stock->proceedToAcceptUnexpectedFail($newStatus);
        $this->assertSame($newStatus, $stock->getStatus());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('proceedToAcceptUnexpectedFailExceptionProvider')]
    public function testProceedToAcceptUnexpectedFailException(mixed $statusId, mixed $statusDescription, mixed $expectedExceptionMessages): void
    {
        $stock = $this->createEntityWithStatusIdAndDescription($statusId, $statusDescription);
        $exceptionThrown = false;

        try {
            $stock->proceedToAcceptUnexpectedFail(m::mock(RefData::class));
        } catch (ForbiddenException $e) {
            $exceptionThrown = true;
            $this->assertEquals($expectedExceptionMessages, $e->getMessages());
        }

        $this->assertTrue($exceptionThrown);
    }

    public static function proceedToAcceptUnexpectedFailExceptionProvider(): array
    {
        return [
            [
                Entity::STATUS_SCORING_NEVER_RUN,
                'Scoring never run',
                ['This stock is not in the correct status to proceed to accept unexpected fail (Scoring never run)']
            ],
            [
                Entity::STATUS_SCORING_PENDING,
                'Scoring pending',
                ['This stock is not in the correct status to proceed to accept unexpected fail (Scoring pending)']
            ],
            [
                Entity::STATUS_SCORING_IN_PROGRESS,
                'Scoring in progress',
                ['This stock is not in the correct status to proceed to accept unexpected fail (Scoring in progress)']
            ],
            [
                Entity::STATUS_SCORING_SUCCESSFUL,
                'Scoring successful',
                ['This stock is not in the correct status to proceed to accept unexpected fail (Scoring successful)']
            ],
            [
                Entity::STATUS_SCORING_PREREQUISITE_FAIL,
                'Scoring prerequisite fail',
                ['This stock is not in the correct status to proceed to accept unexpected fail (Scoring prerequisite fail)']
            ],
            [
                Entity::STATUS_SCORING_UNEXPECTED_FAIL,
                'Scoring unexpected fail',
                ['This stock is not in the correct status to proceed to accept unexpected fail (Scoring unexpected fail)']
            ],
            [
                Entity::STATUS_ACCEPT_PENDING,
                'Accept pending',
                ['This stock is not in the correct status to proceed to accept unexpected fail (Accept pending)']
            ],
            [
                Entity::STATUS_ACCEPT_SUCCESSFUL,
                'Accept successful',
                ['This stock is not in the correct status to proceed to accept unexpected fail (Accept successful)']
            ],
            [
                Entity::STATUS_ACCEPT_PREREQUISITE_FAIL,
                'Accept prerequisite fail',
                ['This stock is not in the correct status to proceed to accept unexpected fail (Accept prerequisite fail)']
            ],
            [
                Entity::STATUS_ACCEPT_UNEXPECTED_FAIL,
                'Accept unexpected fail',
                ['This stock is not in the correct status to proceed to accept unexpected fail (Accept unexpected fail)']
            ],
        ];
    }

    private function createEntityWithStatusIdAndDescription(mixed $statusId, mixed $description): mixed
    {
        $status = m::mock(RefData::class);
        $status->shouldReceive('getId')
            ->andReturn($statusId);
        $status->shouldReceive('getDescription')
            ->andReturn($description);

        return $this->createEntityWithStatus($status);
    }

    private function createEntityWithStatusId(mixed $statusId): mixed
    {
        $status = m::mock(RefData::class);
        $status->shouldReceive('getId')
            ->andReturn($statusId);

        return $this->createEntityWithStatus($status);
    }

    private function createEntityWithStatus(mixed $status): mixed
    {
        $irhpPermitType = m::mock(IrhpPermitType::class)->makePartial();
        $irhpStockEntity = Entity::create(
            $irhpPermitType,
            null,
            null,
            1400,
            $status,
            null,
            null,
            null,
            '2019-01-01',
            '2019-02-01'
        );
        $irhpPermitType->shouldReceive('getId')
            ->andReturn(3);
        return($irhpStockEntity);
    }

    public function testGetNonReservedNonReplacementRangesOrderedByFromNo(): void
    {
        $entity = m::mock(Entity::class)->makePartial();

        $firstExpectedRange = self::createMockRange(false, false, 300, RefData::EMISSIONS_CATEGORY_EURO5_REF);
        $secondExpectedRange = self::createMockRange(false, false, 420, RefData::EMISSIONS_CATEGORY_EURO6_REF);
        $thirdExpectedRange = self::createMockRange(false, false, 500, RefData::EMISSIONS_CATEGORY_EURO5_REF);

        $irhpPermitRanges = new ArrayCollection(
            [
                $secondExpectedRange,
                self::createMockRange(false, true, 230, RefData::EMISSIONS_CATEGORY_EURO5_REF),
                self::createMockRange(true, false, 100, RefData::EMISSIONS_CATEGORY_EURO6_REF),
                self::createMockRange(true, true, 500, RefData::EMISSIONS_CATEGORY_EURO5_REF),
                $firstExpectedRange,
                $thirdExpectedRange
            ]
        );

        $entity->shouldReceive('getIrhpPermitRanges')
            ->andReturn($irhpPermitRanges);

        $result = $entity->getNonReservedNonReplacementRangesOrderedByFromNo();

        $this->assertInstanceOf(ArrayCollection::class, $result);

        $resultAsArray = array_values($result->toArray());
        $this->assertSame($firstExpectedRange, $resultAsArray[0]);
        $this->assertSame($secondExpectedRange, $resultAsArray[1]);
        $this->assertSame($thirdExpectedRange, $resultAsArray[2]);
    }

    public function testGetNonReservedNonReplacementRangesOrderedByFromNoWithEmissionsCategoryId(): void
    {
        $entity = m::mock(Entity::class)->makePartial();

        $range1 = self::createMockRange(false, false, 600);
        $range2 = self::createMockRange(false, true, 700);
        $range3 = self::createMockRange(false, false, 500);
        $range4 = self::createMockRange(true, false, 800);
        $range5 = self::createMockRange(true, true, 900);
        $range6 = self::createMockRange(false, false, 300);
        $range7 = self::createMockRange(false, false, 1000);
        $range8 = self::createMockRange(false, true, 110);
        $range9 = self::createMockRange(true, false, 1200);
        $range10 = self::createMockRange(true, true, 1300);

        $criteria = m::mock(RangeMatchingCriteriaInterface::class);
        $criteria->shouldReceive('matches')->with($range1)->andReturn(false);
        $criteria->shouldReceive('matches')->with($range2)->andReturn(true);
        $criteria->shouldReceive('matches')->with($range3)->andReturn(true);
        $criteria->shouldReceive('matches')->with($range4)->andReturn(false);
        $criteria->shouldReceive('matches')->with($range5)->andReturn(true);
        $criteria->shouldReceive('matches')->with($range6)->andReturn(true);
        $criteria->shouldReceive('matches')->with($range7)->andReturn(false);
        $criteria->shouldReceive('matches')->with($range8)->andReturn(true);
        $criteria->shouldReceive('matches')->with($range9)->andReturn(false);
        $criteria->shouldReceive('matches')->with($range10)->andReturn(false);

        $irhpPermitRanges = new ArrayCollection(
            [$range1, $range2, $range3, $range4, $range5, $range6, $range7, $range8, $range9, $range10]
        );

        $entity->shouldReceive('getIrhpPermitRanges')
            ->andReturn($irhpPermitRanges);

        $result = $entity->getNonReservedNonReplacementRangesOrderedByFromNo($criteria);

        $this->assertInstanceOf(ArrayCollection::class, $result);

        $resultAsArray = array_values($result->toArray());
        $this->assertSame($range6, $resultAsArray[0]);
        $this->assertSame($range3, $resultAsArray[1]);
    }

    private static function createMockRange(mixed $ssReserve, mixed $lostReplacement, mixed $fromNo, mixed $emissionsCategoryId = null, mixed $journey = null): mixed
    {
        $irhpPermitRange = m::mock(IrhpPermitRange::class);
        $irhpPermitRange->shouldReceive('getSsReserve')
            ->andReturn($ssReserve);
        $irhpPermitRange->shouldReceive('getLostReplacement')
            ->andReturn($lostReplacement);
        $irhpPermitRange->shouldReceive('getFromNo')
            ->andReturn($fromNo);
        $irhpPermitRange->shouldReceive('getEmissionsCategory->getId')
            ->andReturn($emissionsCategoryId);
        $irhpPermitRange->shouldReceive('getJourney')
            ->andReturn($journey);

        return $irhpPermitRange;
    }

    public function testGetValidityYear(): void
    {
        $dateTime = new DateTime('2015-12-31');

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getValidTo')
            ->with(true)
            ->andReturn($dateTime);

        $this->assertEquals(2015, $entity->getValidityYear());
    }

    public function testGetValidityYearNullValidTo(): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getValidTo')
            ->with(true)
            ->andReturn(null);

        $this->assertNull($entity->getValidityYear());
    }

    /**
     * Where all ranges have restricted countries, bring back the first range (VOL-1839)
     */
    public function testGetFirstAvailableRangeWhereAllHaveRestrictedCountries(): void
    {
        $emissionsStandardCriteria = m::mock(EmissionsStandardCriteria::class);

        $entity = m::mock(Entity::class)->makePartial();

        $range1 = m::mock(IrhpPermitRange::class);
        $range1->shouldReceive('hasCountries')
            ->andReturn(true);

        $range2 = m::mock(IrhpPermitRange::class);
        $range2->shouldReceive('hasCountries')
            ->andReturn(true);

        $ranges = new ArrayCollection([$range1, $range2]);

        $entity->shouldReceive('getNonReservedNonReplacementRangesOrderedByFromNo')
            ->with($emissionsStandardCriteria)
            ->once()
            ->andReturn($ranges);

        $this->assertSame(
            $range1,
            $entity->getFirstAvailableRangePreferWithNoCountries($emissionsStandardCriteria)
        );
    }

    /**
     * We prefer to return a range with no restricted countries, here we return range 2
     * (hasCountries() evaluates to false on that range)
     */
    public function testGetFirstAvailableRangeWhereRangeWithNoCountriesAvailable(): void
    {
        $emissionsStandardCriteria = m::mock(EmissionsStandardCriteria::class);

        $entity = m::mock(Entity::class)->makePartial();

        $range1 = m::mock(IrhpPermitRange::class);
        $range1->shouldReceive('hasCountries')
            ->andReturn(true);

        $range2 = m::mock(IrhpPermitRange::class);
        $range2->shouldReceive('hasCountries')
            ->andReturn(false);

        $range3 = m::mock(IrhpPermitRange::class);
        $range3->shouldReceive('hasCountries')
            ->andReturn(true);

        $ranges = new ArrayCollection([$range1, $range2, $range3]);

        $entity->shouldReceive('getNonReservedNonReplacementRangesOrderedByFromNo')
            ->with($emissionsStandardCriteria)
            ->once()
            ->andReturn($ranges);

        $this->assertSame(
            $range2,
            $entity->getFirstAvailableRangePreferWithNoCountries($emissionsStandardCriteria)
        );
    }

    /**
     * Test we throw exception if there are no available ranges
     */
    public function testGetFirstAvailableRangePreferWithNoCountriesException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to find available range');

        $emissionsStandardCriteria = m::mock(EmissionsStandardCriteria::class);

        $entity = m::mock(Entity::class)->makePartial();

        $entity->shouldReceive('getNonReservedNonReplacementRangesOrderedByFromNo')
            ->with($emissionsStandardCriteria)
            ->once()
            ->andReturn(new ArrayCollection([]));

        $entity->getFirstAvailableRangePreferWithNoCountries($emissionsStandardCriteria);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetAllocationMode')]
    public function testGetAllocationMode(mixed $irhpPermitTypeId, mixed $businessProcessId, mixed $validityYear, mixed $expectedAllocationMode): void
    {
        $businessProcess = m::mock(RefData::class);
        $businessProcess->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($businessProcessId);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpPermitTypeId);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getValidityYear')
            ->withNoArgs()
            ->andReturn($validityYear);

        $entity->setIrhpPermitType($irhpPermitType);
        $entity->setBusinessProcess($businessProcess);

        $this->assertEquals(
            $expectedAllocationMode,
            $entity->getAllocationMode()
        );
    }

    public static function dpGetAllocationMode(): array
    {
        return [
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                RefData::BUSINESS_PROCESS_APSG,
                2019,
                Entity::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                RefData::BUSINESS_PROCESS_APG,
                2019,
                Entity::ALLOCATION_MODE_BILATERAL,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL,
                RefData::BUSINESS_PROCESS_APG,
                2019,
                Entity::ALLOCATION_MODE_STANDARD,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                RefData::BUSINESS_PROCESS_APGG,
                2019,
                Entity::ALLOCATION_MODE_EMISSIONS_CATEGORIES,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                RefData::BUSINESS_PROCESS_APSG,
                2019,
                Entity::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                RefData::BUSINESS_PROCESS_APG,
                2019,
                Entity::ALLOCATION_MODE_STANDARD_WITH_EXPIRY,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                RefData::BUSINESS_PROCESS_APG,
                2020,
                Entity::ALLOCATION_MODE_BILATERAL,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL,
                RefData::BUSINESS_PROCESS_APG,
                2020,
                Entity::ALLOCATION_MODE_STANDARD,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                RefData::BUSINESS_PROCESS_APGG,
                2020,
                Entity::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                RefData::BUSINESS_PROCESS_APSG,
                2020,
                Entity::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                RefData::BUSINESS_PROCESS_APG,
                2020,
                Entity::ALLOCATION_MODE_STANDARD_WITH_EXPIRY,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                RefData::BUSINESS_PROCESS_APSG,
                2019,
                Entity::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                RefData::BUSINESS_PROCESS_APSG,
                2020,
                Entity::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE,
                RefData::BUSINESS_PROCESS_AG,
                2019,
                Entity::ALLOCATION_MODE_NONE,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE,
                RefData::BUSINESS_PROCESS_AG,
                2020,
                Entity::ALLOCATION_MODE_NONE,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER,
                RefData::BUSINESS_PROCESS_AG,
                2019,
                Entity::ALLOCATION_MODE_NONE,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER,
                RefData::BUSINESS_PROCESS_AG,
                2020,
                Entity::ALLOCATION_MODE_NONE,
            ],
            [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                RefData::BUSINESS_PROCESS_APGG,
                2021,
                Entity::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
        ];
    }

    public function testGetAllocationModeException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'No allocation mode set for permit type ' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL
        );

        $businessProcess = m::mock(RefData::class);
        $businessProcess->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(RefData::BUSINESS_PROCESS_APSG);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setBusinessProcess($businessProcess);
        $entity->setIrhpPermitType($irhpPermitType);

        $entity->getAllocationMode();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetCandidatePermitCreationMode')]
    public function testGetCandidatePermitCreationMode(
        mixed $businessProcessId,
        mixed $isEcmtShortTerm,
        mixed $validityYear,
        mixed $expectedCandidatePermitCreationMode
    ): void {
        $businessProcess = m::mock(RefData::class);
        $businessProcess->shouldReceive('getId')
            ->andReturn($businessProcessId);

        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->andReturn($isEcmtShortTerm);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setBusinessProcess($businessProcess);
        $entity->setIrhpPermitType($irhpPermitType);

        $entity->shouldReceive('getValidityYear')
            ->andReturn($validityYear);

        $this->assertEquals(
            $expectedCandidatePermitCreationMode,
            $entity->getCandidatePermitCreationMode()
        );
    }

    public static function dpGetCandidatePermitCreationMode(): array
    {
        return [
            [
                RefData::BUSINESS_PROCESS_APGG,
                true,
                2019,
                Entity::CANDIDATE_MODE_NONE
            ],
            [
                RefData::BUSINESS_PROCESS_APGG,
                true,
                2020,
                Entity::CANDIDATE_MODE_APGG
            ],
            [
                RefData::BUSINESS_PROCESS_APSG,
                true,
                2020,
                Entity::CANDIDATE_MODE_APSG
            ],
            [
                RefData::BUSINESS_PROCESS_APG,
                false,
                2019,
                Entity::CANDIDATE_MODE_NONE
            ],
            [
                RefData::BUSINESS_PROCESS_APG,
                false,
                2020,
                Entity::CANDIDATE_MODE_NONE
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetPermitUsageList')]
    public function testGetPermitUsageList(mixed $irhpPermitRanges, mixed $expected): void
    {
        $entity = m::mock(Entity::class)->makePartial();

        $entity->shouldReceive('getIrhpPermitRanges')
            ->andReturn($irhpPermitRanges);

        $result = $entity->getPermitUsageList();

        $this->assertSame($expected, $result);
    }

    public static function dpGetPermitUsageList(): array
    {
        $journeyMultiple = (new RefData(RefData::JOURNEY_MULTIPLE))->setDisplayOrder(10);
        $journeySingle = (new RefData(RefData::JOURNEY_SINGLE))->setDisplayOrder(20);

        return [
            [
                new ArrayCollection(
                    [
                        self::createMockRange(false, false, 1, RefData::EMISSIONS_CATEGORY_EURO5_REF, null),
                        self::createMockRange(false, false, 300, RefData::EMISSIONS_CATEGORY_EURO5_REF, $journeyMultiple),
                        self::createMockRange(false, true, 230, RefData::EMISSIONS_CATEGORY_EURO5_REF, $journeyMultiple),
                        self::createMockRange(true, false, 100, RefData::EMISSIONS_CATEGORY_EURO6_REF, $journeySingle),
                        self::createMockRange(true, true, 500, RefData::EMISSIONS_CATEGORY_EURO5_REF, $journeySingle),
                        self::createMockRange(false, false, 420, RefData::EMISSIONS_CATEGORY_EURO6_REF, $journeySingle),
                    ]
                ),
                [
                    10 => $journeyMultiple,
                    20 => $journeySingle,
                ],
            ],
            [
                new ArrayCollection(
                    [
                        self::createMockRange(false, false, 1, RefData::EMISSIONS_CATEGORY_EURO5_REF, $journeyMultiple),
                        self::createMockRange(false, false, 300, RefData::EMISSIONS_CATEGORY_EURO5_REF, $journeyMultiple),
                        self::createMockRange(false, false, 420, RefData::EMISSIONS_CATEGORY_EURO6_REF, $journeyMultiple),
                    ]
                ),
                [
                    10 => $journeyMultiple,
                ],
            ],
            [
                new ArrayCollection(
                    [
                        self::createMockRange(false, false, 1, RefData::EMISSIONS_CATEGORY_EURO5_REF, $journeySingle),
                        self::createMockRange(false, false, 300, RefData::EMISSIONS_CATEGORY_EURO5_REF, $journeySingle),
                        self::createMockRange(false, false, 420, RefData::EMISSIONS_CATEGORY_EURO6_REF, $journeySingle),
                    ]
                ),
                [
                    20 => $journeySingle,
                ],
            ],
            [
                new ArrayCollection(
                    [
                        self::createMockRange(false, false, 1, RefData::EMISSIONS_CATEGORY_EURO5_REF, null),
                        self::createMockRange(false, true, 230, RefData::EMISSIONS_CATEGORY_EURO5_REF, $journeyMultiple),
                        self::createMockRange(true, false, 100, RefData::EMISSIONS_CATEGORY_EURO6_REF, $journeySingle),
                        self::createMockRange(true, true, 500, RefData::EMISSIONS_CATEGORY_EURO5_REF, $journeySingle),
                    ]
                ),
                [],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsMorocco')]
    public function testIsMorocco(mixed $isMorocco): void
    {
        $entity = m::mock(Entity::class)->makePartial();

        $country = m::mock(Country::class);
        $country->shouldReceive('isMorocco')
            ->withNoArgs()
            ->andReturn($isMorocco);

        $entity->setCountry($country);

        $this->assertEquals(
            $isMorocco,
            $entity->isMorocco()
        );
    }

    public static function dpIsMorocco(): array
    {
        return [
            [true],
            [false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetBilateralAnswerSummaryLabelKey')]
    public function testGetBilateralAnswerSummaryLabelKey(mixed $isMorocco, mixed $isMultiStock, mixed $expectedKey): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('isMorocco')
            ->withNoArgs()
            ->andReturn($isMorocco);

        $this->assertEquals(
            $expectedKey,
            $entity->getBilateralAnswerSummaryLabelKey($isMultiStock)
        );
    }

    public static function dpGetBilateralAnswerSummaryLabelKey(): array
    {
        return [
            [true, false, 'permits.page.bilateral.permit-needed'],
            [false, false, 'permits.page.bilateral.which-period-required'],
            [false, true, 'permits.page.bilateral.which-period-required.multi-stock'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetExcludedRestrictedCountryIds')]
    public function testGetExcludedRestrictedCountryIds(
        mixed $isEcmtAnnual,
        mixed $hasEuro5Range,
        mixed $hasEuro6Range,
        mixed $expected
    ): void {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isEcmtAnnual')
            ->andReturn($isEcmtAnnual);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpPermitType($irhpPermitType);

        $entity->shouldReceive('hasEuro5Range')
            ->withNoArgs()
            ->andReturn($hasEuro5Range);
        $entity->shouldReceive('hasEuro6Range')
            ->withNoArgs()
            ->andReturn($hasEuro6Range);

        $this->assertEquals(
            $expected,
            $entity->getExcludedRestrictedCountryIds()
        );
    }

    public static function dpGetExcludedRestrictedCountryIds(): array
    {
        return [
            [
                'isEcmtAnnual' => false,
                'hasEuro5Range' => false,
                'hasEuro6Range' => false,
                'expected' => [],
            ],
            [
                'isEcmtAnnual' => false,
                'hasEuro5Range' => true,
                'hasEuro6Range' => false,
                'expected' => [],
            ],
            [
                'isEcmtAnnual' => false,
                'hasEuro5Range' => false,
                'hasEuro6Range' => true,
                'expected' => [],
            ],
            [
                'isEcmtAnnual' => false,
                'hasEuro5Range' => true,
                'hasEuro6Range' => true,
                'expected' => [],
            ],
            [
                'isEcmtAnnual' => true,
                'hasEuro5Range' => false,
                'hasEuro6Range' => false,
                'expected' => [],
            ],
            [
                'isEcmtAnnual' => true,
                'hasEuro5Range' => true,
                'hasEuro6Range' => false,
                'expected' => [Country::ID_AUSTRIA],
            ],
            [
                'isEcmtAnnual' => true,
                'hasEuro5Range' => false,
                'hasEuro6Range' => true,
                'expected' => [],
            ],
            [
                'isEcmtAnnual' => true,
                'hasEuro5Range' => true,
                'hasEuro6Range' => true,
                'expected' => [],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsCertificateOfRoadworthiness')]
    public function testIsCertificateOfRoadworthiness(mixed $isCertificateOfRoadworthiness): void
    {
        $irhpPermitType = m::mock(IrhpPermitType::class);
        $irhpPermitType->shouldReceive('isCertificateOfRoadworthiness')
            ->withNoArgs()
            ->andReturn($isCertificateOfRoadworthiness);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setIrhpPermitType($irhpPermitType);

        $this->assertEquals(
            $isCertificateOfRoadworthiness,
            $entity->isCertificateOfRoadworthiness()
        );
    }

    public static function dpIsCertificateOfRoadworthiness(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
