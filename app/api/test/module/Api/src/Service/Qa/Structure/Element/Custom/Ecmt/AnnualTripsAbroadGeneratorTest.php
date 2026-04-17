<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\AnnualTripsAbroad;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\AnnualTripsAbroadFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\AnnualTripsAbroadGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Text;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\TextGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * AnnualTripsAbroadGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnnualTripsAbroadGeneratorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTrueFalse')]
    public function testGenerate(mixed $isNi): void
    {
        $intensityWarningThreshold = 47;

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getIntensityOfUseWarningThreshold')
            ->andReturn($intensityWarningThreshold);
        $irhpApplication->shouldReceive('getLicence->isNi')
            ->andReturn($isNi);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getQaEntity')
            ->andReturn($irhpApplication);

        $text = m::mock(Text::class);

        $textGenerator = m::mock(TextGenerator::class);
        $textGenerator->shouldReceive('generate')
            ->with($elementGeneratorContext)
            ->once()
            ->andReturn($text);

        $annualTripsAbroad = m::mock(AnnualTripsAbroad::class);

        $annualTripsAbroadFactory = m::mock(AnnualTripsAbroadFactory::class);
        $annualTripsAbroadFactory->shouldReceive('create')
            ->with($intensityWarningThreshold, $isNi, $text)
            ->once()
            ->andReturn($annualTripsAbroad);

        $annualTripsAbroadGenerator = new AnnualTripsAbroadGenerator($annualTripsAbroadFactory, $textGenerator);

        $this->assertSame(
            $annualTripsAbroad,
            $annualTripsAbroadGenerator->generate($elementGeneratorContext)
        );
    }

    public static function dpTrueFalse(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
