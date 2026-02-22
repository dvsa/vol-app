<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cache;

use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Cache\TranslationKey as Handler;
use Dvsa\Olcs\Api\Domain\Query\Cache\TranslationKey as Qry;
use Mockery as m;
use Laminas\I18n\Translator\LoaderPluginManager;

/**
 * Tests the translation cache query handler calls the translation loader correctly
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TranslationKeyTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Handler();
        $translationLoader = m::mock(TranslationLoader::class);
        $translationLoader->expects('getMessagesFromDb')
            ->with('en_GB', TranslationLoader::DEFAULT_TEXT_DOMAIN)
            ->andReturn(['messages']);

        $pluginManager = m::mock(LoaderPluginManager::class);
        $pluginManager->expects('get')->with(TranslationLoader::class)->andReturn($translationLoader);

        $this->mockedSmServices = [
            'TranslatorPluginManager' => $pluginManager,
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['uniqueId' => 'en_GB']);
        $this->assertEquals(['messages'], $this->sut->handleQuery($query));
    }
}
