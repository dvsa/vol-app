<?php

namespace CommonTest\Service\Qa;

use Common\Service\Qa\TextFactory;
use Common\Service\Qa\TextFieldsetPopulator;
use Common\Service\Qa\TranslateableTextHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * TextFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TextFieldsetPopulatorTest extends MockeryTestCase
{
    private $text;

    private $translateableTextHandler;

    private $form;

    private $fieldset;

    private $textFieldsetPopulator;

    private $labelOptions = [
        'key' => 'labelKey',
        'parameters' => [
            'labelParam1',
            'labelParam2'
        ],
    ];

    private $translatedLabel = 'translatedLabel';

    private $hintOptions = [
        'key' => 'hintKey',
        'parameters' => [
            'hintParam1',
            'hintParam2'
        ],
    ];

    private $translatedHint = 'translatedHint';

    private $value = 'value';

    #[\Override]
    protected function setUp(): void
    {
        $this->text = m::mock(Text::class);
        $this->text->shouldReceive('setValue')
            ->with($this->value)
            ->once();
        $this->text->shouldReceive('setAttribute')
            ->with('class', 'govuk-input govuk-input--width-10')
            ->once();

        $textFactory = m::mock(TextFactory::class);
        $textFactory->shouldReceive('create')
            ->with('qaElement')
            ->once()
            ->andReturn($this->text);

        $this->translateableTextHandler = m::mock(TranslateableTextHandler::class);

        $this->form = m::mock(Form::class);

        $this->fieldset = m::mock(Fieldset::class);
        $this->fieldset->shouldReceive('add')
            ->with($this->text)
            ->once();

        $this->textFieldsetPopulator = new TextFieldsetPopulator(
            $textFactory,
            $this->translateableTextHandler
        );
    }

    public function testPopulateWithLabelAndHint(): void
    {
        $options = [
            'label' => $this->labelOptions,
            'hint' => $this->hintOptions,
            'value' => $this->value
        ];

        $this->translateableTextHandler->shouldReceive('translate')
            ->with($this->labelOptions)
            ->andReturn($this->translatedLabel);

        $this->translateableTextHandler->shouldReceive('translate')
            ->with($this->hintOptions)
            ->andReturn($this->translatedHint);

        $this->text->shouldReceive('setLabel')
            ->with($this->translatedLabel)
            ->once();

        $expectedTextOptions = [
            'hint' => $this->translatedHint,
            'hint-class' => 'govuk-hint'
        ];

        $this->text->shouldReceive('setOptions')
            ->with($expectedTextOptions)
            ->once();

        $this->textFieldsetPopulator->populate($this->form, $this->fieldset, $options);
    }

    public function testPopulateWithLabelOnly(): void
    {
        $options = [
            'label' => $this->labelOptions,
            'value' => $this->value
        ];

        $this->translateableTextHandler->shouldReceive('translate')
            ->with($this->labelOptions)
            ->andReturn($this->translatedLabel);

        $this->text->shouldReceive('setLabel')
            ->with($this->translatedLabel)
            ->once();

        $this->textFieldsetPopulator->populate($this->form, $this->fieldset, $options);
    }

    public function testPopulateWithHintOnly(): void
    {
        $options = [
            'hint' => $this->hintOptions,
            'value' => $this->value
        ];

        $this->translateableTextHandler->shouldReceive('translate')
            ->with($this->hintOptions)
            ->andReturn($this->translatedHint);

        $expectedTextOptions = [
            'hint' => $this->translatedHint,
            'hint-class' => 'govuk-hint'
        ];

        $this->text->shouldReceive('setOptions')
            ->with($expectedTextOptions)
            ->once();

        $this->textFieldsetPopulator->populate($this->form, $this->fieldset, $options);
    }
}
