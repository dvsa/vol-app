<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Translation;

use Dvsa\Olcs\Utils\Translation\MissingTranslationDelegatorFactory;
use Dvsa\Olcs\Utils\Translation\MissingTranslationProcessor;
use Dvsa\Olcs\Utils\Translation\Replacements;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\EventManager\EventManager;
use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class MissingTranslationDelegatorFactoryTest extends TestCase
{
    public function testAttachesProcessorToTheUnderlyingTranslator(): void
    {
        $eventManager = new EventManager();

        $translator = $this->createMock(Translator::class);
        $translator->expects($this->once())->method('enableEventManager');
        $translator->method('getEventManager')->willReturn($eventManager);

        $processor = $this->createMock(MissingTranslationProcessor::class);
        $processor->expects($this->once())->method('attach')->with($eventManager);

        $container = $this->createMock(ServiceManager::class);
        $container->method('get')->with(MissingTranslationProcessor::class)->willReturn($processor);

        $factory = new MissingTranslationDelegatorFactory();
        $result = $factory($container, Translator::class, fn() => $translator);

        $this->assertSame($translator, $result);
    }

    public function testUnwrapsTheTranslatorDelegatorBeforeAttaching(): void
    {
        $eventManager = new EventManager();

        $translator = $this->createMock(Translator::class);
        $translator->expects($this->once())->method('enableEventManager');
        $translator->method('getEventManager')->willReturn($eventManager);

        $delegator = new TranslatorDelegator($translator, new Replacements([]));

        $processor = $this->createMock(MissingTranslationProcessor::class);
        $processor->expects($this->once())->method('attach')->with($eventManager);

        $container = $this->createMock(ServiceManager::class);
        $container->method('get')->with(MissingTranslationProcessor::class)->willReturn($processor);

        $factory = new MissingTranslationDelegatorFactory();
        $result = $factory($container, Translator::class, fn() => $delegator);

        $this->assertSame($delegator, $result);
    }

    public function testReturnsUnmodifiedTranslatorIfNotASupportedType(): void
    {
        $translator = new \stdClass();

        $container = $this->createMock(ServiceManager::class);
        $container->expects($this->never())->method('get');

        $factory = new MissingTranslationDelegatorFactory();
        $result = $factory($container, 'something', fn() => $translator);

        $this->assertSame($translator, $result);
    }
}
