<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\Elements\Types\AttachFilesButton;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatterFactory;
use Common\Form\View\Helper as CommonHelper;
use Common\Form\View\Helper\FormRow;
use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Laminas\Form\Element;
use Laminas\Form\View\Helper as LaminasHelper;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\I18n\View\Helper\Translate;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;
use Laminas\View\Helper\Doctype;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mockery\MockInterface;
use Mockery as m;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Form\View\Helper\FormRow::class)]
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class FormRowTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected const string VALIDATOR_MANAGER = 'ValidatorManager';

    protected const string AN_ELEMENT_NAME = 'AN ELEMENT NAME';

    protected const string AN_EMPTY_FIELD = '<div class="field "></div>';

    protected const string AN_EMPTY_STRING = '';

    /**
     * @var FormRow|null
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, '__invoke']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeClassicNoLabel(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement('Text', ['label' => null]);
        $element->setMessages(['Message']);

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression(
            '/^<div class="validation-wrapper"><div class="field ">(.*)<\/div><\/div>$/',
            $result
        );
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeClassicWithId(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement();
        $element->setAttribute('id', 'test');

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^<div class="field "><label(.*)>(.*)<\/label>(.*)<\/div>$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeClassicWithPartial(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement();

        // Execute
        $result = $this->sut->__invoke($element, null, null, 'partial');

        // Assert
        $this->assertMatchesRegularExpression('/^<div class="field "><\/div>$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Group('questionable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersActionButton(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(\Common\Form\Elements\InputFilters\ActionButton::class);

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Group('questionable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersNoRender(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(\Common\Form\Elements\InputFilters\NoRender::class);

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersTable(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(\Common\Form\Elements\Types\Table::class);
        $mockTable = $this->getMockBuilder(\Common\Service\Table\TableBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();
        $mockTable
            ->method('render')
            ->willReturn('<table></table>');
        $element->setTable($mockTable);

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^<div class="field "><table><\/table><\/div>$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersSingleCheckbox(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(\Common\Form\Elements\InputFilters\SingleCheckbox::class);
        $element->setLabelOption('always_wrap', true);

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^<div class="field "><label>(.*)<\/label>(.*)<\/div>$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersCheckbox(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(
            \Common\Form\Elements\InputFilters\Checkbox::class,
            [
                'label_options' => [
                    'label_position' => 'append',
                ],
            ]
        );

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^<div class="field "><label for="test">(.*)<\/label>(.*)<\/div>$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendesrRadioNoAttribute(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement('Radio');

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^<fieldset><legend>(.*)<\/legend><\/fieldset>$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersRadioLegendAttribute(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(
            'Radio',
            [
                "legend-attributes" => [
                    'class' => 'A_CLASS',
                ],
            ]
        );

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^<fieldset><legend class="A_CLASS">(.*)<\/legend><\/fieldset>$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersRadioWithDataGroupAttribute(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(
            'Radio',
            [
                "fieldset-data-group" => 'data-group',
            ]
        );

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^<fieldset data-group="data-group"><legend>(.*)<\/legend><\/fieldset>$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersRadioWithInlineAttribute(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(
            'Radio',
            [
                "fieldset-attributes" => [
                    "class"      => "inline",
                    "data-group" => "data-group",
                ],
            ]
        );

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^<fieldset class="inline" data-group="data-group"><legend>(.*)<\/legend><\/fieldset>$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Group('formRow')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersCsrfElement(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(
            'Csrf',
            [
                'csrf_options' => [
                    'messageTemplates' => [
                        'notSame' => 'csrf-message',
                    ],
                    'timeout'          => 600,
                ],
                'name'         => 'security',
            ],
            ['id' => 'security']
        );

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertEquals('<label for="security">Label</label>', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Group('formRow')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersVisuallyHiddenElement(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(
            'Text',
            [
                'name' => 'text',
            ],
            ['class' => 'govuk-visually-hidden']
        );

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^<div class="field govuk-visually-hidden">(.*)<\/div>$/', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Group('formRow')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersHiddenElement(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(
            'Hidden',
            [
                'name' => 'hidden',
            ],
            ['class' => 'govuk-visually-hidden']
        );

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertEquals('<label for="test">Label</label>', $result);
    }

    public function renderRadioProvider(): array
    {
        return [
            [null],
            [["class" => ""]],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersWithRenderAsFieldset(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement();
        $element->setOption('render_as_fieldset', true);

        // Execute
        $markup = $this->sut->__invoke($element);

        // Assert
        $this->assertEquals('<fieldset class="fieldset--primary"><legend>Label</legend><p class="hint">Hint</p></fieldset>', $markup);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersReadonlyElement(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(
            \Common\Form\Elements\Types\ReadonlyElement::class,
            [
                'name'  => 'readonly',
                'label' => 'Foo',
            ],
            []
        );
        $element->setValue('Bar');

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertEquals('<div class="field read-only "><p>Foo<br><b>Bar</b></p></div>', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersDateSelectElement(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(
            'DateSelect',
            [
                'name'         => 'date',
                'label'        => 'Foo',
                'label-suffix' => 'unit_LabelSfx',
            ],
            []
        );

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertEquals('<div class="field "><fieldset class="date"><legend>Foo unit_LabelSfx</legend><p class="hint">Hint</p></fieldset></div>', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersDateSelectWithFieldsetClass(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(
            'DateSelect',
            [
                'name'          => 'date',
                'label'         => 'Foo',
                'fieldsetClass' => 'user',
                'hint'          => null,
            ],
            []
        );

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertEquals('<div class="field "><fieldset class="user"><legend>Foo</legend></fieldset></div>', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersDateTimeSelectElement(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement(
            'DateTimeSelect',
            [
                'name'  => 'date',
                'label' => 'Foo',
            ],
            []
        );

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertEquals('<div class="field "><fieldset class="date"><legend>Foo</legend><p class="hint">Hint</p></fieldset></div>', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersAttachFilesButtonElement(): void
    {
        // Setup
        $this->setUpSut();
        $element = new AttachFilesButton('files');
        $element->setOptions(
            [
                'type'  => AttachFilesButton::class,
                'label' => 'Label',
                'hint'  => 'Hint',
            ]
        );
        $element->setAttributes(
            ['class' => 'fileUploadTest']
        );

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertEquals('<div class=""><label for="files">Label</label></div>', $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Group('questionable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRendersSingleRadio(): void
    {
        // Setup
        $this->setUpSut();
        $element = $this->setUpElement('Radio', ['single-radio' => true]);

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertMatchesRegularExpression('/^$/', $result);
    }

    public static function allowWrapValuesThatCauseMarkupToBeWrappedDataProvider(): \Iterator
    {
        yield 'an empty string' => [''];
        yield 'a string true' => ['true'];
        yield 'a string false' => ['false'];
        yield 'a zero integer' => [0];
        yield 'a zero float' => [0.0];
        yield 'a integer string with the value one' => ['1'];
        yield 'a integer string with the value zero' => ['0'];
        yield 'an empty array' => [[]];
        yield 'an empty object' => [(object) []];
        yield 'null' => [null];
        yield 'true' => [true];
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\DataProvider('allowWrapValuesThatCauseMarkupToBeWrappedDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeWrapsMarkupInAField($allowWrapAttributeValue): void
    {
        // Setup
        $this->setUpSut();
        $element = new Element(static::AN_ELEMENT_NAME);
        $element->setAttribute('allowWrap', $allowWrapAttributeValue);

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertEquals(static::AN_EMPTY_FIELD, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeDoesNotWrapMarkupInAFieldIfAllowWrapAttributeIsFalse(): void
    {
        // Setup
        $this->setUpSut();
        $element = new Element(static::AN_ELEMENT_NAME);
        $element->setAttribute('allowWrap', false);

        // Execute
        $result = $this->sut->__invoke($element);

        // Assert
        $this->assertEquals(static::AN_EMPTY_STRING, $result);
    }

    private function setUpElement(string $type = 'Text', array $options = [], array $attributes = ['class' => 'class']): Element
    {
        if (!str_contains($type, '\\')) {
            $type = '\Laminas\Form\Element\\' . ucfirst($type);
        }

        $options = array_merge(
            [
                'type'  => $type,
                'label' => 'Label',
                'hint'  => 'Hint',
            ],
            $options
        );

        $element = new $type('test');
        $element->setOptions($options);
        $element->setAttributes($attributes);

        return $element;
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new CommonHelper\FormRow([]);
        $this->sut->setView($this->phpRenderer());
        $this->sut->setTranslator($this->translator());
    }

    protected function setUpFormElementErrors(ContainerInterface $serviceLocator): CommonHelper\FormElementErrors
    {
        return new CommonHelper\FormElementErrorsFactory()->__invoke($serviceLocator, CommonHelper\FormElementErrors::class);
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $serviceManager->setFactory(FormElementMessageFormatter::class, new FormElementMessageFormatterFactory());
        $serviceManager->setService(static::VALIDATOR_MANAGER, $this->setUpValidatorPluginManager());
        $this->phpRenderer();
        return $serviceManager;
    }

    protected function phpRenderer(): MockObject|PhpRenderer
    {
        if (! $this->serviceManager->has(PhpRenderer::class)) {
            $instance = $this->createPartialMock(PhpRenderer::class, ['render']);
            $instance->method('render')->willReturn('');
            $this->serviceManager->setService(PhpRenderer::class, $instance);
            $instance->setHelperPluginManager($this->viewHelperPluginManager());
        }

        return $this->serviceManager->get(PhpRenderer::class);
    }

    protected function viewHelperPluginManager(): HelperPluginManager
    {
        if (! $this->serviceManager->has(HelperPluginManager::class)) {
            $container = m::mock(ContainerInterface::class);
            $instance = new HelperPluginManager($container);
            $translateHelper = new Translate();
            $translateHelper->setTranslator($this->translator());
            $instance->setService('translate', $translateHelper);
            $instance->setService('form_label', new LaminasHelper\FormLabel());
            $instance->setService('form_element', new CommonHelper\FormElement());
            $instance->setService('form_text', new LaminasHelper\FormText());
            $instance->setService(Doctype::class, m::mock(Doctype::class));
            $formElementErrors = $this->setUpFormElementErrors($this->serviceManager);
            $formElementErrors->setView($this->phpRenderer());
            $instance->setService('form_element_errors', $formElementErrors);

            $this->serviceManager->setService(HelperPluginManager::class, $instance);
        }

        return $this->serviceManager->get(HelperPluginManager::class);
    }

    protected function translator(): Translator
    {
        if (! $this->serviceManager->has(TranslatorInterface::class)) {
            $instance = new Translator();
            $this->serviceManager->setService(TranslatorInterface::class, $instance);
        }

        return $this->serviceManager->get(TranslatorInterface::class);
    }

    protected function setUpValidatorPluginManager(): ValidatorPluginManager
    {
        return m::mock(ValidatorPluginManager::class);
    }
}
