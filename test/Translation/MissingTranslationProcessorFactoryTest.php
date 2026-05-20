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

class MissingTranslationProcessorFactoryTest extends TestCase
{
    public function testBuildsProcessorWithPlaceholderWhenHelperRegistered(): void
    {
        $renderer = $this->createMock(RendererInterface::class);
        $resolver = $this->createMock(TemplatePathStack::class);
        // The registered `getPlaceholder` helper is a Closure, not an instance —
        // see GetPlaceholderFactory.
        $placeholder = fn(string $name) => null;

        $viewHelperManager = $this->createMock(HelperPluginManager::class);
        $viewHelperManager->method('has')->with('getPlaceholder')->willReturn(true);
        $viewHelperManager->method('get')->with('getPlaceholder')->willReturn($placeholder);

        $container = $this->createMock(ServiceManager::class);
        $container->method('get')->willReturnMap([
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
        $renderer = $this->createMock(RendererInterface::class);
        $resolver = $this->createMock(TemplatePathStack::class);

        $viewHelperManager = $this->createMock(HelperPluginManager::class);
        $viewHelperManager->method('has')->with('getPlaceholder')->willReturn(false);
        $viewHelperManager->expects($this->never())->method('get');

        $container = $this->createMock(ServiceManager::class);
        $container->method('get')->willReturnMap([
            ['ViewRenderer', $renderer],
            [TemplatePathStack::class, $resolver],
            ['ViewHelperManager', $viewHelperManager],
        ]);

        $factory = new MissingTranslationProcessorFactory();
        $service = $factory($container, MissingTranslationProcessor::class);

        $this->assertInstanceOf(MissingTranslationProcessor::class, $service);
    }
}
