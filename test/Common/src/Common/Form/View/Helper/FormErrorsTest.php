<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\Elements\Types\PostcodeSearch;
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
class FormErrorsTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected const VALIDATOR_MANAGER = 'ValidatorManager';

    protected $sut;

    protected $view;

    /**
     * @test
     */
    public function invokeIsCallable(): void
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);

        // Assert
        $this->assertIsCallable(static fn(?\Laminas\Form\FormInterface $form = null, bool $ignoreValidation = false): string => $sut->__invoke($form, $ignoreValidation));
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
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
        $this->assertStringNotContainsString('<a>', $result);
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeWithoutForm(): void
    {
        $form = null;

        $sut = $this->sut;

        $this->assertSame($this->sut, $sut($form));
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
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

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeRenderWithMessagesWithoutLabelOrAnchor(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?Bar-translated(\s+)?<\/li>(\s+)?'
            . '<li class="validation-summary__item">(\s+)?Cake-translated(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeRenderWithMessagesWithAnchor(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeRenderWithMessagesWithAnchor2(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeRenderWithMessagesWithAnchor3(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeRenderWithMessagesWithAnchorPostcodeSearch(): void
    {
        $messages = [
            'foo' => [
                'bar',
            ]
        ];
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#PC_ID">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeRenderWithMessagesWithAnchorDateSelect(): void
    {
        $messages = [
            'foo' => [
                'bar',
            ]
        ];
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#DS_ID_day">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
            . '<\/div>/';

        $sut = $this->sut;

        // Mocks
        $form = m::mock(\Laminas\Form\Form::class)->makePartial();
        $element = (new DateSelect())->setAttribute('id', 'DS_ID');
        $element->setLabel('Default Label');

        // Expectations
        $form->shouldReceive('getMessages')->andReturn($messages)
            ->shouldReceive('has')->once()->with('foo')->andReturn(true)
            ->shouldReceive('get')->with('foo')->andReturn($element);

        $this->assertMatchesRegularExpression($expected, $sut($form));
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeRenderWithMessagesWithAnchorUsingName(): void
    {
        $messages = [
            'foo' => [
                'bar',
            ]
        ];
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#NAME">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @testdox Test when a form element has been setup with a custom error message
     * @depends invokeIsCallable
     */
    public function invokeRenderWithCustomErrorMessage(): void
    {
        $messages = [
            'foo' => [
                'foo-error'
            ]
        ];
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?Foo-error-translated(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeRenderWithShortLabelAndAnchor(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?'
            . '<a href="#foo-id">Foo-label-translated\: bar-translated-translated<\/a>(\s+)?'
            . '<\/li>(\s+)?'
            . '<li class="validation-summary__item">(\s+)?'
            . '<a href="#foo-id">Foo-label-translated\: cake-translated-translated<\/a>(\s+)?'
            . '<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeRenderWithShortLabelWithoutAnchor(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?Foo-label-translated\: bar-translated-translated(\s+)?'
            . '<\/li>(\s+)?'
            . '<li class="validation-summary__item">(\s+)?Foo-label-translated\: cake-translated-translated(\s+)?'
            . '<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @testdox Test when a form element has been setup as a fieldset
     * @depends invokeIsCallable
     */
    public function invokeRenderWithMessageObjectElementAsFieldset(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?Bar-translated(\s+)?<\/li>(\s+)?'
            . '<li class="validation-summary__item">(\s+)?Cake-translated(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeRenderWithMessagesWithAnchorAndCustomTitle(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];

        $title = 'error-title';
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">error-title-translated<\/h2>(\s+)?'
            . '<p><\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @depends invokeIsCallable
     */
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
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">error-title-translated<\/h2>(\s+)?'
            . '<p>error-paragraph-translated<\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeRenderWithMessagesWithAnchorAndCustomParagraph(): void
    {
        $messages = [
            'foo' => [
                'bar',
                'cake'
            ]
        ];

        $paragraph = 'error-paragraph';
        $expected = '/(\s+)?<div class="validation-summary" role="alert" id="validationSummary">(\s+)?'
            . '<h2 class="govuk-heading-m">form-errors-translated<\/h2>(\s+)?'
            . '<p>error-paragraph-translated<\/p>(\s+)?'
            . '<ol class="validation-summary__list">(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Bar-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<li class="validation-summary__item">(\s+)?<a href="#foo-id">Cake-translated<\/a>(\s+)?<\/li>(\s+)?'
            . '<\/ol>(\s+)?'
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
        $this->view = m::mock(\Laminas\View\Renderer\RendererInterface::class);
        $serviceLocator = $this->setUpServiceLocator();
        $this->sut = $this->setUpSut($serviceLocator);
        $this->sut->setView($this->view);
    }

    protected function setUpSut(ContainerInterface $serviceLocator): FormErrors
    {
        //$pluginManager = $this->setUpAbstractPluginManager($serviceLocator);
        return (new FormErrorsFactory())->__invoke($serviceLocator, FormErrors::class);
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
    protected function setUpElement(string $name = null): Element
    {
        $element = new Element($name);
        $element->setLabel('Default Label');
        return $element;
    }
}
