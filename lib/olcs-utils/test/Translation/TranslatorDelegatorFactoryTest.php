<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Translation;

use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegatorFactory;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class TranslatorDelegatorFactoryTest extends TestCase
{
    public function testWrapsTranslatorWithReplacementsLoadedFromRemoteLoader(): void
    {
        // The real loader implementations (in olcs-common / app/api) extend RemoteLoaderInterface
        // and add loadReplacements(). Use an anonymous class here to duck-type that contract.
        $loader = new class () implements \Laminas\I18n\Translator\Loader\RemoteLoaderInterface {
            public function load($locale, $textDomain): \Laminas\I18n\Translator\TextDomain
            {
                return new \Laminas\I18n\Translator\TextDomain();
            }

            /**
             * @return array<string, string>
             */
            public function loadReplacements(): array
            {
                return ['{{token}}' => 'value'];
            }
        };

        $loaderPluginManager = $this->createMock(LoaderPluginManager::class);
        $loaderPluginManager->method('get')->with('CustomLoader')->willReturn($loader);

        $translator = $this->createMock(Translator::class);
        $translator->method('getPluginManager')->willReturn($loaderPluginManager);
        $translator->method('translate')->willReturn('with {{token}} inside');

        $container = $this->createMock(ServiceManager::class);
        $container->method('get')->with('Config')->willReturn([
            'translator' => [
                'remote_translation' => [['type' => 'CustomLoader']],
            ],
        ]);

        $factory = new TranslatorDelegatorFactory();
        $result = $factory($container, 'translator', fn() => $translator);

        $this->assertInstanceOf(TranslatorDelegator::class, $result);
        $this->assertEquals('with value inside', $result->translate('any'));
    }
}
