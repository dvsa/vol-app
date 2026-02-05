<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Text\Custom;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\TotAuthVehiclesGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Text;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\TextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextParameter;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TotAuthVehiclesGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TotAuthVehiclesGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $totAuthVehicles = 14;

        $validator = m::mock(Validator::class);
        $validator->shouldReceive('setParameter')
            ->with('max', $totAuthVehicles)
            ->once();

        $validatorList = m::mock(ValidatorList::class);
        $validatorList->shouldReceive('getValidatorByRule')
            ->with('LessThan')
            ->andReturn($validator);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getQaEntity->getLicence->getTotAuthVehicles')
            ->andReturn($totAuthVehicles);
        $elementGeneratorContext->shouldReceive('getValidatorList')
            ->andReturn($validatorList);

        $hintTranslateableTextParameter = m::mock(TranslateableTextParameter::class);
        $hintTranslateableTextParameter->shouldReceive('setValue')
            ->with($totAuthVehicles)
            ->once();

        $hintTranslateableText = m::mock(TranslateableText::class);
        $hintTranslateableText->shouldReceive('getParameter')
            ->with(0)
            ->andReturn($hintTranslateableTextParameter);

        $text = m::mock(Text::class);
        $text->shouldReceive('getHint')
            ->andReturn($hintTranslateableText);

        $textGenerator = m::mock(TextGenerator::class);
        $textGenerator->shouldReceive('generate')
            ->with($elementGeneratorContext)
            ->andReturn($text);

        $totAuthVehiclesGenerator = new TotAuthVehiclesGenerator($textGenerator);

        $this->assertSame(
            $text,
            $totAuthVehiclesGenerator->generate($elementGeneratorContext)
        );
    }
}
