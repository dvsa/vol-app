<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cache;

use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Cache\Replacements as Handler;
use Dvsa\Olcs\Api\Domain\Query\Cache\Replacements as ReplacementsQry;
use Mockery as m;
use Laminas\I18n\Translator\LoaderPluginManager;

/**
 * Tests the translation replacements query handler calls the translation loader correctly
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ReplacementsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Handler();
        $translationLoader = m::mock(TranslationLoader::class);
        $translationLoader->expects('getReplacementsFromDb')->withNoArgs()->andReturn(['messages']);

        $pluginManager = m::mock(LoaderPluginManager::class);
        $pluginManager->expects('get')->with(TranslationLoader::class)->andReturn($translationLoader);

        $this->mockedSmServices = [
            'TranslatorPluginManager' => $pluginManager,
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = ReplacementsQry::create([]);
        $this->assertEquals(['messages'], $this->sut->handleQuery($query));
    }
}
