<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\EmissionsStandards;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\EmissionsStandardsFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\EmissionsStandardsGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EmissionsStandardsGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EmissionsStandardsGeneratorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerate')]
    public function testGenerate(mixed $answerValue, mixed $expectedYesNo): void
    {
        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($answerValue);

        $emissionsStandards = m::mock(EmissionsStandards::class);

        $emissionsStandardsFactory = m::mock(EmissionsStandardsFactory::class);
        $emissionsStandardsFactory->shouldReceive('create')
            ->with($expectedYesNo)
            ->andReturn($emissionsStandards);

        $emissionsStandardsGenerator = new EmissionsStandardsGenerator($emissionsStandardsFactory);

        $this->assertSame(
            $emissionsStandards,
            $emissionsStandardsGenerator->generate($elementGeneratorContext)
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
