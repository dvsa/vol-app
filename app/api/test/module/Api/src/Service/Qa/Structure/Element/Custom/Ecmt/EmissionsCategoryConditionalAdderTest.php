<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Availability\EmissionsCategoryAvailabilityCounter;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\EmissionsCategory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\EmissionsCategoryConditionalAdder;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\EmissionsCategoryFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\NoOfPermits;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EmissionsCategoryConditionalAdderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EmissionsCategoryConditionalAdderTest extends MockeryTestCase
{
    public const TYPE = 'euro5';
    public const VALUE = 45;
    public const EMISSIONS_CATEGORY_ID = RefData::EMISSIONS_CATEGORY_EURO5_REF;
    public const STOCK_ID = 28;

    private $emissionsCategoryFactory;

    private $emissionsCategoryAvailabilityCounter;

    private $emissionsCategoryConditionalAdder;

    private $noOfPermits;

    public function setUp(): void
    {
        $this->emissionsCategoryFactory = m::mock(EmissionsCategoryFactory::class);
        $this->emissionsCategoryAvailabilityCounter = m::mock(EmissionsCategoryAvailabilityCounter::class);

        $this->noOfPermits = m::mock(NoOfPermits::class);

        $this->emissionsCategoryConditionalAdder = new EmissionsCategoryConditionalAdder(
            $this->emissionsCategoryFactory,
            $this->emissionsCategoryAvailabilityCounter
        );
    }

    public function testAddWhenRangesExistAndFreePermits(): void
    {
        $permitsRemaining = 3;

        $this->emissionsCategoryAvailabilityCounter->shouldReceive('getCount')
            ->with(self::STOCK_ID, self::EMISSIONS_CATEGORY_ID)
            ->andReturn($permitsRemaining);

        $emissionsCategory = m::mock(EmissionsCategory::class);

        $this->emissionsCategoryFactory->shouldReceive('create')
            ->with(self::TYPE, self::VALUE, $permitsRemaining)
            ->once()
            ->andReturn($emissionsCategory);

        $this->noOfPermits->shouldReceive('addEmissionsCategory')
            ->with($emissionsCategory)
            ->once();

        $this->emissionsCategoryConditionalAdder->addIfRequired(
            $this->noOfPermits,
            self::TYPE,
            self::VALUE,
            self::EMISSIONS_CATEGORY_ID,
            self::STOCK_ID
        );
    }

    public function testNoAddWhenNoFreePermitsInRanges(): void
    {
        $this->emissionsCategoryAvailabilityCounter->shouldReceive('getCount')
            ->with(self::STOCK_ID, self::EMISSIONS_CATEGORY_ID)
            ->andReturn(0);

        $this->noOfPermits->shouldReceive('addEmissionsCategory')
            ->never();

        $this->emissionsCategoryConditionalAdder->addIfRequired(
            $this->noOfPermits,
            self::TYPE,
            self::VALUE,
            self::EMISSIONS_CATEGORY_ID,
            self::STOCK_ID
        );
    }
}
