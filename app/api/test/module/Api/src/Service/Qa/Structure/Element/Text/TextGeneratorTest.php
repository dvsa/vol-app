<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Text;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Text;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\TextFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\TextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TextGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TextGeneratorTest extends MockeryTestCase
{
    private $answerValue;

    private $hintOption;

    private $labelOption;

    private $applicationStepEntity;

    private $elementGeneratorContext;

    private $labelTranslateableText;

    private $hintTranslateableText;

    private $text;

    private $textFactory;

    private $translateableTextGenerator;

    private $textGenerator;

    public function setUp(): void
    {
        $this->answerValue = '456';

        $this->hintOption = [
            'key' => 'hintKey',
            'parameters' => [
                'hintParameter1',
                'hintParameter2'
            ]
        ];

        $this->labelOption = [
            'key' => 'labelKey',
            'parameters' => [
                'labelParameter1',
                'labelParameter2'
            ]
        ];

        $this->applicationStepEntity = m::mock(ApplicationStepEntity::class);

        $this->elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $this->elementGeneratorContext->shouldReceive('getApplicationStepEntity')
            ->andReturn($this->applicationStepEntity);
        $this->elementGeneratorContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($this->answerValue);

        $this->labelTranslateableText = m::mock(TranslateableText::class);
        $this->hintTranslateableText = m::mock(TranslateableText::class);

        $this->text = m::mock(Text::class);
        $this->textFactory = m::mock(TextFactory::class);

        $this->translateableTextGenerator = m::mock(TranslateableTextGenerator::class);
        $this->translateableTextGenerator->shouldReceive('generate')
            ->with($this->labelOption)
            ->andReturn($this->labelTranslateableText);
        $this->translateableTextGenerator->shouldReceive('generate')
            ->with($this->hintOption)
            ->andReturn($this->hintTranslateableText);

        $this->textGenerator = new TextGenerator(
            $this->textFactory,
            $this->translateableTextGenerator
        );
    }

    public function testGenerate(): void
    {
        $this->textFactory->shouldReceive('create')
            ->with($this->answerValue, $this->labelTranslateableText, $this->hintTranslateableText)
            ->andReturn($this->text);

        $decodedOptionSource = [
            'hint' => $this->hintOption,
            'label' => $this->labelOption
        ];

        $this->applicationStepEntity->shouldReceive('getDecodedOptionSource')
            ->andReturn($decodedOptionSource);

        $this->assertSame(
            $this->text,
            $this->textGenerator->generate($this->elementGeneratorContext)
        );
    }

    public function testGenerateWithLabelOnly(): void
    {
        $this->textFactory->shouldReceive('create')
            ->with($this->answerValue, $this->labelTranslateableText, null)
            ->andReturn($this->text);

        $decodedOptionSource = [
            'label' => $this->labelOption
        ];

        $this->applicationStepEntity->shouldReceive('getDecodedOptionSource')
            ->andReturn($decodedOptionSource);

        $this->assertSame(
            $this->text,
            $this->textGenerator->generate($this->elementGeneratorContext)
        );
    }

    public function testGenerateWithHintOnly(): void
    {
        $this->textFactory->shouldReceive('create')
            ->with($this->answerValue, null, $this->hintTranslateableText)
            ->andReturn($this->text);

        $decodedOptionSource = [
            'hint' => $this->hintOption
        ];

        $this->applicationStepEntity->shouldReceive('getDecodedOptionSource')
            ->andReturn($decodedOptionSource);

        $this->assertSame(
            $this->text,
            $this->textGenerator->generate($this->elementGeneratorContext)
        );
    }
}
