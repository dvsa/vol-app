<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatterFactory;
use Common\Form\View\Helper\FormLabelFactory;
use Laminas\Form\View\Helper\FormLabel;
use Common\Form\View\Helper\FormElementErrors;
use Common\Form\View\Helper\FormElementErrorsFactory;
use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use HTMLPurifier;
use Laminas\Form\Element;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\I18n\View\Helper\Translate;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;
use Laminas\View\Helper\Doctype;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mockery as m;
use Mockery\MockInterface;
use Psr\Container\ContainerInterface;

/**
 * @see FormElementErrors
 */
class FormElementErrorsTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected const VALIDATOR_MANAGER = 'ValidatorManager';

    /**
     * @test
     */
    public function renderIsCallable(): void
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);

        // Assert
        $this->assertIsCallable(static fn(\Laminas\Form\ElementInterface $element, array $attributes = []): string => $sut->render($element, $attributes));
    }

    /**
     * @test
     * @depends renderIsCallable
     */
    public function renderEscapesHtmlInMessage(): void
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut($serviceLocator);
        $element = new Element();
        $element->setMessages(['<a>some text</a>']);

        // Execute
        $result = $sut->render($element);

        // Assert
        $this->assertStringNotContainsString('<a>', $result);
    }

    /**
     * @depends renderIsCallable
     */
    public function testRender(): void
    {
        $element = new \Laminas\Form\Element\Text('test');
        $element->setLabel('Test');
        $element->setMessages(['Message']);

        $translator = new Translator();
        $translateHelper = new Translate();
        $translateHelper->setTranslator($translator);

        $doctype = m::mock(Doctype::class);

        $container = m::mock(ContainerInterface::class);
        $helpers = new HelperPluginManager($container);
        $helpers->setService('translate', $translateHelper);
        $helpers->setService(Doctype::class, $doctype);

        $view = new PhpRenderer();
        $view->setHelperPluginManager($helpers);

        $serviceLocator = $this->setUpServiceLocator();
        $viewHelper = $this->setUpSut($serviceLocator);
        $viewHelper->setView($view);

        $markup = $viewHelper($element);

        $expectedMarkup = '<p class="govuk-error-message"><span class="govuk-visually-hidden">Error:</span>Message</p>';

        $this->assertSame($expectedMarkup, $markup);
    }

    protected function setUpElement(): Element
    {
        $element = new Element();
        $element->setAttribute('id', 'foo');
        $element->setLabel("foo");
        return $element;
    }

    protected function setUpSut(ContainerInterface $container): FormElementErrors
    {
        return (new FormElementErrorsFactory())->__invoke($container, FormElementErrors::class);
    }

    protected function setUpTranslator(): MockInterface
    {
        $instance = $this->setUpMockService(Translator::class);
        $instance->shouldReceive('translate')->andReturnUsing(static fn($key) => $key)->byDefault();
        return $instance;
    }

    protected function setUpFormLabel(ContainerInterface $container): FormLabel
    {
        return (new FormLabelFactory())->__invoke($container, \Laminas\Form\View\Helper\FormLabel::class);
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $serviceManager->setService(TranslatorInterface::class, $this->setUpTranslator());
        $serviceManager->setService(HTMLPurifier::class, new HTMLPurifier());
        $serviceManager->setFactory(\Laminas\Form\View\Helper\FormLabel::class, new FormLabelFactory());
        $serviceManager->setFactory(FormElementMessageFormatter::class, new FormElementMessageFormatterFactory());
        $serviceManager->setService(static::VALIDATOR_MANAGER, m::mock(ValidatorPluginManager::class));
        return $serviceManager;
    }
}
