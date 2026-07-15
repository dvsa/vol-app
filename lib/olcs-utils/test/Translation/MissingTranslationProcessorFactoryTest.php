<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Translation;

use Dvsa\Olcs\Utils\Translation\MissingTranslationProcessor;
use Dvsa\Olcs\Utils\Translation\MissingTranslationProcessorFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\TemplatePathStack;
use PHPUnit\Framework\TestCase;

final class MissingTranslationProcessorFactoryTest extends TestCase
{
    public function testBuildsProcessorWithPlaceholderWhenHelperRegistered(): void
    {
        $renderer = $this->createStub(RendererInterface::class);
        $resolver = $this->createStub(TemplatePathStack::class);
        // The registered `getPlaceholder` helper is a Closure, not an instance —
        // see GetPlaceholderFactory.
        $placeholder = fn(string $name) => null;

        $viewHelperManager = $this->createStub(HelperPluginManager::class);
        $viewHelperManager->method('has')->willReturnMap([
            ['getPlaceholder', true],
        ]);
        $viewHelperManager->method('get')->willReturnMap([
            ['getPlaceholder', $placeholder],
        ]);

        $container = $this->createMock(ServiceManager::class);
        $container->expects($this->exactly(3))->method('get')->willReturnMap([
            ['ViewRenderer', $renderer],
            [TemplatePathStack::class, $resolver],
            ['ViewHelperManager', $viewHelperManager],
        ]);

        $factory = new MissingTranslationProcessorFactory();
        $service = $factory($container, MissingTranslationProcessor::class);

        $this->assertInstanceOf(MissingTranslationProcessor::class, $service);
    }

    public function testBuildsProcessorWithoutPlaceholderWhenHelperMissing(): void
    {
        $renderer = $this->createStub(RendererInterface::class);
        $resolver = $this->createStub(TemplatePathStack::class);

        $viewHelperManager = $this->createMock(HelperPluginManager::class);
        $viewHelperManager->method('has')->willReturnMap([
            ['getPlaceholder', false],
        ]);
        $viewHelperManager->expects($this->never())->method('get');

        $container = $this->createMock(ServiceManager::class);
        $container->expects($this->exactly(3))->method('get')->willReturnMap([
            ['ViewRenderer', $renderer],
            [TemplatePathStack::class, $resolver],
            ['ViewHelperManager', $viewHelperManager],
        ]);

        $factory = new MissingTranslationProcessorFactory();
        $service = $factory($container, MissingTranslationProcessor::class);

        $this->assertInstanceOf(MissingTranslationProcessor::class, $service);
    }
}
