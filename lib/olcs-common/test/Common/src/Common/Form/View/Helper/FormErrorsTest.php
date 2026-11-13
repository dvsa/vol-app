<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\Elements\Types\PostcodeSearch;
use Common\Form\Elements\Types\Radio;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatterFactory;
use Common\Form\View\Helper\FormErrorsFactory;
use Common\Test\MocksServicesTrait;
use Laminas\Form\ElementInterface;
use Psr\Container\ContainerInterface;
use Laminas\Form\Form;
use Laminas\I18n\Translator\TranslatorInterface;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator as Translator;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Form\View\Helper\FormErrors;
use Laminas\Form\Element;
use Laminas\Form\Element\DateSelect;
use Mockery\MockInterface;
use HTMLPurifier;

/**
 * @see FormErrors
 */
final class FormErrorsTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected const string VALIDATOR_MANAGER = 'ValidatorManager';

    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeIsCallable(): void
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);

        // Assert
        $this->assertIsCallable(static fn(?\Laminas\Form\FormInterface $form = null, bool $ignoreValidation = false): string => $sut->__invoke($form, $ignoreValidation));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeEscapesHtmlInMessage(): void
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);
        $form = new Form();
        $form->setMessages(['<a>some text</a>']);

        // Execute
        $result = $sut->__invoke($form);

        // Assert
        $this->assertStringNotContainsString('<a>', (string) $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeWithoutForm(): void
    {
        $form = null;

        $sut = $this->sut;

        $this->assertSame($this->sut, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithoutMessages(): void
    {
        $form = m::mock(\Laminas\Form\Form::class);
        $messages = [];
        $expected = '';

        $sut = $this->sut;

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages);

        $this->assertEquals($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessagesWithoutLabelOrAnchor(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?Bar-translated(\s+)?<\/li>(\s+)?'
            . '<li>(\s+)?Cake-translated(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class);

        $element = $this->setUpElement();

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages)
            ->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(true)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsTitle')
            ->andReturn(null)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsParagraph')
            ->andReturn(null);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessagesWithAnchor(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class);

        $element = $this->setUpElement();
        $element->setLabel('foo');
        $element->setAttribute('id', 'foo-id');

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages)
            ->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(true)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsTitle')
            ->andReturn(null)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsParagraph')
            ->andReturn(null);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessagesWithAnchor2(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class);

        $element = $this->setUpElement();
        $element->setOption('label_attributes', ['id' => 'foo-id']);

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages)
            ->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(true)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsTitle')
            ->andReturn(null)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsParagraph')
            ->andReturn(null);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessagesWithAnchor3(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class);

        $element = $this->setUpElement();
        $element->setOption('fieldset-attributes', ['id' => 'foo-id']);

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages)
            ->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(true)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsTitle')
            ->andReturn(null)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsParagraph')
            ->andReturn(null);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessagesWithAnchorPostcodeSearch(): void
    {
        $messages = [
            'foo' => [
                'bar',
            ]
        ];
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?<a href="#PC_ID">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class)->makePartial();
        $mockFoo = m::mock(PostcodeSearch::class)->makePartial();

        // Expectations
        $form->shouldReceive('hasValidated')->andReturn(true)
            ->shouldReceive('isValid')->andReturn(false)
            ->shouldReceive('getMessages')->andReturn($messages)
            ->shouldReceive('has')->once()->with('foo')->andReturn(true)
            ->shouldReceive('get')->with('foo')->once()->andReturn($mockFoo);

        $mockFoo
            ->shouldReceive('has')->once()->andReturn()
            ->shouldReceive('get')->with('postcode')->twice()->andReturn(
                m::mock(ElementInterface::class)->shouldReceive('getAttribute')->with('id')->twice()->andReturn('PC_ID')->getMock()
            )
            ->shouldReceive('getLabel')->andReturn('Default Label');

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessagesWithAnchorDateSelect(): void
    {
        $messages = [
            'foo' => [
                'bar',
            ]
        ];
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?<a href="#DS_ID_day">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class)->makePartial();
        $element = new DateSelect()->setAttribute('id', 'DS_ID');
        $element->setLabel('Default Label');

        // Expectations
        $form->shouldReceive('getMessages')->andReturn($messages)
            ->shouldReceive('has')->once()->with('foo')->andReturn(true)
            ->shouldReceive('get')->with('foo')->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessagesWithAnchorToFirstRadioInput(): void
    {
        $idGenerator = m::mock(\Common\View\Helper\UniqidGenerator::class);
        $idGenerator->shouldReceive('generateId')->twice()->andReturn('generated_id');

        $radio = new Radio('existingOperatorLicence', [], $idGenerator);
        $radio->setValueOptions([
            'NoLicence' => ['label' => 'No', 'value' => 'N'],
            'licence' => ['label' => 'Yes', 'value' => 'Y'],
        ]);

        $licenceFieldset = new \Laminas\Form\Fieldset('existing-operator-licence');
        $licenceFieldset->add($radio);
        $fields = new \Laminas\Form\Fieldset('fields');
        $fields->add($licenceFieldset);

        $form = new Form();
        $form->add($fields);
        $form->setMessages([
            'fields' => [
                'existing-operator-licence' => [
                    'existingOperatorLicence' => ['isEmpty' => 'required'],
                ],
            ],
        ]);

        $result = $this->sut->__invoke($form);

        $this->assertStringContainsString('href="#generated_id_N"', $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nestedAnchorProvider')]
    public function testNestedAnchorMatchesRenderedInput(bool $prepareFirst, ?string $explicitId): void
    {
        $reply = new \Laminas\Form\Element\Textarea('reply');
        if ($explicitId !== null) {
            $reply->setAttribute('id', $explicitId);
        }
        $inputs = new \Laminas\Form\Fieldset('inputs');
        $inputs->add($reply);
        $actions = new \Laminas\Form\Fieldset('form-actions');
        $actions->add($inputs);
        $form = new Form();
        $form->add($actions);
        $form->setMessages([
            'form-actions' => ['inputs' => ['reply' => ['isEmpty' => 'Enter a reply']]],
        ]);
        if ($prepareFirst) {
            $form->prepare();
        }
        $originalName = $reply->getName();
        $summary = $this->sut->__invoke($form);

        $this->assertSame($originalName, $reply->getName());
        $this->assertSame($explicitId, $reply->getAttribute('id'));

        $form->prepare();
        $translator = new \CommonTest\Common\Util\DummyTranslator();
        $helpers = new \Laminas\View\HelperPluginManager(new \Laminas\ServiceManager\ServiceManager());
        $view = new \Laminas\View\Renderer\PhpRenderer();
        $view->setHelperPluginManager($helpers);
        $helpers->setService('formtextarea', new \Laminas\Form\View\Helper\FormTextarea());
        $helpers->setService(
            'form_element_errors',
            new \Common\Form\View\Helper\FormElementErrors(new FormElementMessageFormatter($translator), $translator)
        );
        $renderer = new \Common\Form\View\Helper\FormElement();
        $renderer->setView($view);
        $markup = html_entity_decode($renderer->render($reply), ENT_QUOTES);
        $id = $explicitId ?? 'form-actions[inputs][reply]';

        $this->assertStringContainsString('href="#' . $id . '"', $summary);
        $this->assertStringContainsString('id="' . $id . '"', $markup);
        $this->assertSame($summary, $this->sut->__invoke($form));
    }

    public static function nestedAnchorProvider(): array
    {
        return [
            'before preparation' => [false, null],
            'after preparation' => [true, null],
            'explicit ID before preparation' => [false, 'reply-input'],
            'explicit ID after preparation' => [true, 'reply-input'],
        ];
    }

    public function testNestedCollectionAnchorExcludesValidatorKeys(): void
    {
        $row = new \Laminas\Form\Fieldset('0');
        $row->add(new Element('name'));
        $rows = new \Laminas\Form\Fieldset('rows');
        $rows->add($row);
        $form = new Form();
        $form->add($rows);
        $form->setMessages(['rows' => [0 => ['name' => ['validation' => ['isEmpty' => 'Enter a name']]]]]);

        $this->assertStringContainsString('href="#rows[0][name]"', $this->sut->__invoke($form));
    }

    public function testNestedDateWithoutIdLinksToDay(): void
    {
        $fields = new \Laminas\Form\Fieldset('fields');
        $fields->add(new DateSelect('date'));
        $form = new Form();
        $form->add($fields);
        $form->setMessages(['fields' => ['date' => ['invalid' => 'Enter a date']]]);

        $this->assertStringContainsString('href="#fields[date]_day"', $this->sut->__invoke($form));
    }

    public function testTopLevelAnchorStillUsesElementName(): void
    {
        $form = new Form();
        $form->add(new Element('reply'));
        $form->setMessages(['reply' => ['isEmpty' => 'Enter a reply']]);

        $this->assertStringContainsString('href="#reply"', $this->sut->__invoke($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessagesWithAnchorUsingName(): void
    {
        $messages = [
            'foo' => [
                'bar',
            ]
        ];
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?<a href="#NAME">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class)->makePartial();
        $element = $this->setUpElement('NAME');

        // Expectations
        $form->shouldReceive('getMessages')->andReturn($messages)
            ->shouldReceive('has')->once()->with('foo')->andReturn(true)
            ->shouldReceive('get')->with('foo')->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\TestDox('Test when a form element has been setup with a custom error message')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithCustomErrorMessage(): void
    {
        $messages = [
            'foo' => [
                'foo-error'
            ]
        ];
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?Foo-error-translated(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class);
        $element = $this->setUpElement();
        $element->setOption('error-message', 'foo-error');

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsTitle')
            ->andReturn(null)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsParagraph')
            ->andReturn(null)
            ->shouldReceive('getOption')
            ->andReturn(null)
            ->shouldReceive('getAttribute')
            ->andReturn(null)
            ->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(true);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithShortLabelAndAnchor(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?'
            . '<a href="#foo-id">Foo-label-translated\: bar-translated-translated<\/a>(\s+)?'
            . '<\/li>(\s+)?'
            . '<li>(\s+)?'
            . '<a href="#foo-id">Foo-label-translated\: cake-translated-translated<\/a>(\s+)?'
            . '<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class);

        $element = $this->setUpElement();
        $element->setOption('short-label', 'foo-label');
        $element->setOption('fieldset-attributes', ['id' => 'foo-id']);

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages)
            ->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(true)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsTitle')
            ->andReturn(null)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsParagraph')
            ->andReturn(null);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithShortLabelWithoutAnchor(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?Foo-label-translated\: bar-translated-translated(\s+)?'
            . '<\/li>(\s+)?'
            . '<li>(\s+)?Foo-label-translated\: cake-translated-translated(\s+)?'
            . '<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class);
        $element = $this->setUpElement();
        $element->setOption('short-label', 'foo-label');

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages)
            ->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(true)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsTitle')
            ->andReturn(null)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsParagraph')
            ->andReturn(null);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\TestDox('Test when a form element has been setup as a fieldset')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessageObjectElementAsFieldset(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?Bar-translated(\s+)?<\/li>(\s+)?'
            . '<li>(\s+)?Cake-translated(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class);

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsTitle')
            ->andReturn(null)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsParagraph')
            ->andReturn(null)
            ->shouldReceive('getOption')
            ->andReturn(null)
            ->shouldReceive('getAttribute')
            ->andReturn(null)
            ->shouldReceive('has')
            ->times(3)
            ->andReturn(false)
            ->shouldReceive('getName')->andReturn(null)
            ->shouldReceive('getLabel')->andReturn('Default Label');

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessagesWithAnchorAndCustomTitle(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];

        $title = 'error-title';
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">error-title-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class);
        $element = $this->setUpElement();
        $element->setAttribute('id', 'foo-id');

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages)
            ->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(true)
            ->shouldReceive('getOption')
            ->twice()
            ->with('formErrorsTitle')
            ->andReturn($title)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsParagraph')
            ->andReturn(null);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessagesWithAnchorAndCustomTitleAndParagraph(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];

        $title = 'error-title';
        $paragraph = 'error-paragraph';
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">error-title-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p>error-paragraph-translated<\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class);
        $element = $this->setUpElement();
        $element->setAttribute('id', 'foo-id');

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages)
            ->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(true)
            ->shouldReceive('getOption')
            ->twice()
            ->with('formErrorsTitle')
            ->andReturn($title)
            ->shouldReceive('getOption')
            ->twice()
            ->with('formErrorsParagraph')
            ->andReturn($paragraph);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    #[\PHPUnit\Framework\Attributes\Depends('invokeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeRenderWithMessagesWithAnchorAndCustomParagraph(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];

        $paragraph = 'error-paragraph';
        $expected = '/(\s+)?<div class="govuk-error-summary" data-module="govuk-error-summary">(\s+)?<div role="alert">(\s+)?'
            . '<h2 class="govuk-error-summary__title">form-errors-translated<\/h2>(\s+)?'
            . '<div class="govuk-error-summary__body">(\s+)?'
            . '<p>error-paragraph-translated<\/p>(\s+)?'
            . '<ul class="govuk-list govuk-error-summary__list">(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li>(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ul>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class);
        $element = $this->setUpElement();
        $element->setAttribute('id', 'foo-id');

        // Expectations
        $form->shouldReceive('hasValidated')
            ->andReturn(true)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('getMessages')
            ->andReturn($messages)
            ->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(true)
            ->shouldReceive('getOption')
            ->once()
            ->with('formErrorsTitle')
            ->andReturn(null)
            ->shouldReceive('getOption')
            ->twice()
            ->with('formErrorsParagraph')
            ->andReturn($paragraph);

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    protected function setUp(): void
    {
        $view = m::mock(\Laminas\View\Renderer\RendererInterface::class);
        $serviceLocator = $this->setUpServiceLocator();
        $this->sut = $this->setUpSut($serviceLocator);
        $this->sut->setView($view);
    }

    protected function setUpSut(ContainerInterface $serviceLocator): FormErrors
    {
        //$pluginManager = $this->setUpAbstractPluginManager($serviceLocator);
        return new FormErrorsFactory()->__invoke($serviceLocator, FormErrors::class);
    }

    /**
     * @return void
     */
    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $serviceManager->setService(TranslatorInterface::class, $this->setUpTranslator());
        $serviceManager->setFactory(FormElementMessageFormatter::class, new FormElementMessageFormatterFactory());
        $serviceManager->setService(static::VALIDATOR_MANAGER, m::mock(ValidatorPluginManager::class));
        return $serviceManager;
    }

    protected function setUpTranslator(): MockInterface
    {
        $instance = $this->setUpMockService(Translator::class);
        $instance->shouldReceive('translate')->andReturnUsing(static fn($key) => $key . '-translated')->byDefault();
        return $instance;
    }

    /**
     * @param string|null $name
     */
    protected function setUpElement(?string $name = null): Element
    {
        $element = new Element($name);
        $element->setLabel('Default Label');
        return $element;
    }
}
