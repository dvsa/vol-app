<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\EcmtRemoval;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThreshold;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThresholdGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\EcmtRemoval\PermitStartDateGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitStartDateGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PermitStartDateGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);

        $dateWithThreshold = m::mock(DateWithThreshold::class);

        $dateWithThresholdGenerator = m::mock(DateWithThresholdGenerator::class);
        $dateWithThresholdGenerator->shouldReceive('generate')
            ->with($elementGeneratorContext, 'P60D')
            ->once()
            ->andReturn($dateWithThreshold);

        $permitStartDateGenerator = new PermitStartDateGenerator($dateWithThresholdGenerator);

        $this->assertSame(
            $dateWithThreshold,
            $permitStartDateGenerator->generate($elementGeneratorContext)
        );
    }
}
