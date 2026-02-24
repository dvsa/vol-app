<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsMorocco;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsMoroccoFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsMoroccoGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsMoroccoGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsMoroccoGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $label = 'period.name.key';
        $value = 77;

        $filteredBilateralRequired = [
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => $value
        ];

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getPeriodNameKey')
            ->withNoArgs()
            ->andReturn($label);
        $irhpPermitApplication->shouldReceive('getFilteredBilateralRequired')
            ->withNoArgs()
            ->andReturn($filteredBilateralRequired);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $noOfPermitsMorocco = m::mock(NoOfPermitsMorocco::class);

        $noOfPermitsMoroccoFactory = m::mock(NoOfPermitsMoroccoFactory::class);
        $noOfPermitsMoroccoFactory->shouldReceive('create')
            ->with($label, $value)
            ->andReturn($noOfPermitsMorocco);

        $noOfPermitsMoroccoGenerator = new NoOfPermitsMoroccoGenerator($noOfPermitsMoroccoFactory);

        $this->assertSame(
            $noOfPermitsMorocco,
            $noOfPermitsMoroccoGenerator->generate($elementGeneratorContext)
        );
    }
}
