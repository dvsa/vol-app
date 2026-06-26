<?php

namespace CommonTest\Service\Translator;

use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Translator\TranslationLoader;
use Common\Service\Translator\TranslationLoaderFactory;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class TranslationLoaderFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockQueryService = m::mock(CachingQueryService::class);

        $parentSl = m::mock(ContainerInterface::class);
        $parentSl->expects('get')->with('QueryService')->andReturn($mockQueryService);

        $sut = new TranslationLoaderFactory();
        $service = $sut->__invoke($parentSl, TranslationLoader::class);

        self::assertInstanceOf(TranslationLoader::class, $service);
    }
}
