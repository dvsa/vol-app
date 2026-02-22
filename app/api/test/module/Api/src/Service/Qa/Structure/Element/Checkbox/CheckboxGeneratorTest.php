<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Checkbox;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox\Checkbox;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox\CheckboxFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox\CheckboxGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CheckboxGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CheckboxGeneratorTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGenerate')]
    public function testGenerate(mixed $answerValue, mixed $expectedChecked): void
    {
        $labelOptions = [
            'key' => 'labelKey',
            'parameters' => [
                'labelParameter1',
                'labelParameter2'
            ]
        ];

        $notCheckedMessageOptions = [
            'key' => 'notCheckedMessageKey',
            'parameters' => [
                'notCheckedMessageParameter1',
                'notCheckedMessageParameter2'
            ]
        ];

        $options = [
            'label' => $labelOptions,
            'notCheckedMessage' => $notCheckedMessageOptions
        ];

        $labelTranslateableText = m::mock(TranslateableText::class);

        $notCheckedMessageTranslateableText = m::mock(TranslateableText::class);

        $checkbox = m::mock(Checkbox::class);

        $checkboxFactory = m::mock(CheckboxFactory::class);
        $checkboxFactory->shouldReceive('create')
            ->with($labelTranslateableText, $notCheckedMessageTranslateableText, $expectedChecked)
            ->andReturn($checkbox);

        $applicationStepEntity = m::mock(ApplicationStepEntity::class);
        $applicationStepEntity->shouldReceive('getDecodedOptionSource')
            ->andReturn($options);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getApplicationStepEntity')
            ->andReturn($applicationStepEntity);
        $elementGeneratorContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($answerValue);

        $translateableTextGenerator = m::mock(TranslateableTextGenerator::class);
        $translateableTextGenerator->shouldReceive('generate')
            ->with($labelOptions)
            ->andReturn($labelTranslateableText);
        $translateableTextGenerator->shouldReceive('generate')
            ->with($notCheckedMessageOptions)
            ->andReturn($notCheckedMessageTranslateableText);

        $checkboxGenerator = new CheckboxGenerator($checkboxFactory, $translateableTextGenerator);

        $this->assertSame(
            $checkbox,
            $checkboxGenerator->generate($elementGeneratorContext)
        );
    }

    public static function dpTestGenerate(): array
    {
        return [
            [null, false],
            [false, false],
            [true, true],
        ];
    }
}
