<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\ThirdCountry;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\ThirdCountryFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\ThirdCountryGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ThirdCountryGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ThirdCountryGeneratorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerate')]
    public function testGenerate(mixed $answerValue, mixed $expectedYesNo): void
    {
        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($answerValue);

        $thirdCountry = m::mock(ThirdCountry::class);

        $thirdCountryFactory = m::mock(ThirdCountryFactory::class);
        $thirdCountryFactory->shouldReceive('create')
            ->with($expectedYesNo)
            ->andReturn($thirdCountry);

        $thirdCountryGenerator = new ThirdCountryGenerator($thirdCountryFactory);

        $this->assertSame(
            $thirdCountry,
            $thirdCountryGenerator->generate($elementGeneratorContext)
        );
    }

    public static function dpGenerate(): array
    {
        return [
            ['string_value', 'Y'],
            [null, null],
        ];
    }
}
