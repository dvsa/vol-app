<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Translator;

use Dvsa\Olcs\Api\Domain\Repository\Replacement;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKeyText;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoaderFactory;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * TranslationLoaderFactoryTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TranslationLoaderFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockCache = m::mock(CacheEncryption::class);
        $mockTranslationKeyTextRepo = m::mock(TranslationKeyText::class);
        $mockReplacementRepo = m::mock(Replacement::class);
        $mockRepoManager = m::mock(RepositoryServiceManager::class);
        $mockRepoManager->expects('get')->with('TranslationKeyText')->andReturn($mockTranslationKeyTextRepo);
        $mockRepoManager->expects('get')->with('Replacement')->andReturn($mockReplacementRepo);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->expects('get')->with('RepositoryServiceManager')->andReturn($mockRepoManager);
        $mockSl->expects('get')->with(CacheEncryption::class)->andReturn($mockCache);

        $sut = new TranslationLoaderFactory();
        $service = $sut->__invoke($mockSl, TranslationLoader::class);

        self::assertInstanceOf(TranslationLoader::class, $service);
    }
}
