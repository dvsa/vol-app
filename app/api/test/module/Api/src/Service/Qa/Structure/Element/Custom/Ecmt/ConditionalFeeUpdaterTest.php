<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\ConditionalFeeUpdater;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\FeeUpdater;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ConditionalFeeUpdaterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ConditionalFeeUpdaterTest extends MockeryTestCase
{
    private $feeUpdater;

    private $irhpPermitApplication;

    private $irhpApplication;

    private $oldTotal;

    private $conditionalFeeUpdater;

    public function setUp(): void
    {
        $this->feeUpdater = m::mock(FeeUpdater::class);

        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->irhpApplication = m::mock(IrhpApplication::class);
        $this->irhpApplication->shouldReceive('getFirstIrhpPermitApplication')
            ->andReturn($this->irhpPermitApplication);

        $this->oldTotal = 5;

        $this->conditionalFeeUpdater = new ConditionalFeeUpdater($this->feeUpdater);
    }

    public function testCallFeeUpdater(): void
    {
        $newTotal = 7;

        $this->irhpPermitApplication->shouldReceive('getTotalEmissionsCategoryPermitsRequired')
            ->andReturn($newTotal);

        $this->feeUpdater->shouldReceive('updateFees')
            ->with($this->irhpApplication, $newTotal)
            ->once();

        $this->conditionalFeeUpdater->updateFees($this->irhpApplication, $this->oldTotal);
    }

    public function testDontCallFeeUpdater(): void
    {
        $this->irhpPermitApplication->shouldReceive('getTotalEmissionsCategoryPermitsRequired')
            ->andReturn($this->oldTotal);

        $this->feeUpdater->shouldReceive('updateFees')
            ->never();

        $this->conditionalFeeUpdater->updateFees($this->irhpApplication, $this->oldTotal);
    }
}
