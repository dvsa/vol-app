<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\View\Helper\FormLabelFactory;
use Laminas\Form\View\Helper\FormLabel;
use Common\Form\View\Helper\FormErrors;
use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\ServiceManager;

class FormLabelFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @test
     */
    public function invokeIsCallable(): void
    {
        // Setup
        $sut = $this->setUpSut();

        // Assert
        $this->assertIsCallable(static fn(\Psr\Container\ContainerInterface $container, string $requestedName, ?array $options = null): \Laminas\Form\View\Helper\FormLabel => $sut->__invoke($container, $requestedName, $options));
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeReturnsInstanceOfFormLabel(): void
    {
        // Setup
        $serviceLocator = $this->setUpServiceLocator();
        $sut = $this->setUpSut();

        // Execute
        $result = $sut->__invoke($serviceLocator, FormErrors::class);

        // Assert
        $this->assertInstanceOf(FormLabel::class, $result);
    }

    protected function setUpSut(): FormLabelFactory
    {
        return new FormLabelFactory();
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $serviceManager->setService(TranslatorInterface::class, $this->setUpMockService(TranslatorInterface::class));
        return $serviceManager;
    }
}
