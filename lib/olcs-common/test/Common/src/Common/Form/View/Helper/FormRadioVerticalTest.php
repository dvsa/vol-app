<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\View\Helper\FormRadioVertical;
use Common\Test\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\ServiceManager\ServiceManager;
use Common\Test\MocksServicesTrait;
use Mockery\MockInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\Stdlib\ArrayObject;
use Common\Form\Elements\Custom\RadioVertical;
use Hamcrest\Core\IsAnything;
use Hamcrest\Arrays\IsArrayContainingKeyValuePair;
use Hamcrest\Core\IsInstanceOf;
use Hamcrest\Arrays\IsArrayContainingKey;
use Laminas\Form\Element;

/**
 * @see FormRadioVertical
 */
final class FormRadioVerticalTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected const string A_RADIO_NAME = 'RADIO_NAME';

    protected const string RADIO_VERTICAL_TEMPLATE = 'partials/form/radio-vertical';

    protected const string RENDERED_TEMPLATE = 'RENDERED TEMPLATE';

    protected const string RADIO_ELEMENT_VARIABLE_KEY = 'radioElement';

    protected const string ELEMENT_VARIABLE_KEY = 'element';

    protected const string VALUE_OPTIONS_VARIABLE_KEY = 'valueOptions';

    protected const string CONDITIONAL_CONTENT_KEY = 'conditional_content';

    protected const array A_SINGLE_STRING_FORMATTED_VALUE_OPTION = [self::A_VALUE_OPTION_VALUE => self::A_VALUE_OPTION_LABEL];

    protected const string LABEL = 'label';

    protected const string VALUE = 'value';

    protected const string A_VALUE_OPTION_VALUE = 'A VALUE OPTION VALUE';

    protected const string A_VALUE_OPTION_LABEL = 'A VALUE OPTION LABEL';

    protected const string A_VALUE_OPTION_CONDITIONAL_CONTENT = 'A VALUE OPTION CONDITIONAL CONTENT';

    protected const array AN_ARRAY_FORMATTED_VALUE_OPTION_FROM_A_STRING_FORMAT = [
        'value' => 0,
        'label' => self::A_VALUE_OPTION_LABEL,
    ];

    protected const array AN_ARRAY_FORMATTED_VALUE_OPTION_WITH_CONDITIONAL_CONTENT = [
        'value' => self::A_VALUE_OPTION_VALUE,
        'label' => self::A_VALUE_OPTION_LABEL,
        'conditional_content' => self::A_VALUE_OPTION_CONDITIONAL_CONTENT,
    ];

    protected const array AN_ARRAY_FORMATTED_VALUE_OPTION_WITHOUT_CONDITIONAL_CONTENT = [
        'value' => self::A_VALUE_OPTION_VALUE,
        'label' => self::A_VALUE_OPTION_LABEL,
    ];

    protected const string CONDITIONAL_CONTENT_SIBLING_NAME = '0Content';

    protected const string HINT = 'hint';

    protected const string AN_ELEMENT_HINT = 'AN ELEMENT HINT';

    protected const string AN_ELEMENT_LABEL = 'AN ELEMENT LABEL';

    protected const string LABEL_ATTRIBUTES = 'label_attributes';

    protected const array AN_ELEMENT_LABEL_ATTRIBUTES = ['LABEL_ATTRIBUTE_1' => 'LABEL ATTRIBUTES 1 VALUE'];

    /**
     * @var FormRadioVertical
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function renderIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(fn(\Laminas\Form\ElementInterface $element): string => $this->sut->render($element));
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderRendersAFieldset(): void
    {
        // Setup
        $this->setUpSut();
        $fieldset = $this->wrapElementInFieldset($this->setUpRadio());
        $this->renderer()->allows('render')->andReturns(static::RENDERED_TEMPLATE);

        // Execute
        $this->sut->render($fieldset);

        // Assert
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderRendersARadio(): void
    {
        // Setup
        $this->setUpSut();
        $fieldset = $this->setUpRadio();
        $this->renderer()->allows('render')->andReturns(static::RENDERED_TEMPLATE);

        // Execute
        $this->sut->render($fieldset);

        // Assert
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderRendersARadio')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderReturnsARenderedView(): void
    {
        // Setup
        $this->setUpSut();
        $this->renderer()->allows('render')->andReturns(static::RENDERED_TEMPLATE);

        // Execute
        $result = $this->sut->render($this->setUpRadio());

        // Assert
        $this->assertEquals(static::RENDERED_TEMPLATE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderRendersAFieldset')]
    #[\PHPUnit\Framework\Attributes\Depends('renderReturnsARenderedView')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderReturnsARenderedViewWithFieldsetElement(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->wrapElementInFieldset($this->setUpRadio());

        // Expect
        $varsExpectation = IsArrayContainingKeyValuePair::hasKeyValuePair('element', $element);
        $this->renderer()->expects('render')->with(static::RADIO_VERTICAL_TEMPLATE, $varsExpectation)->andReturn(static::RENDERED_TEMPLATE);

        // Execute
        $result = $this->sut->render($element);

        // Assert
        $this->assertEquals(static::RENDERED_TEMPLATE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderReturnsARenderedViewWithFieldsetElement')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderReturnsARenderedViewWithFieldsetElementWhenPassedARadio(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpRadio();

        // Expect
        $this->renderer()->expects('render')->withArgs(static function ($template, $vars) use ($element) {
            if (! is_array($vars) || !isset($vars[static::ELEMENT_VARIABLE_KEY]) || ! ($vars[static::ELEMENT_VARIABLE_KEY] instanceof Fieldset)) {
                return false;
            }
            return array_values($vars[static::ELEMENT_VARIABLE_KEY]->getElements())[0] === $element;
        })->andReturn(static::RENDERED_TEMPLATE);

        // Execute
        $result = $this->sut->render($element);

        // Assert
        $this->assertEquals(static::RENDERED_TEMPLATE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderReturnsARenderedViewWithFieldsetElement')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderReturnsARenderedViewWithFieldsetElementWhenPassedARadioWithRadioElementOptionSetToTheElementsName(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpRadio();

        // Expect
        $this->renderer()->expects('render')->withArgs(static function ($template, $vars) use ($element) {
            $fieldset = $vars[static::ELEMENT_VARIABLE_KEY];
            \PHPUnit\Framework\Assert::assertInstanceOf(Fieldset::class, $fieldset, 'Expected instance of Fieldset');
            return $fieldset->getOption('radio-element') === $element->getName();
        })->andReturn(static::RENDERED_TEMPLATE);

        // Execute
        $result = $this->sut->render($element);

        // Assert
        $this->assertEquals(static::RENDERED_TEMPLATE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderRendersARadio')]
    #[\PHPUnit\Framework\Attributes\Depends('renderReturnsARenderedView')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderReturnsARenderedViewWithRadioElement(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpRadio();

        // Expect
        $varsExpectation = IsArrayContainingKeyValuePair::hasKeyValuePair(static::RADIO_ELEMENT_VARIABLE_KEY, IsInstanceOf::anInstanceOf(RadioVertical::class));
        $this->renderer()->expects('render')->with(IsAnything::anything(), $varsExpectation)->andReturn(static::RENDERED_TEMPLATE);

        // Execute
        $result = $this->sut->render($element);

        // Assert
        $this->assertEquals(static::RENDERED_TEMPLATE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderRendersARadio')]
    #[\PHPUnit\Framework\Attributes\Depends('renderReturnsARenderedView')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderReturnsARenderedViewWithValueOptions(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpRadio();

        // Expect
        $varsExpectation = IsArrayContainingKey::hasKeyInArray(static::VALUE_OPTIONS_VARIABLE_KEY);
        $this->renderer()->expects('render')->with(IsAnything::anything(), $varsExpectation)->andReturn(static::RENDERED_TEMPLATE);

        // Execute
        $result = $this->sut->render($element);

        // Assert
        $this->assertEquals(static::RENDERED_TEMPLATE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderRendersARadio')]
    #[\PHPUnit\Framework\Attributes\Depends('renderReturnsARenderedView')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderReturnsARenderedViewWithValueOptionsFromAStringFormatToBeAnArray(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpRadio();
        $element->setValueOptions(static::A_SINGLE_STRING_FORMATTED_VALUE_OPTION);

        // Expect
        $this->renderer()->expects('render')->withArgs(function ($template, $vars) {
            $valueOption = $vars[static::VALUE_OPTIONS_VARIABLE_KEY][static::A_VALUE_OPTION_VALUE];
            $this->assertEquals(static::A_VALUE_OPTION_VALUE, $valueOption[static::VALUE]);
            $this->assertEquals(static::A_VALUE_OPTION_LABEL, $valueOption[static::LABEL]);
            return true;
        })->andReturn(static::RENDERED_TEMPLATE);

        // Execute
        $result = $this->sut->render($element);

        // Assert
        $this->assertEquals(static::RENDERED_TEMPLATE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderRendersARadio')]
    #[\PHPUnit\Framework\Attributes\Depends('renderReturnsARenderedView')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderReturnsARenderedViewWithValueOptionsFromAnArrayFormatToBeAnArrayIncludingConditionalContentFromAValueOptionConfiguration(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpRadio();
        $element->setValueOptions([static::AN_ARRAY_FORMATTED_VALUE_OPTION_WITH_CONDITIONAL_CONTENT]);

        // Expect
        $this->renderer()->expects('render')->withArgs(function ($template, $vars) {
            $valueOption = $vars[static::VALUE_OPTIONS_VARIABLE_KEY][0];
            $this->assertEquals(static::A_VALUE_OPTION_VALUE, $valueOption[static::VALUE]);
            $this->assertEquals(static::A_VALUE_OPTION_LABEL, $valueOption[static::LABEL]);
            $this->assertSame(static::A_VALUE_OPTION_CONDITIONAL_CONTENT, $valueOption[static::CONDITIONAL_CONTENT_KEY]);
            return true;
        })->andReturn(static::RENDERED_TEMPLATE);

        // Execute
        $result = $this->sut->render($element);

        // Assert
        $this->assertEquals(static::RENDERED_TEMPLATE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderRendersARadio')]
    #[\PHPUnit\Framework\Attributes\Depends('renderReturnsARenderedView')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderReturnsARenderedViewWithValueOptionsFromAnArrayFormatToBeAnArrayIncludingConditionalContentFromASibling(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpRadio();
        $element->setValueOptions([static::AN_ARRAY_FORMATTED_VALUE_OPTION_WITHOUT_CONDITIONAL_CONTENT]);

        $fieldset = $this->wrapElementInFieldset($element);

        $conditionContentSibling = new Element(static::CONDITIONAL_CONTENT_SIBLING_NAME);
        $fieldset->add($conditionContentSibling);

        // Expect
        $this->renderer()->expects('render')->withArgs(function ($template, $vars) use ($conditionContentSibling) {
            $valueOption = $vars[static::VALUE_OPTIONS_VARIABLE_KEY][0];
            $this->assertEquals(static::A_VALUE_OPTION_VALUE, $valueOption[static::VALUE]);
            $this->assertEquals(static::A_VALUE_OPTION_LABEL, $valueOption[static::LABEL]);
            $this->assertSame($conditionContentSibling, $valueOption[static::CONDITIONAL_CONTENT_KEY]);
            return true;
        })->andReturn(static::RENDERED_TEMPLATE);

        // Execute
        $result = $this->sut->render($fieldset);

        // Assert
        $this->assertEquals(static::RENDERED_TEMPLATE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('renderReturnsARenderedView')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function renderReturnsRenderedViewWithLabelAndHint(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpRadio();
        $element->setLabel(static::AN_ELEMENT_LABEL);
        $element->setLabelAttributes(static::AN_ELEMENT_LABEL_ATTRIBUTES);
        $element->setOption(static::HINT, static::AN_ELEMENT_HINT);

        // Assert
        $this->renderer()->expects('render')->withArgs(function ($template, $variables) {
            $this->assertEquals(static::AN_ELEMENT_LABEL, $variables[static::LABEL] ?? null);
            $this->assertEquals(static::AN_ELEMENT_LABEL_ATTRIBUTES, $variables[static::LABEL_ATTRIBUTES] ?? null);
            $this->assertEquals(static::AN_ELEMENT_HINT, $variables[static::HINT] ?? null);
            return true;
        })->andReturn(self::RENDERED_TEMPLATE);

        $this->sut->render($element);
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->setUpServiceManager();
    }

    public function setUpSut(): void
    {
        $this->sut = new FormRadioVertical();
        $this->sut->setView($this->renderer());
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $serviceManager->setService(PhpRenderer::class, $this->renderer());
        return $serviceManager;
    }

    protected function renderer(): MockInterface
    {
        if (! $this->serviceManager->has(PhpRenderer::class)) {
            $instance = $this->setUpMockService(PhpRenderer::class);
            $instance->allows('vars')->andReturn(new ArrayObject());
            $this->serviceManager->setService(PhpRenderer::class, $instance);
        }

        return $this->serviceManager->get(PhpRenderer::class);
    }

    protected function setUpRadio(): RadioVertical
    {
        return new RadioVertical(static::A_RADIO_NAME);
    }

    protected function wrapElementInFieldset(RadioVertical $radio): Fieldset
    {
        $instance = new Fieldset();
        $instance->add($radio);
        $instance->setOption('radio-element', $radio->getName());
        return $instance;
    }
}
