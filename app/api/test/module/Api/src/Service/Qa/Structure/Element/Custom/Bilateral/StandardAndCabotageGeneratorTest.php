<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\StandardAndCabotage;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\StandardAndCabotageFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\StandardAndCabotageGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StandardAndCabotageGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StandardAndCabotageGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $answerValue = 'standard_and_cabotage_value';

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($answerValue);
        $standardAndCabotage = m::mock(StandardAndCabotage::class);

        $standardAndCabotageFactory = m::mock(StandardAndCabotageFactory::class);
        $standardAndCabotageFactory->shouldReceive('create')
            ->with($answerValue)
            ->andReturn($standardAndCabotage);

        $standardAndCabotageGenerator = new StandardAndCabotageGenerator($standardAndCabotageFactory);

        $this->assertSame(
            $standardAndCabotage,
            $standardAndCabotageGenerator->generate($elementGeneratorContext)
        );
    }
}
